<?php
require_once __DIR__ . '/../config/config.php';

function renderSidebar($active = '') {
    global $pdo;
    $msgCount = 0;
    try {
        $msgCount = (int)$pdo->query('SELECT COUNT(*) FROM `messages` WHERE `is_read` = 0')->fetchColumn();
    } catch (Throwable $e) {}

    $links = [
        'index.php' => ['icon' => 'fa-chart-pie', 'label' => 'Overview'],
        'messages.php' => ['icon' => 'fa-envelope', 'label' => 'Inbox Messages', 'badge' => $msgCount],
        'profile.php' => ['icon' => 'fa-user', 'label' => 'Personal Details'],
        'education.php' => ['icon' => 'fa-graduation-cap', 'label' => 'Education'],
        'experience.php' => ['icon' => 'fa-briefcase', 'label' => 'Work Experience'],
        'publications.php' => ['icon' => 'fa-book-open', 'label' => 'Publications'],
        'blog.php' => ['icon' => 'fa-pen-nib', 'label' => 'Blog Posts'],
        'projects.php' => ['icon' => 'fa-rocket', 'label' => 'Projects'],
        'settings.php' => ['icon' => 'fa-gear', 'label' => 'Settings'],
        'logo.php' => ['icon' => 'fa-image', 'label' => 'Logo']
    ];
    
    $html = '
    <aside class="sidebar">
      <div class="brand-box">
        <img src="../Tanui-Sila-Logo-v3.png" alt="Tanui Sila logo">
        <div class="brand-text">
          <strong>Tanui Sila</strong>
          <span>Admin</span>
        </div>
      </div>
      <nav class="nav">';
      
    foreach ($links as $url => $data) {
        $isActive = ($active === $url || basename($_SERVER['PHP_SELF']) === $url) ? ' class="active"' : '';
        $badgeHtml = '';
        if (isset($data['badge']) && $data['badge'] > 0) {
            $badgeHtml = '<span class="badge">' . $data['badge'] . '</span>';
        }
        $html .= '<a href="' . $url . '"' . $isActive . '><i class="fa-solid ' . $data['icon'] . '"></i><span>' . $data['label'] . '</span>' . $badgeHtml . '</a>';
    }
    
    $html .= '
      </nav>
      <div class="sidebar-footer">
        <a href="../public/index.php" target="_blank">View portfolio</a>
        <a href="logout.php">Logout</a>
      </div>
    </aside>';
    
    return $html;
}
?>
