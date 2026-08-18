<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/education.php';
ensureEducationDetails($pdo);

$preferredLogos = [
  '../Tanui-Sila-Logo-v3.png',
  '../Tanui-Sila-Logo-v3.jpg',
  '../Tanui-Sila-Logo.png',
  '../Tanui-Sila-Logo.jpg',
  'logo.png',
  'logo.jpg',
  '../Tanui-Sila-Logo.jpeg',
];
$logoFile = '../Tanui-Sila-Logo-v3.png';
foreach ($preferredLogos as $fileName) {
  $candidate = __DIR__ . '/' . $fileName;
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

if (!isset($activePage)) {
  $activePage = 'home';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <?php if (basename(dirname($_SERVER['SCRIPT_NAME'] ?? '')) !== 'public'): ?>
  <base href="public/">
  <?php endif; ?>
  <title><?php echo htmlspecialchars($settings['site_title'] ?? $profile['full_name']); ?></title>
  <link rel="icon" href="logo_with_bg.jpg?v=<?php echo @filemtime(__DIR__ . '/logo_with_bg.jpg'); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($profile['full_name']); ?> — Portfolio">
  <meta property="og:description" content="<?php echo htmlspecialchars($profile['summary'] ?: $profile['bio']); ?>">
  <meta property="og:image" content="logo_with_bg.jpg?v=<?php echo @filemtime(__DIR__ . '/logo_with_bg.jpg'); ?>">
  <meta name="theme-color" content="#ffffff">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo @filemtime(__DIR__ . '/../assets/css/styles.css'); ?>">
  <style>
    /* Premium high density typography transitions for navigation links */
    .top-nav a {
      position: relative;
      padding: 6px 0;
      transition: color 0.2s ease, opacity 0.2s ease;
    }
    .top-nav a::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 2px;
      background-color: var(--accent);
      transform: scaleX(0);
      transform-origin: right;
      transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .top-nav a:hover::after {
      transform: scaleX(1);
      transform-origin: left;
    }
    .top-nav a.active {
      color: var(--accent) !important;
      opacity: 1 !important;
    }
    .top-nav a.active::after {
      transform: scaleX(1);
      background-color: var(--accent);
    }
    /* Topbar styles */
    .site-topbar {
      background-color: var(--text);
      color: var(--bg);
      padding: 10px 0;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .site-topbar .container {
      display: flex;
      justify-content: flex-start;
      align-items: center;
      flex-wrap: wrap;
      gap: 20px;
    }
    .topbar-left {
      display: flex;
      gap: 12px;
      align-items: center;
      font-size: 0.85rem;
      flex-wrap: wrap;
    }
    .topbar-left a {
      color: var(--bg);
      text-decoration: none;
      transition: opacity 0.2s ease;
    }
    .topbar-left a:hover {
      opacity: 0.8;
    }
    .topbar-left .separator {
      color: var(--bg);
      opacity: 0.5;
    }
    .topbar-left {
      color: var(--bg);
    }
    .cv-btn {
      color: var(--text);
      text-decoration: none;
      border: 2px solid var(--text);
      padding: 10px 20px;
      border-radius: 4px;
      font-weight: 600;
      font-size: 0.85rem;
      transition: all 0.2s ease;
      display: inline-block;
    }
    .cv-btn:hover {
      background-color: var(--text);
      color: var(--bg);
    }
    .header-toggle {
      display: none !important;
      background: none;
      border: none;
      color: var(--text);
      font-size: 1.7rem;
      line-height: 1;
      cursor: pointer;
      padding: 8px 12px;
      font-family: 'Segoe UI Symbol', 'Arial Unicode MS', sans-serif;
    }
    .header-toggle:hover {
      opacity: 0.8;
    }
    .header-menu {
      display: contents;
    }
    .topbar-mobile {
      display: none;
    }
    .site-header .brand {
      display: flex;
      align-items: center;
      flex-direction: row;
      gap: 10px;
      min-width: 0;
    }
    .site-header .brand > div {
      min-width: 0;
    }
    @media (max-width: 1024px) {
      .site-topbar {
        display: none;
      }
      .site-header .container {
        flex-wrap: wrap;
        gap: 12px;
      }
      .site-header .brand {
        flex-direction: row;
        gap: 10px;
      }
      .header-toggle {
        display: flex !important;
        align-items: center;
        justify-content: center;
        order: 3;
      }
      .top-nav {
        display: none;
        order: 4;
        flex-basis: 100%;
        flex-direction: column;
        gap: 8px;
        padding: 16px 0;
        border-top: 1px solid var(--line);
      }
      .top-nav.active {
        display: flex;
      }
      .header-menu.active .topbar-mobile {
        display: flex;
        order: 4;
        flex-basis: 100%;
        flex-direction: column;
        gap: 8px;
        padding: 12px 0;
        border-top: 1px solid var(--line);
        font-size: 0.85rem;
      }
      .topbar-mobile a {
        color: var(--text);
        text-decoration: none;
        padding: 4px 0;
      }
      .topbar-mobile a:hover {
        opacity: 0.7;
      }
      .topbar-mobile .separator {
        display: none;
      }
      div[style*="display: flex"][style*="gap: 12px"] {
        order: 5;
        flex-basis: 100%;
        gap: 8px;
        padding-top: 12px;
        border-top: 1px solid var(--line);
        margin-top: 12px;
      }
      .cv-btn, .header-cta {
        flex: 1;
        text-align: center;
        padding: 12px 8px;
      }
    }
  </style>
  <script>
    function toggleHeaderMenu(event) {
      event.preventDefault();
      const nav = document.querySelector('.top-nav');
      const menu = document.querySelector('.header-menu');
      nav.classList.toggle('active');
      menu.classList.toggle('active');
    }
  </script>
</head>
<body>
  <div class="site-topbar">
    <div class="container">
      <div class="topbar-content">
        <div class="topbar-left">
          <a href="mailto:silatanuikipngetich@gmail.com" title="Send email">silatanuikipngetich@gmail.com</a>
          <span class="separator">·</span>
          <a href="https://wa.me/254742178644" target="_blank" rel="noopener noreferrer" title="Chat on WhatsApp">+254 742 178 644</a>
          <span class="separator">·</span>
          <?php if (!empty($profile['github'])): ?>
            <a href="<?php echo htmlspecialchars($profile['github']); ?>" target="_blank" rel="noopener noreferrer" title="GitHub">GitHub</a>
          <?php endif; ?>
          <?php if (!empty($profile['github']) && !empty($profile['linkedin'])): ?>
            <span class="separator">·</span>
          <?php endif; ?>
          <?php if (!empty($profile['linkedin'])): ?>
            <a href="<?php echo htmlspecialchars($profile['linkedin']); ?>" target="_blank" rel="noopener noreferrer" title="LinkedIn">LinkedIn</a>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
  <header class="site-header">
    <div class="container">
      <a href="index.php" class="brand">
        <img src="<?php echo htmlspecialchars($logoFile); ?>" alt="Logo" class="logo">
        <div>
          <h1><?php echo htmlspecialchars($settings['site_title'] ?? $profile['full_name']); ?></h1>
          <p style="margin: 2px 0 0; color: var(--muted); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; line-height: 1;">Computer Scientist</p>
        </div>
      </a>
      <button class="header-toggle" onclick="toggleHeaderMenu(event)" title="Toggle menu" aria-label="Toggle menu">
        ☰
      </button>
      <nav class="top-nav">
        <a href="index.php" class="<?php echo $activePage === 'home' ? 'active' : ''; ?>">Home</a>
        <a href="about.php" class="<?php echo $activePage === 'about' ? 'active' : ''; ?>">About</a>
        <a href="education.php" class="<?php echo $activePage === 'education' ? 'active' : ''; ?>">Education</a>
        <a href="experience.php" class="<?php echo $activePage === 'experience' ? 'active' : ''; ?>">Experience</a>
        <a href="skills.php" class="<?php echo $activePage === 'skills' ? 'active' : ''; ?>">Skills</a>
        <a href="research.php" class="<?php echo $activePage === 'research' ? 'active' : ''; ?>">Research</a>
        <a href="blog.php" class="<?php echo $activePage === 'blog' ? 'active' : ''; ?>">Blog</a>
        <a href="contact.php" class="<?php echo $activePage === 'contact' ? 'active' : ''; ?>">Contact</a>
      </nav>
      <div class="header-menu">
        <div class="topbar-mobile">
          <a href="mailto:silatanuikipngetich@gmail.com" title="Send email">Email: silatanuikipngetich@gmail.com</a>
          <a href="https://wa.me/254742178644" target="_blank" rel="noopener noreferrer" title="Chat on WhatsApp">WhatsApp: +254 742 178 644</a>
          <?php if (!empty($profile['github'])): ?>
            <a href="<?php echo htmlspecialchars($profile['github']); ?>" target="_blank" rel="noopener noreferrer" title="GitHub">GitHub Profile</a>
          <?php endif; ?>
          <?php if (!empty($profile['linkedin'])): ?>
            <a href="<?php echo htmlspecialchars($profile['linkedin']); ?>" target="_blank" rel="noopener noreferrer" title="LinkedIn">LinkedIn Profile</a>
          <?php endif; ?>
        </div>
      </div>
      <div style="display: flex; gap: 12px; align-items: center;">
        <a class="cv-btn" href="Sila_Kipng'etich_Tanui_CV.pdf" target="_blank">Download CV</a>
        <a class="header-cta" href="contact.php">Get in touch</a>
      </div>
    </div>
  </header>
