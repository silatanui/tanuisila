<?php
require_once __DIR__ . '/../config/config.php';

function renderTopbar($pageTitle = 'Dashboard') {
    global $pdo;
    $unreadCount = 0;
    try {
        $unreadCount = (int)$pdo->query('SELECT COUNT(*) FROM `messages` WHERE `is_read` = 0')->fetchColumn();
    } catch (Throwable $e) {}
    
    $badgeHtml = '';
    if ($unreadCount > 0) {
        $badgeHtml = '<span style="position:absolute; top:-4px; right:-6px; background:#ef4444; color:#fff; border-radius:999px; width:16px; height:16px; font-size:0.62rem; font-weight:800; display:flex; align-items:center; justify-content:center;">' . $unreadCount . '</span>';
    }

    return '
    <header class="topbar">
        <h1>' . htmlspecialchars($pageTitle) . '</h1>
        <div class="top-actions" style="display:flex; align-items:center; gap:12px;">
          <a href="messages.php" class="btn" style="position:relative; text-decoration:none; padding: 9px 14px; background: #ffffff;">
            <i class="fa-solid fa-envelope" style="color: var(--primary);"></i> Messages
            ' . $badgeHtml . '
          </a>
          <a class="btn" href="../index.php" target="_blank" style="background: #ffffff;">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> View Live Site
          </a>
          <a class="btn danger" href="logout.php">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
          </a>
        </div>
    </header>';
}
?>
