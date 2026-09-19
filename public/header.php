<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/education.php';
ensureEducationDetails($pdo);

$preferredLogos = [
  'Tanui-Sila-Logo-v3.png',
  'Tanui-Sila-Logo-v3.jpg',
  'Tanui-Sila-Logo.png',
  'Tanui-Sila-Logo.jpg',
  'Tanui-Sila-Logo.jpeg',
  'logo.png',
  'logo.jpg',
];
$logoFile = 'Tanui-Sila-Logo-v3.png';
foreach ($preferredLogos as $fileName) {
  $candidate = __DIR__ . '/../' . $fileName;
  if (file_exists($candidate)) {
    $logoFile = $fileName;
    break;
  }
}

$profile = [
  'full_name' => 'Your Name',
  'headline' => 'Developer • Designer • Creator',
  'bio' => 'I build thoughtful digital experiences and impactful products.',
  'email' => 'you@example.com',
  'phone' => '',
  'location' => 'Your City, Country',
  'website' => '',
  'linkedin' => '',
  'github' => '',
  'summary' => '',
];

try {
  $profileRow = $pdo->query('SELECT * FROM profile WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
  if ($profileRow) {
    $profile = $profileRow;
  }
} catch (Throwable $e) {
  $profile = $profile;
}

$settings = [
  'site_title' => $profile['full_name'],
  'tagline' => $profile['headline'],
  'hero_text' => '',
  'show_blog' => '1',
  'theme' => 'light',
];

try {
  $settingsRows = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll(PDO::FETCH_ASSOC);
  foreach ($settingsRows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
  }
} catch (Throwable $e) {
  $settings = $settings;
}

$about = $pdo->query('SELECT content FROM about WHERE id=1')->fetchColumn();
if ($about === false || trim((string)$about) === '') {
  $about = $profile['bio'];
}

$projects = $pdo->query('SELECT * FROM projects ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);

$skillsRaw = $pdo->query('SELECT name, level FROM skills ORDER BY level DESC')->fetchAll(PDO::FETCH_ASSOC);
$skills = [];
$seenSkills = [];
foreach ($skillsRaw as $s) {
  $skillNameKey = strtolower(trim($s['name']));
  if (!in_array($skillNameKey, $seenSkills) && !empty($skillNameKey)) {
    $seenSkills[] = $skillNameKey;
    $skills[] = $s;
  }
}

$contact = $pdo->query('SELECT * FROM contact WHERE id=1')->fetch(PDO::FETCH_ASSOC);

$education = $pdo->query('SELECT * FROM education ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
$experience = $pdo->query('SELECT * FROM experience ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
$publications = $pdo->query('SELECT * FROM publications ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);

function portfolioAssetUrl(string $assetPath): string {
  $assetPath = trim($assetPath);
  if ($assetPath === '' || preg_match('#^(https?:)?//#i', $assetPath)) {
    return $assetPath;
  }

  $appBase = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/public/index.php'));
  $appBase = $appBase === '/' || $appBase === '\\' ? '' : rtrim(str_replace('\\', '/', $appBase), '/');
  $assetPath = '/' . ltrim($assetPath, '/');

  if (strpos($assetPath, '/tanuisila/') === 0) {
    $assetPath = substr($assetPath, strlen('/tanuisila'));
  }

  return $appBase . '/' . ltrim($assetPath, '/');
}

function appBasePath(): string {
  $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
  $scriptDir = trim(dirname($scriptName), '/');

  if ($scriptDir === '' || $scriptDir === '.') {
    return '';
  }

  $segments = array_values(array_filter(explode('/', $scriptDir), static function ($segment) {
    return $segment !== '';
  }));

  if (!empty($segments) && end($segments) === 'public') {
    array_pop($segments);
  }

  return empty($segments) ? '' : '/' . implode('/', $segments);
}

function appUrl(string $path = ''): string {
  $base = appBasePath();
  $path = ltrim($path, '/');

  if ($path === '') {
    return $base === '' ? '/' : $base . '/';
  }

  return ($base === '' ? '' : $base) . '/' . $path;
}

if (!isset($activePage)) {
  $activePage = 'home';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo htmlspecialchars($settings['site_title'] ?? $profile['full_name']); ?></title>
  <link rel="icon" href="<?php echo appUrl('public/logo_with_bg.jpg'); ?>?v=<?php echo @filemtime(__DIR__ . '/logo_with_bg.jpg'); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($profile['full_name']); ?> - Portfolio">
  <meta property="og:description" content="<?php echo htmlspecialchars($profile['summary'] ?: $profile['bio']); ?>">
  <meta property="og:image" content="<?php echo appUrl('public/logo_with_bg.jpg'); ?>?v=<?php echo @filemtime(__DIR__ . '/logo_with_bg.jpg'); ?>">
  <meta name="theme-color" content="#ffffff">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo appUrl('assets/css/styles.css'); ?>?v=<?php echo @filemtime(__DIR__ . '/../assets/css/styles.css'); ?>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" crossorigin="anonymous">
  <style>
    /* ═══════════════════════════════════════════════════════
       TOPBAR - dark info strip
    ═══════════════════════════════════════════════════════ */
    .tb {
      background: #0a0f1e;
      border-bottom: 1px solid rgba(255,255,255,0.07);
      position: relative;
      z-index: 110;
    }
    .tb::before {
      content: '';
      display: block;
      height: 2px;
      background: linear-gradient(90deg, var(--accent), #f97316, var(--accent-muted), var(--accent));
      background-size: 200% 100%;
      animation: tb-shimmer 4s linear infinite;
    }
    @keyframes tb-shimmer {
      0%   { background-position: 0% 0; }
      100% { background-position: 200% 0; }
    }
    .tb__inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      min-height: 44px;
      padding-top: 0;
      padding-bottom: 0;
    }
    .tb__left {
      display: flex;
      align-items: stretch;
    }
    .tb__status {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 0 18px;
      height: 44px;
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: rgba(255,255,255,0.4);
      border-right: 1px solid rgba(255,255,255,0.07);
      white-space: nowrap;
    }
    .tb__dot {
      width: 7px;
      height: 7px;
      background: #22c55e;
      border-radius: 50%;
      flex-shrink: 0;
      animation: tb-pulse 2s ease-in-out infinite;
    }
    @keyframes tb-pulse {
      0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.55); }
      50%       { box-shadow: 0 0 0 5px rgba(34,197,94,0); }
    }
    .tb__item {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 0 18px;
      height: 44px;
      font-size: 0.76rem;
      font-weight: 600;
      color: rgba(255,255,255,0.6);
      text-decoration: none;
      border-right: 1px solid rgba(255,255,255,0.07);
      white-space: nowrap;
      letter-spacing: 0.02em;
      transition: color 0.2s ease, background 0.2s ease;
    }
    .tb__item:hover {
      color: #fff;
      background: rgba(255,255,255,0.05);
      opacity: 1;
    }
    .tb__item i {
      font-size: 0.78rem;
      color: var(--accent);
      flex-shrink: 0;
    }
    .tb__right {
      display: flex;
      align-items: stretch;
      margin-left: auto;
    }
    .tb__social {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 44px;
      height: 44px;
      font-size: 0.95rem;
      color: rgba(255,255,255,0.45);
      border-left: 1px solid rgba(255,255,255,0.07);
      text-decoration: none;
      transition: color 0.2s ease, background 0.2s ease;
    }
    .tb__social:hover {
      color: #fff;
      background: rgba(255,255,255,0.07);
      opacity: 1;
    }
    .tb__cv {
      display: flex;
      align-items: center;
      gap: 7px;
      padding: 0 20px;
      height: 44px;
      font-size: 0.71rem;
      font-weight: 800;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: #fff;
      background: var(--accent);
      border-left: 1px solid rgba(255,255,255,0.07);
      text-decoration: none;
      white-space: nowrap;
      transition: background 0.2s ease;
    }
    .tb__cv:hover { background: var(--accent-muted); opacity: 1; }
    .tb__cv i { font-size: 0.8rem; }

    /* ═══════════════════════════════════════════════════════
       MAIN STICKY HEADER
    ═══════════════════════════════════════════════════════ */
    .sh {
      position: sticky;
      top: 0;
      z-index: 100;
      background: rgba(248,249,250,0.95);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
      border-bottom: 1px solid var(--light-gray);
    }
    .sh__inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      min-height: 72px;
      gap: 28px;
      padding-top: 0;
      padding-bottom: 0;
    }
    .sh__brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      flex-shrink: 0;
    }
    .sh__logo {
      width: 42px;
      height: 42px;
      object-fit: contain;
      border: 1px solid var(--light-gray);
      background: #fff;
      padding: 4px;
      display: block;
    }
    .sh__brand-text { min-width: 0; }
    .sh__name {
      margin: 0;
      font-size: 1.1rem;
      font-weight: 800;
      letter-spacing: -0.04em;
      color: var(--text);
      line-height: 1.1;
    }
    .sh__role {
      margin: 3px 0 0;
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--muted);
      line-height: 1;
    }
    /* Nav */
    .sh__nav {
      display: flex;
      align-items: center;
      gap: 2px;
      flex: 1;
      justify-content: center;
    }
    .sh__nav-link {
      display: flex;
      align-items: center;
      gap: 5px;
      padding: 6px 11px;
      font-size: 0.77rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: var(--muted);
      text-decoration: none;
      position: relative;
      white-space: nowrap;
      transition: color 0.2s ease;
    }
    .sh__nav-link i { font-size: 0.7rem; opacity: 0.65; }
    .sh__nav-link::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 11px;
      right: 11px;
      height: 2px;
      background: var(--accent);
      transform: scaleX(0);
      transform-origin: right;
      transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .sh__nav-link:hover { color: var(--text); opacity: 1; }
    .sh__nav-link:hover::after { transform: scaleX(1); transform-origin: left; }
    .sh__nav-link.active { color: var(--accent) !important; opacity: 1 !important; }
    .sh__nav-link.active::after { transform: scaleX(1); }
    /* CTA */
    .sh__actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
    .sh__contact-btn {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 10px 18px;
      font-size: 0.74rem;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      background: var(--text);
      color: #fff;
      text-decoration: none;
      border: 1px solid var(--text);
      transition: background 0.2s ease, border-color 0.2s ease;
    }
    .sh__contact-btn:hover { background: var(--accent); border-color: var(--accent); opacity: 1; }
    .sh__contact-btn i { font-size: 0.76rem; }
    /* Hamburger */
    .sh__toggle {
      display: none;
      background: none;
      border: 1px solid var(--light-gray);
      color: var(--text);
      width: 40px;
      height: 40px;
      cursor: pointer;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
      transition: background 0.2s ease;
    }
    .sh__toggle:hover { background: rgba(15,23,42,0.06); }
    /* Mobile drawer */
    .sh__drawer { display: none; }
    .sh__drawer.open {
      display: flex;
      flex-direction: column;
      border-top: 1px solid var(--light-gray);
      padding: 10px 0 16px;
      gap: 1px;
    }
    .sh__drawer-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 16px;
      font-size: 0.82rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: var(--muted);
      text-decoration: none;
      transition: background 0.15s, color 0.15s;
    }
    .sh__drawer-link i { font-size: 0.82rem; color: var(--accent); width: 16px; text-align: center; flex-shrink: 0; }
    .sh__drawer-link:hover, .sh__drawer-link.active { background: rgba(234,88,12,0.06); color: var(--accent); opacity: 1; }
    .sh__drawer-divider { height: 1px; background: var(--light-gray); margin: 8px 16px; }
    .sh__drawer-contact { display: flex; flex-direction: column; gap: 8px; padding: 10px 16px; }
    .sh__drawer-contact a {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.82rem;
      color: var(--muted);
      text-decoration: none;
      transition: color 0.15s;
    }
    .sh__drawer-contact a i { color: var(--accent); width: 14px; text-align: center; flex-shrink: 0; }
    .sh__drawer-contact a:hover { color: var(--text); opacity: 1; }

    /* ── Responsive ─────────────────────────────────────── */
    @media (max-width: 1180px) {
      .sh__inner { gap: 16px; }
      .sh__nav { gap: 0; }
      .sh__nav-link { padding: 6px 7px; font-size: 0.72rem; gap: 4px; }
      .sh__contact-btn { padding: 8px 14px; font-size: 0.72rem; }
    }
    @media (max-width: 990px) {
      .sh__nav    { display: none; }
      .sh__actions { display: none; }
      .sh__toggle { display: flex; }
    }
    @media (max-width: 640px) {
      .tb { display: none; }
    }
  </style>
  <script>
    function shToggle() {
      const drawer = document.getElementById('sh-drawer');
      const btn    = document.getElementById('sh-toggle');
      const open   = drawer.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      btn.innerHTML = open
        ? '<i class="fa-solid fa-xmark"></i>'
        : '<i class="fa-solid fa-bars"></i>';
    }
  </script>
