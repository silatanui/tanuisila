<?php

/**
 * Durable admin login throttle (survives new browser sessions).
 */
function adminLoginClientIp(): string {
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    return $ip !== '' ? $ip : '0.0.0.0';
}

function adminLoginIpHash(string $ip = ''): string {
    if ($ip === '') {
        $ip = adminLoginClientIp();
    }
    $pepper = defined('ADMIN_KEY') ? (string) ADMIN_KEY : 'tanui-admin';
    return hash('sha256', $pepper . '|' . $ip);
}

function adminLoginThrottleStatus(PDO $pdo): array {
    $defaults = [
        'locked' => false,
        'failures' => 0,
        'retry_after' => 0,
        'max_attempts' => 5,
        'lockout_seconds' => 15 * 60,
    ];

    try {
        $stmt = $pdo->prepare('SELECT failures, locked_until FROM admin_login_throttle WHERE ip_hash = :ip_hash LIMIT 1');
        $stmt->execute([':ip_hash' => adminLoginIpHash()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return $defaults;
    }

    if (!$row) {
        return $defaults;
    }

    $failures = (int) ($row['failures'] ?? 0);
    $lockedUntil = $row['locked_until'] ?? null;
    $retryAfter = 0;
    $locked = false;

    if ($lockedUntil) {
        $untilTs = strtotime((string) $lockedUntil);
        if ($untilTs !== false && $untilTs > time()) {
            $locked = true;
            $retryAfter = $untilTs - time();
        }
    }

    return [
        'locked' => $locked,
        'failures' => $failures,
        'retry_after' => $retryAfter,
        'max_attempts' => $defaults['max_attempts'],
        'lockout_seconds' => $defaults['lockout_seconds'],
    ];
}

function adminLoginRegisterFailure(PDO $pdo): array {
    $status = adminLoginThrottleStatus($pdo);
    $max = (int) $status['max_attempts'];
    $lockoutSeconds = (int) $status['lockout_seconds'];
    $ipHash = adminLoginIpHash();

    if ($status['locked']) {
        return $status;
    }

    $failures = (int) $status['failures'] + 1;
    $lockedUntil = null;

    if ($failures >= $max) {
        $lockedUntil = date('Y-m-d H:i:s', time() + $lockoutSeconds);
        $failures = $max;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO admin_login_throttle (ip_hash, failures, locked_until)
             VALUES (:ip_hash, :failures, :locked_until)
             ON DUPLICATE KEY UPDATE
               failures = VALUES(failures),
               locked_until = VALUES(locked_until)'
        );
        $stmt->execute([
            ':ip_hash' => $ipHash,
            ':failures' => $failures,
            ':locked_until' => $lockedUntil,
        ]);
    } catch (Throwable $e) {
        // Fall through with in-memory status if table is unavailable.
    }

    // Small delay makes automated guessing slower without blocking humans much.
    usleep(350000);

    return [
        'locked' => $lockedUntil !== null,
        'failures' => $failures,
        'retry_after' => $lockedUntil !== null ? $lockoutSeconds : 0,
        'max_attempts' => $max,
        'lockout_seconds' => $lockoutSeconds,
    ];
}

function adminLoginClearThrottle(PDO $pdo): void {
    try {
        $stmt = $pdo->prepare('DELETE FROM admin_login_throttle WHERE ip_hash = :ip_hash');
        $stmt->execute([':ip_hash' => adminLoginIpHash()]);
    } catch (Throwable $e) {
        // Ignore cleanup failures.
    }
}
