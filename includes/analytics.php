<?php
/**
 * Analytics and View Tracking Module
 * Provides device-specific, non-refreshing view tracking with bot filtering and cooldowns.
 */

function trackPostViewDeviceSpecific(PDO $pdo, array &$post): void {
    $postId = (int)($post['id'] ?? 0);
    if ($postId <= 0) {
        return;
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // 1. Exclude Automated Bots, Scrapers & Crawlers
    if (preg_match('/bot|crawl|slurp|spider|mediapartners|lighthouse|headless|curl|wget|python|facebookexternalhit/i', $userAgent)) {
        return;
    }

    // 2. Device Cookie Fast-Path (skips DB if already counted on this device)
    $cookieName = 'sila_pv_' . $postId;
    if (!empty($_COOKIE[$cookieName])) {
        return;
    }

    // 3. Anonymous Device UUID
    if (empty($_COOKIE['sila_vid'])) {
        $visitorId = bin2hex(random_bytes(16));
        if (!headers_sent()) {
            setcookie('sila_vid', $visitorId, [
                'expires' => time() + (86400 * 365), // 1 year device token
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        $_COOKIE['sila_vid'] = $visitorId;
    } else {
        $visitorId = preg_replace('/[^a-zA-Z0-9]/', '', (string)$_COOKIE['sila_vid']);
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $visitorHash = hash('sha256', $visitorId . '|' . $ip . '|' . $userAgent);

    try {
        // 4. Verify in DB if this device/visitor viewed this post within the 24-hour window
        $checkStmt = $pdo->prepare("
            SELECT COUNT(*) FROM blog_post_views 
            WHERE post_id = ? AND visitor_hash = ? AND viewed_at >= (NOW() - INTERVAL 24 HOUR)
        ");
        $checkStmt->execute([$postId, $visitorHash]);
        $alreadyCounted = (int)$checkStmt->fetchColumn();

        if ($alreadyCounted === 0) {
            // Log this unique device view
            $logStmt = $pdo->prepare("
                INSERT INTO blog_post_views (post_id, visitor_hash, ip_address, user_agent, viewed_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            $logStmt->execute([$postId, $visitorHash, $ip, substr($userAgent, 0, 255)]);

            // Increment post views in database
            $pdo->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = ?')->execute([$postId]);
            $post['views'] = ((int)($post['views'] ?? 0)) + 1;
        }

        // Set device cookie for 24 hours
        if (!headers_sent()) {
            setcookie($cookieName, '1', [
                'expires' => time() + 86400,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        $_COOKIE[$cookieName] = '1';
    } catch (Throwable $e) {
        // Fallback: set cookie to prevent rapid loops in case of any database glitch
        if (!headers_sent()) {
            setcookie($cookieName, '1', time() + 86400, '/');
        }
    }
}
