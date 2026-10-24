<?php
/**
 * Analytics — unique visitor tracking (site + blog posts).
 * Site uniqueness: one count per visitor device cookie per calendar day.
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

    // Fast path: already counted today on this device
    $dayKey = 'sila_site_' . date('Ymd');
    if (!empty($_COOKIE[$dayKey])) {
        return;
    }

    $visitorId = analyticsEnsureVisitorId();
    if ($visitorId === '') {
        return;
    }

    // Cookie alone identifies the device for unique daily counts
    $visitorHash = hash('sha256', 'site|' . $visitorId);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $path = substr((string) ($_SERVER['REQUEST_URI'] ?? '/'), 0, 255);
    $visitDate = date('Y-m-d');

    try {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO site_daily_visitors
                (visit_date, visitor_hash, ip_address, user_agent, landing_path, first_seen)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $visitDate,
            $visitorHash,
            substr($ip, 0, 45),
            substr($userAgent, 0, 255),
            $path,
        ]);

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
    $map = [];
    try {
        $stmt = $pdo->prepare("
            SELECT visit_date AS d, COUNT(*) AS c
            FROM site_daily_visitors
            WHERE visit_date >= (CURDATE() - INTERVAL ? DAY)
            GROUP BY visit_date
            ORDER BY visit_date ASC
        ");
        $stmt->execute([$days - 1]);
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
        $labels[] = date('M j', strtotime($d));
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
    $map = [];
    try {
        $stmt = $pdo->prepare("
            SELECT DATE(viewed_at) AS d, COUNT(DISTINCT visitor_hash) AS c
            FROM blog_post_views
            WHERE viewed_at >= (CURDATE() - INTERVAL ? DAY)
            GROUP BY DATE(viewed_at)
            ORDER BY d ASC
        ");
        $stmt->execute([$days - 1]);
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
        $labels[] = date('M j', strtotime($d));
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
