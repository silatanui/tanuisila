<?php
require_once __DIR__ . '/../config/config.php';

function renderSidebar($active = '') {
    global $pdo;
    $msgCount = 0;
    $pendingComments = 0;
    try {
        $msgCount = (int)$pdo->query('SELECT COUNT(*) FROM `messages` WHERE `is_read` = 0')->fetchColumn();
        $pendingComments = (int)$pdo->query("SELECT COUNT(*) FROM `blog_comments` WHERE `status` = 'pending'")->fetchColumn();
    } catch (Throwable $e) {}

    $current = $active !== '' ? $active : basename($_SERVER['PHP_SELF'] ?? '');

    $sections = [
        [
            'label' => 'Operate',
            'links' => [
                'index.php' => ['icon' => 'fa-chart-pie', 'label' => 'Overview', 'hint' => 'Dashboard'],
                'messages.php' => ['icon' => 'fa-inbox', 'label' => 'Messages', 'hint' => 'Inbox', 'badge' => $msgCount],
                'comments.php' => ['icon' => 'fa-comments', 'label' => 'Comments', 'hint' => 'Moderation', 'badge' => $pendingComments],
            ],
        ],
        [
            'label' => 'Content',
            'links' => [
                'blog.php' => ['icon' => 'fa-pen-nib', 'label' => 'Blog posts', 'hint' => 'Essays'],
                'projects.php' => ['icon' => 'fa-rocket', 'label' => 'Projects', 'hint' => 'Portfolio'],
                'research_projects.php' => ['icon' => 'fa-flask', 'label' => 'Research', 'hint' => 'Labs'],
                'publications.php' => ['icon' => 'fa-book-open', 'label' => 'Publications', 'hint' => 'Writing'],
            ],
        ],
        [
            'label' => 'Profile',
            'links' => [
                'profile.php' => ['icon' => 'fa-id-card', 'label' => 'Personal details', 'hint' => 'About'],
                'education.php' => ['icon' => 'fa-graduation-cap', 'label' => 'Education', 'hint' => 'School'],
                'experience.php' => ['icon' => 'fa-briefcase', 'label' => 'Experience', 'hint' => 'Work'],
            ],
        ],
        [
            'label' => 'System',
            'links' => [
                'settings.php' => ['icon' => 'fa-sliders', 'label' => 'Settings', 'hint' => 'Config'],
                'logo.php' => ['icon' => 'fa-image', 'label' => 'Logo', 'hint' => 'Brand'],
            ],
        ],
    ];

    $html = '
    <div class="sidebar-backdrop" id="sidebar-backdrop" hidden></div>
    <aside class="sidebar" id="admin-sidebar" aria-label="Admin navigation">
      <div class="sidebar-inner">
        <div class="brand-box">
          <a class="brand-link" href="index.php" title="Admin overview">
            <span class="brand-mark">
              <img src="../Tanui-Sila-Logo-v3.png" alt="">
            </span>
            <span class="brand-text">
              <strong>Tanui Sila</strong>
              <span class="brand-chip">Admin cockpit</span>
            </span>
          </a>
          <button type="button" class="sidebar-close" id="sidebar-close" aria-label="Close menu">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
          </button>
        </div>

        <nav class="nav">';

    foreach ($sections as $section) {
        $html .= '<div class="nav-section">';
        $html .= '<p class="nav-section__label">' . htmlspecialchars($section['label'], ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '<div class="nav-section__links">';

        foreach ($section['links'] as $url => $data) {
            $isActive = ($current === $url) ? ' active' : '';
            $badge = isset($data['badge']) ? (int)$data['badge'] : 0;
            $badgeHtml = $badge > 0
                ? '<span class="badge" aria-label="' . $badge . ' pending">' . $badge . '</span>'
                : '';
            $hint = isset($data['hint']) ? htmlspecialchars($data['hint'], ENT_QUOTES, 'UTF-8') : '';

            $html .= '<a class="nav-link' . $isActive . '" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"'
                . ($isActive ? ' aria-current="page"' : '')
                . ' title="' . htmlspecialchars($data['label'], ENT_QUOTES, 'UTF-8') . '">'
                . '<span class="nav-link__icon"><i class="fa-solid ' . htmlspecialchars($data['icon'], ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></i></span>'
                . '<span class="nav-link__copy">'
                . '<span class="nav-link__label">' . htmlspecialchars($data['label'], ENT_QUOTES, 'UTF-8') . '</span>'
                . ($hint !== '' ? '<span class="nav-link__hint">' . $hint . '</span>' : '')
                . '</span>'
                . $badgeHtml
                . '</a>';
        }

        $html .= '</div></div>';
    }

    $html .= '
        </nav>

        <div class="sidebar-footer">
          <a class="sidebar-foot-link sidebar-foot-link--site" href="../index.php" target="_blank" rel="noopener">
            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
            <span>View portfolio</span>
          </a>
          <a class="sidebar-foot-link sidebar-foot-link--logout" href="logout.php">
            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
            <span>Logout</span>
          </a>
        </div>
      </div>
    </aside>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
      var sidebar = document.getElementById("admin-sidebar");
      var backdrop = document.getElementById("sidebar-backdrop");
      var openBtn = document.getElementById("sidebar-open");
      var closeBtn = document.getElementById("sidebar-close");
      if (!sidebar) return;

      function setOpen(open) {
        document.body.classList.toggle("sidebar-open", open);
        if (backdrop) {
          if (open) backdrop.removeAttribute("hidden");
          else backdrop.setAttribute("hidden", "");
        }
        if (openBtn) openBtn.setAttribute("aria-expanded", open ? "true" : "false");
        if (window.matchMedia("(max-width: 980px)").matches) {
          sidebar.setAttribute("aria-hidden", open ? "false" : "true");
        } else {
          sidebar.removeAttribute("aria-hidden");
        }
      }

      if (openBtn) openBtn.addEventListener("click", function () { setOpen(true); });
      if (closeBtn) closeBtn.addEventListener("click", function () { setOpen(false); });
      if (backdrop) backdrop.addEventListener("click", function () { setOpen(false); });
      document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") setOpen(false);
      });
      window.addEventListener("resize", function () {
        if (window.innerWidth > 980) setOpen(false);
      });
      setOpen(false);
    });
    </script>';

    return $html;
}
?>
