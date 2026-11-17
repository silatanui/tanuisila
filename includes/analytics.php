<?php
/**
 * Analytics — unique visitor tracking (site + blog posts).
 *
 * Site uniqueness (hybrid): IP + browser family per calendar day.
 * - Same person, Chrome normal + Chrome incognito → 1
 * - Two people on campus Wi‑Fi using Chrome + Firefox → 2
 * - Two people both on Chrome on the same Wi‑Fi → still 1 (unavoidable without login)
 */

function analyticsIsBot(?string $userAgent = null): bool
{
    $userAgent = $userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($userAgent === '') {
        return true;
    }
    return (bool) preg_match(
        '/bot|crawl|slurp|spider|mediapartners|lighthouse|headless|curl|wget|python|facebookexternalhit|pingdom|monitor|preview/i',
        $userAgent
    );
}

function analyticsEnsureVisitorId(): string
{
    if (empty($_COOKIE['sila_vid'])) {
        $visitorId = bin2hex(random_bytes(16));
        if (!headers_sent()) {
            setcookie('sila_vid', $visitorId, [
                'expires' => time() + (86400 * 365),
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        $_COOKIE['sila_vid'] = $visitorId;
    }

    return preg_replace('/[^a-zA-Z0-9]/', '', (string) $_COOKIE['sila_vid']);
}

/**
 * Best-effort public client IP (supports common proxies / Cloudflare).
 */
function analyticsClientIp(): string
{
    $candidates = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '',
        $_SERVER['HTTP_TRUE_CLIENT_IP'] ?? '',
        $_SERVER['HTTP_X_REAL_IP'] ?? '',
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
        $_SERVER['REMOTE_ADDR'] ?? '',
    ];

    foreach ($candidates as $raw) {
        if ($raw === '' || $raw === null) {
            continue;
        }
        // X-Forwarded-For may be a list: client, proxy1, proxy2
        foreach (explode(',', (string) $raw) as $part) {
            $ip = trim($part);
            if ($ip === '') {
                continue;
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '';
}

/**
 * Normalize client IP for identity hashing.
 */
function analyticsNormalizeIp(string $ip): string
{
    $ip = strtolower(trim($ip));
    if ($ip === '') {
        return 'unknown';
    }
    if ($ip === '::1' || $ip === '0:0:0:0:0:0:0:1') {
        return '127.0.0.1';
    }
    return $ip;
}

/**
 * Coarse browser family from User-Agent (not a full fingerprint).
 */
function analyticsBrowserFamily(?string $userAgent = null): string
{
    $ua = strtolower((string) ($userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? '')));
    if ($ua === '') {
        return 'other';
    }

    // Order matters (Edge/Opera include "chrome" in UA)
    if (strpos($ua, 'edg/') !== false || strpos($ua, 'edgios/') !== false) {
        return 'edge';
    }
    if (strpos($ua, 'opr/') !== false || strpos($ua, 'opera') !== false) {
        return 'opera';
    }
    if (strpos($ua, 'firefox/') !== false || strpos($ua, 'fxios/') !== false) {
        return 'firefox';
    }
    if (strpos($ua, 'crios/') !== false || strpos($ua, 'chrome/') !== false || strpos($ua, 'chromium/') !== false) {
        return 'chrome';
    }
    if (strpos($ua, 'safari/') !== false) {
        return 'safari';
    }
    if (strpos($ua, 'msie') !== false || strpos($ua, 'trident/') !== false) {
        return 'ie';
    }

    return 'other';
}

/**
 * Stable daily identity: network IP + browser family.
 * Avoids cookie inflation (incognito) while separating browsers on shared Wi‑Fi.
 */
function analyticsVisitorHash(string $ip, ?string $userAgent = null): string
{
    $ip = analyticsNormalizeIp($ip);
    $family = analyticsBrowserFamily($userAgent);
    return hash('sha256', 'site-ip-browser|v2|' . $ip . '|' . $family);
}

/** @deprecated Use analyticsVisitorHash() */
function analyticsVisitorHashFromIp(string $ip): string
{
    return analyticsVisitorHash($ip, '');
}

/**
 * Resolve approximate location from IP (server-side).
 * City / region / coordinates are approximate ISP locations, not GPS.
 *
 * @return array{
 *   country_code:?string,
 *   country_name:?string,
 *   region:?string,
 *   city:?string,
 *   latitude:?float,
 *   longitude:?float
 * }
 */
function analyticsLookupGeo(string $ip): array
{
    $empty = [
        'country_code' => null,
        'country_name' => null,
        'region' => null,
        'city' => null,
        'latitude' => null,
        'longitude' => null,
    ];
    $ip = trim($ip);
    if (
        $ip === '' || $ip === '::1' || $ip === '127.0.0.1'
        || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0
    ) {
        return $empty;
    }

    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return $empty;
    }

    // Prefer a full IP lookup so city / region / lat-lon are available for map drill-down.
    // Fall back to Cloudflare country-only when the lookup fails.
    $url = 'http://ip-api.com/json/' . rawurlencode($ip)
        . '?fields=status,country,countryCode,regionName,city,lat,lon';
    $json = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_USERAGENT => 'tanuisila-analytics/1.0',
        ]);
        $json = curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 3,
                'header' => "User-Agent: tanuisila-analytics/1.0\r\n",
            ],
        ]);
        $json = @file_get_contents($url, false, $ctx);
    }

    if (is_string($json) && $json !== '') {
        $data = json_decode($json, true);
        if (is_array($data) && ($data['status'] ?? '') === 'success') {
            $code = strtoupper(trim((string) ($data['countryCode'] ?? '')));
            if ($code !== '' && preg_match('/^[A-Z]{2}$/', $code)) {
                $lat = isset($data['lat']) && is_numeric($data['lat']) ? (float) $data['lat'] : null;
                $lon = isset($data['lon']) && is_numeric($data['lon']) ? (float) $data['lon'] : null;
                if ($lat !== null && ($lat < -90 || $lat > 90)) {
                    $lat = null;
                }
                if ($lon !== null && ($lon < -180 || $lon > 180)) {
                    $lon = null;
                }

                return [
                    'country_code' => $code,
                    'country_name' => substr(trim((string) ($data['country'] ?? '')), 0, 100) ?: null,
                    'region' => substr(trim((string) ($data['regionName'] ?? '')), 0, 120) ?: null,
                    'city' => substr(trim((string) ($data['city'] ?? '')), 0, 120) ?: null,
                    'latitude' => $lat,
                    'longitude' => $lon,
                ];
            }
        }
    }

    $cf = strtoupper(trim((string) ($_SERVER['HTTP_CF_IPCOUNTRY'] ?? '')));
    if ($cf !== '' && $cf !== 'XX' && preg_match('/^[A-Z]{2}$/', $cf)) {
        return [
            'country_code' => $cf,
            'country_name' => null,
            'region' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
        ];
    }

    return $empty;
}

