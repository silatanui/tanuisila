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
        $badgeHtml = '<span class="topbar-badge" aria-label="' . $unreadCount . ' unread">' . $unreadCount . '</span>';
    }

    return '
    <header class="topbar">
        <div class="topbar-lead">
          <button type="button" class="sidebar-open" id="sidebar-open" aria-label="Open menu" aria-controls="admin-sidebar" aria-expanded="false">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
          </button>
          <h1>' . htmlspecialchars($pageTitle) . '</h1>
        </div>
        <div class="top-actions">
          <a href="messages.php" class="btn top-action top-action--msg">
            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
            <span class="top-action__label">Messages</span>
            ' . $badgeHtml . '
          </a>
          <a class="btn top-action top-action--site" href="../index.php" target="_blank" rel="noopener">
            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
            <span class="top-action__label">Live site</span>
          </a>
          <a class="btn danger top-action top-action--logout" href="logout.php">
            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
            <span class="top-action__label">Logout</span>
          </a>
        </div>
    </header>';
}
?>