</head>
<body>

  <!-- ═══════════════════════ TOPBAR ═══════════════════════ -->
  <div class="tb" role="banner">
    <div class="container tb__inner">

      <!-- LEFT: status + contact info -->
      <div class="tb__left">
        <div class="tb__status">
          <span class="tb__dot"></span>
          Available
        </div>
        <a class="tb__item" href="mailto:silatanuikipngetich@gmail.com" title="Send email">
          <i class="fa-solid fa-envelope"></i>
          silatanuikipngetich@gmail.com
        </a>
        <a class="tb__item" href="https://wa.me/254742178644" target="_blank" rel="noopener noreferrer" title="Chat on WhatsApp">
          <i class="fa-brands fa-whatsapp"></i>
          +254 742 178 644
        </a>
      </div>

      <!-- RIGHT: social icons + CV button -->
      <div class="tb__right">
        <?php if (!empty($profile['github'])): ?>
        <a class="tb__social" href="<?php echo htmlspecialchars($profile['github']); ?>" target="_blank" rel="noopener noreferrer" title="GitHub" aria-label="GitHub">
          <i class="fa-brands fa-github"></i>
        </a>
        <?php endif; ?>
        <?php if (!empty($profile['linkedin'])): ?>
        <a class="tb__social" href="<?php echo htmlspecialchars($profile['linkedin']); ?>" target="_blank" rel="noopener noreferrer" title="LinkedIn" aria-label="LinkedIn">
          <i class="fa-brands fa-linkedin"></i>
        </a>
        <?php endif; ?>
        <a class="tb__cv" href="<?php echo appUrl('public/Sila_Kipng\'etich_Tanui_CV.pdf'); ?>" target="_blank" rel="noopener noreferrer">
          <i class="fa-solid fa-file-arrow-down"></i> Download CV
        </a>
      </div>

    </div>
  </div>

  <!-- ═══════════════════════ MAIN HEADER ═══════════════════════ -->
  <header class="sh">
    <div class="container sh__inner">

      <!-- Brand -->
      <a href="<?php echo appUrl('index.php'); ?>" class="sh__brand">
        <img src="<?php echo htmlspecialchars(appUrl($logoFile)); ?>" alt="Logo" class="sh__logo">
        <div class="sh__brand-text">
          <h1 class="sh__name"><?php echo htmlspecialchars($settings['site_title'] ?? $profile['full_name']); ?></h1>
          <p class="sh__role">Computer Scientist</p>
        </div>
      </a>

      <!-- Desktop nav with icons -->
      <nav class="sh__nav" aria-label="Main navigation">
        <a href="<?php echo appUrl('index.php'); ?>"      class="sh__nav-link <?php echo $activePage === 'home'       ? 'active' : ''; ?>"><i class="fa-solid fa-house"></i> Home</a>
        <a href="<?php echo appUrl('about.php'); ?>"      class="sh__nav-link <?php echo $activePage === 'about'      ? 'active' : ''; ?>"><i class="fa-solid fa-user"></i> About</a>
        <a href="<?php echo appUrl('education.php'); ?>"  class="sh__nav-link <?php echo $activePage === 'education'  ? 'active' : ''; ?>"><i class="fa-solid fa-graduation-cap"></i> Education</a>
        <a href="<?php echo appUrl('experience.php'); ?>" class="sh__nav-link <?php echo $activePage === 'experience' ? 'active' : ''; ?>"><i class="fa-solid fa-briefcase"></i> Experience</a>
        <a href="<?php echo appUrl('skills.php'); ?>"     class="sh__nav-link <?php echo $activePage === 'skills'     ? 'active' : ''; ?>"><i class="fa-solid fa-layer-group"></i> Skills</a>
        <a href="<?php echo appUrl('projects.php'); ?>"   class="sh__nav-link <?php echo $activePage === 'projects'   ? 'active' : ''; ?>"><i class="fa-solid fa-code"></i> Projects</a>
        <a href="<?php echo appUrl('blog.php'); ?>"       class="sh__nav-link <?php echo $activePage === 'blog'       ? 'active' : ''; ?>"><i class="fa-solid fa-pen-nib"></i> Blog</a>
      </nav>

      <!-- Desktop CTA -->
      <div class="sh__actions">
        <a class="sh__contact-btn" href="<?php echo appUrl('contact.php'); ?>">
          <i class="fa-solid fa-paper-plane"></i> Get in Touch
        </a>
      </div>

      <!-- Mobile hamburger -->
      <button id="sh-toggle" class="sh__toggle" onclick="shToggle()" aria-label="Toggle navigation" aria-expanded="false" aria-controls="sh-drawer">
        <i class="fa-solid fa-bars"></i>
      </button>

    </div>

    <!-- Mobile slide-down drawer -->
    <div id="sh-drawer" class="sh__drawer container">
      <a href="<?php echo appUrl('index.php'); ?>"      class="sh__drawer-link <?php echo $activePage === 'home'       ? 'active' : ''; ?>"><i class="fa-solid fa-house"></i> Home</a>
      <a href="<?php echo appUrl('about.php'); ?>"      class="sh__drawer-link <?php echo $activePage === 'about'      ? 'active' : ''; ?>"><i class="fa-solid fa-user"></i> About</a>
      <a href="<?php echo appUrl('education.php'); ?>"  class="sh__drawer-link <?php echo $activePage === 'education'  ? 'active' : ''; ?>"><i class="fa-solid fa-graduation-cap"></i> Education</a>
      <a href="<?php echo appUrl('experience.php'); ?>" class="sh__drawer-link <?php echo $activePage === 'experience' ? 'active' : ''; ?>"><i class="fa-solid fa-briefcase"></i> Experience</a>
      <a href="<?php echo appUrl('skills.php'); ?>"     class="sh__drawer-link <?php echo $activePage === 'skills'     ? 'active' : ''; ?>"><i class="fa-solid fa-layer-group"></i> Skills</a>
      <a href="<?php echo appUrl('projects.php'); ?>"   class="sh__drawer-link <?php echo $activePage === 'projects'   ? 'active' : ''; ?>"><i class="fa-solid fa-code"></i> Projects</a>
      <a href="<?php echo appUrl('blog.php'); ?>"       class="sh__drawer-link <?php echo $activePage === 'blog'       ? 'active' : ''; ?>"><i class="fa-solid fa-pen-nib"></i> Blog</a>
      <a href="<?php echo appUrl('contact.php'); ?>"    class="sh__drawer-link <?php echo $activePage === 'contact'    ? 'active' : ''; ?>"><i class="fa-solid fa-paper-plane"></i> Contact</a>
      <div class="sh__drawer-divider"></div>
      <div class="sh__drawer-contact">
        <a href="mailto:silatanuikipngetich@gmail.com"><i class="fa-solid fa-envelope"></i> silatanuikipngetich@gmail.com</a>
        <a href="https://wa.me/254742178644" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp"></i> +254 742 178 644</a>
        <?php if (!empty($profile['github'])): ?>
        <a href="<?php echo htmlspecialchars($profile['github']); ?>" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-github"></i> GitHub Profile</a>
        <?php endif; ?>
        <?php if (!empty($profile['linkedin'])): ?>
        <a href="<?php echo htmlspecialchars($profile['linkedin']); ?>" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-linkedin"></i> LinkedIn Profile</a>
        <?php endif; ?>
      </div>
    </div>
  </header>