/**
 * Fallback city centroids when older rows have a city name but no coordinates.
 * Key format: CITY|CC (uppercase).
 *
 * @return array{0:float,1:float}|null lat, lon
 */
function analyticsCityFallbackCoords(string $city, string $countryCode): ?array
{
    static $coords = [
        'NAIROBI|KE' => [-1.2921, 36.8219],
        'MOMBASA|KE' => [-4.0435, 39.6682],
        'KISUMU|KE' => [-0.0917, 34.7680],
        'NAKURU|KE' => [-0.3031, 36.0800],
        'ELDORET|KE' => [0.5143, 35.2698],
        'THIKA|KE' => [-1.0333, 37.0693],
        'NEW YORK|US' => [40.7128, -74.0060],
        'LOS ANGELES|US' => [34.0522, -118.2437],
        'CHICAGO|US' => [41.8781, -87.6298],
        'HOUSTON|US' => [29.7604, -95.3698],
        'PHOENIX|US' => [33.4484, -112.0740],
        'PHILADELPHIA|US' => [39.9526, -75.1652],
        'SAN ANTONIO|US' => [29.4241, -98.4936],
        'SAN DIEGO|US' => [32.7157, -117.1611],
        'DALLAS|US' => [32.7767, -96.7970],
        'SAN JOSE|US' => [37.3382, -121.8863],
        'AUSTIN|US' => [30.2672, -97.7431],
        'JACKSONVILLE|US' => [30.3322, -81.6557],
        'SAN FRANCISCO|US' => [37.7749, -122.4194],
        'SEATTLE|US' => [47.6062, -122.3321],
        'DENVER|US' => [39.7392, -104.9903],
        'WASHINGTON|US' => [38.9072, -77.0369],
        'BOSTON|US' => [42.3601, -71.0589],
        'ATLANTA|US' => [33.7490, -84.3880],
        'MIAMI|US' => [25.7617, -80.1918],
        'LONDON|GB' => [51.5074, -0.1278],
        'MANCHESTER|GB' => [53.4808, -2.2426],
        'BIRMINGHAM|GB' => [52.4862, -1.8904],
        'BUDAPEST|HU' => [47.4979, 19.0402],
        'BERLIN|DE' => [52.5200, 13.4050],
        'MUNICH|DE' => [48.1351, 11.5820],
        'PARIS|FR' => [48.8566, 2.3522],
        'AMSTERDAM|NL' => [52.3676, 4.9041],
        'TORONTO|CA' => [43.6532, -79.3832],
        'VANCOUVER|CA' => [49.2827, -123.1207],
        'SAO PAULO|BR' => [-23.5505, -46.6333],
        'RIO DE JANEIRO|BR' => [-22.9068, -43.1729],
        'MUMBAI|IN' => [19.0760, 72.8777],
        'BENGALURU|IN' => [12.9716, 77.5946],
        'BANGALORE|IN' => [12.9716, 77.5946],
        'DELHI|IN' => [28.7041, 77.1025],
        'BEIJING|CN' => [39.9042, 116.4074],
        'SHANGHAI|CN' => [31.2304, 121.4737],
        'HONG KONG|HK' => [22.3193, 114.1694],
        'SINGAPORE|SG' => [1.3521, 103.8198],
        'SYDNEY|AU' => [-33.8688, 151.2093],
        'MELBOURNE|AU' => [-37.8136, 144.9631],
        'TOKYO|JP' => [35.6762, 139.6503],
        'SEOUL|KR' => [37.5665, 126.9780],
        'LAGOS|NG' => [6.5244, 3.3792],
        'ACCRA|GH' => [5.6037, -0.1870],
        'CAIRO|EG' => [30.0444, 31.2357],
        'JOHANNESBURG|ZA' => [-26.2041, 28.0473],
        'CAPE TOWN|ZA' => [-33.9249, 18.4241],
        'DUBAI|AE' => [25.2048, 55.2708],
    ];

    $key = strtoupper(trim($city)) . '|' . strtoupper(trim($countryCode));
    return $coords[$key] ?? null;
}

