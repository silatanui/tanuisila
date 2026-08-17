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
        <div class="top-actions" style="display:flex; align-items:center; gap:16px;">
          <a href="messages.php" style="position:relative; text-decoration:none; color:var(--text); font-size:1.25rem;">
            <i class="fa-solid fa-envelope"></i>
            ' . $badgeHtml . '
          </a>
          <a class="btn" href="../public/index.php" target="_blank">Preview</a>
          <a class="btn danger" href="logout.php">Logout</a>
        </div>
    </header>';
}
?>