/**
 * Record at most one unique site visitor for today.
 * Call from public pages only (not admin).
 */
function trackSiteUniqueVisit(PDO $pdo): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }

    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (strpos($script, '/admin/') !== false) {
        return;
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (analyticsIsBot($userAgent)) {
        return;
    }

    $ip = analyticsClientIp();
    if ($ip === '') {
        return;
    }

    // Hybrid identity: IP + browser family (incognito same browser = same person)
    $visitorHash = analyticsVisitorHash($ip, $userAgent);
    $visitDate = date('Y-m-d');

    // Fast path: this browser already confirmed today's hybrid row exists
    $dayKey = 'sila_hb_' . date('Ymd') . '_' . substr($visitorHash, 0, 12);
    if (!empty($_COOKIE[$dayKey])) {
        return;
    }

    $path = substr((string) ($_SERVER['REQUEST_URI'] ?? '/'), 0, 255);

    try {
        $existsStmt = $pdo->prepare("
            SELECT id FROM site_daily_visitors
            WHERE visit_date = ? AND visitor_hash = ?
            LIMIT 1
        ");
        $existsStmt->execute([$visitDate, $visitorHash]);
        $already = $existsStmt->fetchColumn();

        if (!$already) {
            $geo = analyticsLookupGeo($ip);
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO site_daily_visitors
                    (visit_date, visitor_hash, ip_address, user_agent, landing_path, country_code, country_name, region, city, latitude, longitude, first_seen)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $visitDate,
                $visitorHash,
                substr($ip, 0, 45),
                substr($userAgent, 0, 255),
                $path,
                $geo['country_code'],
                $geo['country_name'],
                $geo['region'],
                $geo['city'],
                $geo['latitude'],
                $geo['longitude'],
            ]);
        }

        if (!headers_sent()) {
            $expires = strtotime('tomorrow') - 1;
            setcookie($dayKey, '1', [
                'expires' => $expires,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        $_COOKIE[$dayKey] = '1';
    } catch (Throwable $e) {
        // Table may not exist yet on older deploys — ignore quietly
    }
}

function trackPostViewDeviceSpecific(PDO $pdo, array &$post): void
{
    $postId = (int) ($post['id'] ?? 0);
    if ($postId <= 0) {
        return;
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (analyticsIsBot($userAgent)) {
        return;
    }

    $cookieName = 'sila_pv_' . $postId;
    if (!empty($_COOKIE[$cookieName])) {
        return;
    }

    $visitorId = analyticsEnsureVisitorId();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $visitorHash = hash('sha256', $visitorId . '|' . $ip . '|' . $userAgent);

    try {
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) FROM blog_post_views
            WHERE post_id = ? AND visitor_hash = ? AND viewed_at >= (NOW() - INTERVAL 24 HOUR)
        ");
        $checkStmt->execute([$postId, $visitorHash]);
        $alreadyCounted = (int) $checkStmt->fetchColumn();

        if ($alreadyCounted === 0) {
            $logStmt = $pdo->prepare("
                INSERT INTO blog_post_views (post_id, visitor_hash, ip_address, user_agent, viewed_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $logStmt->execute([$postId, $visitorHash, $ip, substr($userAgent, 0, 255)]);
            $pdo->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = ?')->execute([$postId]);
            $post['views'] = ((int) ($post['views'] ?? 0)) + 1;
        }

        if (!headers_sent()) {
            setcookie($cookieName, '1', [
                'expires' => time() + 86400,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        $_COOKIE[$cookieName] = '1';
    } catch (Throwable $e) {
        if (!headers_sent()) {
            setcookie($cookieName, '1', time() + 86400, '/');
        }
    }
}

/**
 * @return array{labels: string[], values: int[]}
 */
function analyticsDailyUniqueSeries(PDO $pdo, int $days = 30): array
{
    $days = max(7, min(90, $days));
    $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
    $map = [];
    try {
        $stmt = $pdo->prepare("
            SELECT visit_date AS d, COUNT(*) AS c
            FROM site_daily_visitors
            WHERE visit_date >= ?
            GROUP BY visit_date
            ORDER BY visit_date ASC
        ");
        $stmt->execute([$since]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[(string) $row['d']] = (int) $row['c'];
        }
    } catch (Throwable $e) {
        $map = [];
    }

    $labels = [];
    $values = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        // Mark the last point so Chart.js skip-ticks cannot hide “today”
        $labels[] = $i === 0 ? (date('M j', strtotime($d)) . ' · today') : date('M j', strtotime($d));
        $values[] = $map[$d] ?? 0;
    }

    return ['labels' => $labels, 'values' => $values];
}

/**
 * Distinct blog readers per day (from post view log).
 * @return array{labels: string[], values: int[]}
 */
function analyticsDailyBlogReadersSeries(PDO $pdo, int $days = 30): array
{
    $days = max(7, min(90, $days));
    $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
    $map = [];
    try {
        $stmt = $pdo->prepare("
            SELECT DATE(viewed_at) AS d, COUNT(DISTINCT visitor_hash) AS c
            FROM blog_post_views
            WHERE viewed_at >= ?
            GROUP BY DATE(viewed_at)
            ORDER BY d ASC
        ");
        $stmt->execute([$since . ' 00:00:00']);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[(string) $row['d']] = (int) $row['c'];
        }
    } catch (Throwable $e) {
        $map = [];
    }

    $labels = [];
    $values = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = $i === 0 ? (date('M j', strtotime($d)) . ' · today') : date('M j', strtotime($d));
        $values[] = $map[$d] ?? 0;
    }

    return ['labels' => $labels, 'values' => $values];
}

function analyticsCountUniqueSince(PDO $pdo, string $sinceDate): int
{
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT visitor_hash)
            FROM site_daily_visitors
            WHERE visit_date >= ?
        ");
        $stmt->execute([$sinceDate]);
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function analyticsCountUniqueOn(PDO $pdo, string $date): int
{
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM site_daily_visitors WHERE visit_date = ?
        ");
        $stmt->execute([$date]);
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/** Distinct networks (IPs) since date — useful alongside hybrid visitor counts. */
function analyticsCountUniqueNetworksSince(PDO $pdo, string $sinceDate): int
{
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT ip_address)
            FROM site_daily_visitors
            WHERE visit_date >= ?
              AND ip_address IS NOT NULL
              AND ip_address <> ''
        ");
        $stmt->execute([$sinceDate]);
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * Unique visitors by country (for world map).
 * @return array{series: array<string,int>, names: array<string,string>, top: list<array{code:string,name:string,city:?string,count:int}>, total: int}
 */
function analyticsCountryBreakdown(PDO $pdo, int $days = 30): array
{
    $days = max(7, min(365, $days));
    $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
    $series = [];
    $names = [];
    $top = [];
    $total = 0;

    try {
        $stmt = $pdo->prepare("
            SELECT
                UPPER(country_code) AS code,
                MAX(country_name) AS name,
                COUNT(*) AS c
            FROM site_daily_visitors
            WHERE visit_date >= ?
              AND country_code IS NOT NULL
              AND country_code <> ''
            GROUP BY UPPER(country_code)
            ORDER BY c DESC
        ");
        $stmt->execute([$since]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $code = (string) $row['code'];
            $count = (int) $row['c'];
            $name = (string) ($row['name'] ?: $code);
            $series[$code] = $count;
            $names[$code] = $name;
            $total += $count;
            $top[] = [
                'code' => $code,
                'name' => $name,
                'city' => null,
                'count' => $count,
            ];
        }

        // Enrich top list with most common city per country (optional detail)
        $cityStmt = $pdo->prepare("
            SELECT UPPER(country_code) AS code, city, COUNT(*) AS c
            FROM site_daily_visitors
            WHERE visit_date >= ?
              AND country_code IS NOT NULL
              AND city IS NOT NULL
              AND city <> ''
            GROUP BY UPPER(country_code), city
            ORDER BY c DESC
        ");
        $cityStmt->execute([$since]);
        $cityBest = [];
        foreach ($cityStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $code = (string) $row['code'];
            if (!isset($cityBest[$code])) {
                $cityBest[$code] = (string) $row['city'];
            }
        }
        foreach ($top as &$item) {
            $item['city'] = $cityBest[$item['code']] ?? null;
        }
        unset($item);
    } catch (Throwable $e) {
        return ['series' => [], 'names' => [], 'top' => [], 'total' => 0];
    }

    return [
        'series' => $series,
        'names' => $names,
        // Keep every located country in the list; small places like HK are easy to miss on the map.
        'top' => $top,
        'total' => $total,
    ];
}

/**
 * Approximate city / region points for map drill-down (last N days).
 * Coordinates come from IP lookup when available, otherwise a small city fallback table.
 *
 * @return array<string, list<array{city:string,region:?string,count:int,lat:float,lon:float}>>
 */
function analyticsCityLocations(PDO $pdo, int $days = 30): array
{
    $days = max(7, min(365, $days));
    $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
    $byCountry = [];

    try {
        $stmt = $pdo->prepare("
            SELECT
                UPPER(country_code) AS code,
                COALESCE(NULLIF(TRIM(city), ''), 'Unknown area') AS city,
                MAX(NULLIF(TRIM(region), '')) AS region,
                AVG(latitude) AS lat,
                AVG(longitude) AS lon,
                COUNT(*) AS c
            FROM site_daily_visitors
            WHERE visit_date >= ?
              AND country_code IS NOT NULL
              AND country_code <> ''
            GROUP BY UPPER(country_code), COALESCE(NULLIF(TRIM(city), ''), 'Unknown area')
            ORDER BY c DESC
        ");
        $stmt->execute([$since]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $code = (string) $row['code'];
            $city = (string) $row['city'];
            $count = (int) $row['c'];
            $lat = isset($row['lat']) && $row['lat'] !== null && $row['lat'] !== '' ? (float) $row['lat'] : null;
            $lon = isset($row['lon']) && $row['lon'] !== null && $row['lon'] !== '' ? (float) $row['lon'] : null;

            if (($lat === null || $lon === null) && $city !== 'Unknown area') {
                $fallback = analyticsCityFallbackCoords($city, $code);
                if ($fallback) {
                    $lat = $fallback[0];
                    $lon = $fallback[1];
                }
            }

            if ($lat === null || $lon === null) {
                continue;
            }

            if (!isset($byCountry[$code])) {
                $byCountry[$code] = [];
            }
            $byCountry[$code][] = [
                'city' => $city,
                'region' => isset($row['region']) && $row['region'] !== '' ? (string) $row['region'] : null,
                'count' => $count,
                'lat' => round($lat, 5),
                'lon' => round($lon, 5),
            ];
        }
    } catch (Throwable $e) {
        return [];
    }

    return $byCountry;
}

/**
 * Record a project card view or outbound launch click (deduped per visitor / 24h).
 * @param 'view'|'click' $eventType
 */
function trackProjectEvent(PDO $pdo, int $projectId, string $eventType): bool
{
    $projectId = max(0, $projectId);
    $eventType = $eventType === 'click' ? 'click' : 'view';
    if ($projectId <= 0) {
        return false;
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (analyticsIsBot($userAgent)) {
        return false;
    }

    $cookieName = 'sila_pj_' . $eventType . '_' . $projectId;
    if (!empty($_COOKIE[$cookieName])) {
        return false;
    }

    $visitorId = analyticsEnsureVisitorId();
    $ip = analyticsClientIp();
    $visitorHash = hash('sha256', $visitorId . '|' . $ip . '|' . $userAgent . '|project|' . $eventType);

    try {
        $check = $pdo->prepare("
            SELECT COUNT(*) FROM project_events
            WHERE project_id = ? AND event_type = ? AND visitor_hash = ?
              AND created_at >= (NOW() - INTERVAL 24 HOUR)
        ");
        $check->execute([$projectId, $eventType, $visitorHash]);
        if ((int) $check->fetchColumn() > 0) {
            if (!headers_sent()) {
                setcookie($cookieName, '1', [
                    'expires' => time() + 86400,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }
            return false;
        }

        $pdo->prepare("
            INSERT INTO project_events (project_id, event_type, visitor_hash, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ")->execute([$projectId, $eventType, $visitorHash, $ip, substr($userAgent, 0, 255)]);

        $column = $eventType === 'click' ? 'clicks' : 'views';
        $pdo->prepare("UPDATE projects SET `{$column}` = `{$column}` + 1 WHERE id = ?")->execute([$projectId]);

        if (!headers_sent()) {
            setcookie($cookieName, '1', [
                'expires' => time() + 86400,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        $_COOKIE[$cookieName] = '1';
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Daily unique people who viewed or launched any project.
 * @return array{labels: string[], views: int[], clicks: int[]}
 */
function analyticsDailyProjectInterestSeries(PDO $pdo, int $days = 30): array
{
    $days = max(7, min(90, $days));
    $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
    $viewMap = [];
    $clickMap = [];
    try {
        $stmt = $pdo->prepare("
            SELECT DATE(created_at) AS d, event_type, COUNT(DISTINCT visitor_hash) AS c
            FROM project_events
            WHERE created_at >= ?
            GROUP BY DATE(created_at), event_type
            ORDER BY d ASC
        ");
        $stmt->execute([$since . ' 00:00:00']);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $d = (string) $row['d'];
            if ($row['event_type'] === 'click') {
                $clickMap[$d] = (int) $row['c'];
            } else {
                $viewMap[$d] = (int) $row['c'];
            }
        }
    } catch (Throwable $e) {
        // table may not exist yet
    }

    $labels = [];
    $views = [];
    $clicks = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = $i === 0 ? (date('M j', strtotime($d)) . ' · today') : date('M j', strtotime($d));
        $views[] = $viewMap[$d] ?? 0;
        $clicks[] = $clickMap[$d] ?? 0;
    }

    return ['labels' => $labels, 'views' => $views, 'clicks' => $clicks];
}

/**
 * @return list<array{id:int,title:string,url:?string,views:int,clicks:int}>
 */
function analyticsTopProjects(PDO $pdo, int $limit = 12): array
{
    $limit = max(1, min(30, $limit));
    try {
        $stmt = $pdo->query("
            SELECT id, title, url, views, clicks
            FROM projects
            ORDER BY views DESC, clicks DESC, id DESC
            LIMIT {$limit}
        ");
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'title' => (string) ($row['title'] ?? 'Untitled'),
                'url' => $row['url'] !== null && $row['url'] !== '' ? (string) $row['url'] : null,
                'views' => (int) ($row['views'] ?? 0),
                'clicks' => (int) ($row['clicks'] ?? 0),
            ];
        }, $rows);
    } catch (Throwable $e) {
        return [];
    }
}

/** @return list<string> */
function analyticsKnownPageKeys(): array
{
    return [
        'home', 'about', 'education', 'experience', 'skills', 'projects',
        'blog', 'research', 'contact', 'publications', 'lab', 'saas', 'now',
        'tools', 'uses', 'reading', 'guestbook', 'faq', 'talks', 'friends',
        'oss', 'resources', 'changelog', 'colophon', 'sitemap',
    ];
}

function analyticsNormalizePageKey(string $pageKey): string
{
    $key = strtolower(trim($pageKey));
    $key = preg_replace('/[^a-z0-9_\-]/', '', $key) ?? '';
    $aliases = [
        'employment' => 'experience',
        'work' => 'experience',
        'index' => 'home',
        'post' => 'blog',
        'open-source' => 'oss',
        'opensource' => 'oss',
        'reverse-engineering' => 'research',
        '404' => 'other',
    ];
    if (isset($aliases[$key])) {
        $key = $aliases[$key];
    }
    if ($key === '' || !in_array($key, analyticsKnownPageKeys(), true)) {
        return 'other';
    }
    return $key;
}

function analyticsPageKeyLabel(string $pageKey): string
{
    $labels = [
        'home' => 'Home',
        'about' => 'About',
        'education' => 'Education',
        'experience' => 'Experience',
        'skills' => 'Skills',
        'projects' => 'Projects',
        'blog' => 'Blog',
        'research' => 'Research',
        'contact' => 'Contact',
        'publications' => 'Publications',
        'lab' => 'Lab',
        'saas' => 'SaaS',
        'now' => 'Now',
        'tools' => 'Tools',
        'uses' => 'Uses',
        'reading' => 'Reading',
        'guestbook' => 'Guestbook',
        'faq' => 'FAQ',
        'talks' => 'Talks',
        'friends' => 'Friends',
        'oss' => 'Open source',
        'resources' => 'Resources',
        'changelog' => 'Changelog',
        'colophon' => 'Colophon',
        'sitemap' => 'Sitemap',
        'other' => 'Other',
    ];
    $key = analyticsNormalizePageKey($pageKey);
    return $labels[$key] ?? ucfirst($key);
}

function analyticsEnsurePageViewsTable(PDO $pdo): void
{
    static $ready = false;
    if ($ready) {
        return;
    }
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS site_page_views (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            visit_date DATE NOT NULL,
            page_key VARCHAR(64) NOT NULL,
            visitor_hash CHAR(64) NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            path VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_page_visitor_day (visit_date, page_key, visitor_hash),
            KEY idx_page_date (page_key, visit_date),
            KEY idx_visit_date (visit_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $ready = true;
}

/**
 * One count per visitor + page section per calendar day.
 */
function trackSitePageView(PDO $pdo, string $pageKey): void
{
    if (PHP_SAPI === 'cli') {
        return;
    }

    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (strpos($script, '/admin/') !== false) {
        return;
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (analyticsIsBot($userAgent)) {
        return;
    }

    $ip = analyticsClientIp();
    if ($ip === '') {
        return;
    }

    $pageKey = analyticsNormalizePageKey($pageKey);
    $visitorHash = analyticsVisitorHash($ip, $userAgent);
    $visitDate = date('Y-m-d');
    $cookieName = 'sila_pg_' . $pageKey . '_' . date('Ymd') . '_' . substr($visitorHash, 0, 10);
    if (!empty($_COOKIE[$cookieName])) {
        return;
    }

    $path = substr((string) ($_SERVER['REQUEST_URI'] ?? '/'), 0, 255);

    try {
        analyticsEnsurePageViewsTable($pdo);
        $pdo->prepare("
            INSERT IGNORE INTO site_page_views
                (visit_date, page_key, visitor_hash, ip_address, user_agent, path, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ")->execute([
            $visitDate,
            $pageKey,
            $visitorHash,
            substr($ip, 0, 45),
            substr($userAgent, 0, 255),
            $path,
        ]);

        if (!headers_sent()) {
            setcookie($cookieName, '1', [
                'expires' => strtotime('tomorrow') - 1,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        $_COOKIE[$cookieName] = '1';
    } catch (Throwable $e) {
        // Ignore quietly on older deploys / locked DBs
    }
}

/**
 * Unique people per page section over the window.
 * @return array{labels: string[], values: int[], keys: string[]}
 */
function analyticsTopPages(PDO $pdo, int $days = 30, int $limit = 12): array
{
    $days = max(7, min(90, $days));
    $limit = max(1, min(30, $limit));
    $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
    $labels = [];
    $values = [];
    $keys = [];
    try {
        analyticsEnsurePageViewsTable($pdo);
        $stmt = $pdo->prepare("
            SELECT page_key, COUNT(DISTINCT visitor_hash) AS c
            FROM site_page_views
            WHERE visit_date >= ?
            GROUP BY page_key
            ORDER BY c DESC, page_key ASC
            LIMIT {$limit}
        ");
        $stmt->execute([$since]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $key = (string) $row['page_key'];
            $keys[] = $key;
            $labels[] = analyticsPageKeyLabel($key);
            $values[] = (int) $row['c'];
        }
    } catch (Throwable $e) {
        return ['labels' => [], 'values' => [], 'keys' => []];
    }
    return ['labels' => $labels, 'values' => $values, 'keys' => $keys];
}

/**
 * Daily distinct page-section views (person+page counted once per day, summed).
 * @return array{labels: string[], values: int[]}
 */
function analyticsDailyPageViewsSeries(PDO $pdo, int $days = 30): array
{
    $days = max(7, min(90, $days));
    $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
    $map = [];
    try {
        analyticsEnsurePageViewsTable($pdo);
        $stmt = $pdo->prepare("
            SELECT visit_date AS d, COUNT(*) AS c
            FROM site_page_views
            WHERE visit_date >= ?
            GROUP BY visit_date
            ORDER BY visit_date ASC
        ");
        $stmt->execute([$since]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[(string) $row['d']] = (int) $row['c'];
        }
    } catch (Throwable $e) {
        $map = [];
    }

    $labels = [];
    $values = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = $i === 0 ? (date('M j', strtotime($d)) . ' · today') : date('M j', strtotime($d));
        $values[] = $map[$d] ?? 0;
    }
    return ['labels' => $labels, 'values' => $values];
}

function analyticsNormalizeLandingLabel(string $path): string
{
    $path = strtolower(trim(parse_url($path, PHP_URL_PATH) ?: $path));
    $path = preg_replace('#/+#', '/', $path) ?? $path;
    $base = basename($path);
    $base = preg_replace('/\.(php|html?)$/', '', $base) ?? $base;
    if ($base === '' || $base === '/' || $base === 'index') {
        return 'Home';
    }
    $map = [
        'about' => 'About',
        'education' => 'Education',
        'experience' => 'Experience',
        'skills' => 'Skills',
        'projects' => 'Projects',
        'blog' => 'Blog',
        'post' => 'Blog post',
        'research' => 'Research',
        'contact' => 'Contact',
        'publications' => 'Publications',
        'lab' => 'Lab',
        'saas' => 'SaaS',
        'now' => 'Now',
        'tools' => 'Tools',
        'uses' => 'Uses',
        'reading' => 'Reading',
        'guestbook' => 'Guestbook',
        'faq' => 'FAQ',
        'talks' => 'Talks',
        'friends' => 'Friends',
        'open-source' => 'Open source',
        'resources' => 'Resources',
        'changelog' => 'Changelog',
        'colophon' => 'Colophon',
        'sitemap' => 'Sitemap',
        'reverse-engineering' => 'Research',
    ];
    if (isset($map[$base])) {
        return $map[$base];
    }
    return ucwords(str_replace(['-', '_'], ' ', $base));
}

/**
 * First-hit landing paths from unique daily visitors.
 * @return array{labels: string[], values: int[]}
 */
function analyticsLandingBreakdown(PDO $pdo, int $days = 30, int $limit = 8): array
{
    $days = max(7, min(90, $days));
    $limit = max(1, min(20, $limit));
    $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
    $bucket = [];
    try {
        $stmt = $pdo->prepare("
            SELECT landing_path, COUNT(*) AS c
            FROM site_daily_visitors
            WHERE visit_date >= ? AND landing_path IS NOT NULL AND landing_path != ''
            GROUP BY landing_path
            ORDER BY c DESC
        ");
        $stmt->execute([$since]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $label = analyticsNormalizeLandingLabel((string) $row['landing_path']);
            $bucket[$label] = ($bucket[$label] ?? 0) + (int) $row['c'];
        }
    } catch (Throwable $e) {
        return ['labels' => [], 'values' => []];
    }

    arsort($bucket);
    $labels = [];
    $values = [];
    $i = 0;
    foreach ($bucket as $label => $count) {
        if ($i >= $limit) {
            break;
        }
        $labels[] = $label;
        $values[] = (int) $count;
        $i++;
    }
    return ['labels' => $labels, 'values' => $values];
}

/**
 * Browser family mix from daily unique visitors.
 * @return array{labels: string[], values: int[]}
 */
function analyticsBrowserBreakdown(PDO $pdo, int $days = 30): array
{
    $days = max(7, min(90, $days));
    $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
    $bucket = [];
    try {
        $stmt = $pdo->prepare("
            SELECT user_agent
            FROM site_daily_visitors
            WHERE visit_date >= ?
        ");
        $stmt->execute([$since]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $family = analyticsBrowserFamily($row['user_agent'] ?? '');
            $label = match ($family) {
                'chrome' => 'Chrome',
                'edge' => 'Edge',
                'firefox' => 'Firefox',
                'safari' => 'Safari',
                'opera' => 'Opera',
                'ie' => 'IE',
                default => 'Other',
            };
            $bucket[$label] = ($bucket[$label] ?? 0) + 1;
        }
    } catch (Throwable $e) {
        return ['labels' => [], 'values' => []];
    }

    arsort($bucket);
    return [
        'labels' => array_keys($bucket),
        'values' => array_map('intval', array_values($bucket)),
    ];
}

/**
 * Launch rate for top projects by views.
 * @return array{labels: string[], rates: float[], views: int[], clicks: int[]}
 */
function analyticsProjectConversion(PDO $pdo, int $limit = 10): array
{
    $limit = max(1, min(30, $limit));
    $labels = [];
    $rates = [];
    $views = [];
    $clicks = [];
    try {
        $stmt = $pdo->query("
            SELECT title, views, clicks
            FROM projects
            WHERE views > 0
            ORDER BY views DESC, clicks DESC, id DESC
            LIMIT {$limit}
        ");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $title = trim((string) ($row['title'] ?? 'Untitled')) ?: 'Untitled';
            if (function_exists('mb_strlen') && mb_strlen($title) > 36) {
                $title = mb_substr($title, 0, 33) . '…';
            } elseif (strlen($title) > 36) {
                $title = substr($title, 0, 33) . '…';
            }
            $v = (int) ($row['views'] ?? 0);
            $c = (int) ($row['clicks'] ?? 0);
            $labels[] = $title;
            $views[] = $v;
            $clicks[] = $c;
            $rates[] = $v > 0 ? round(($c / $v) * 100, 1) : 0.0;
        }
    } catch (Throwable $e) {
        return ['labels' => [], 'rates' => [], 'views' => [], 'clicks' => []];
    }
    return ['labels' => $labels, 'rates' => $rates, 'views' => $views, 'clicks' => $clicks];
}

/** Top page label for a single day (or empty string). */
function analyticsTopPageOn(PDO $pdo, string $date): string
{
    try {
        analyticsEnsurePageViewsTable($pdo);
        $stmt = $pdo->prepare("
            SELECT page_key, COUNT(DISTINCT visitor_hash) AS c
            FROM site_page_views
            WHERE visit_date = ?
            GROUP BY page_key
            ORDER BY c DESC, page_key ASC
            LIMIT 1
        ");
        $stmt->execute([$date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return '';
        }
        return analyticsPageKeyLabel((string) $row['page_key']);
    } catch (Throwable $e) {
        return '';
    }
}

function analyticsDistinctLandingsSince(PDO $pdo, string $sinceDate): int
{
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT landing_path)
            FROM site_daily_visitors
            WHERE visit_date >= ? AND landing_path IS NOT NULL AND landing_path != ''
        ");
        $stmt->execute([$sinceDate]);
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function analyticsAverageProjectConversion(PDO $pdo): float
{
    try {
        $row = $pdo->query("
            SELECT COALESCE(SUM(views), 0) AS v, COALESCE(SUM(clicks), 0) AS c
            FROM projects
            WHERE views > 0
        ")->fetch(PDO::FETCH_ASSOC);
        $v = (int) ($row['v'] ?? 0);
        $c = (int) ($row['c'] ?? 0);
        return $v > 0 ? round(($c / $v) * 100, 1) : 0.0;
    } catch (Throwable $e) {
        return 0.0;
    }
}
