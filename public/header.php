<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/education.php';
require_once __DIR__ . '/../includes/media.php';
require_once __DIR__ . '/../includes/analytics.php';
ensureEducationDetails($pdo);
trackSiteUniqueVisit($pdo);

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

// Main projects shown in the navbar (matches https://tanuisila.dev/projects.php).
$navProjectAllowTitles = [
  'quire',
  'tanui university',
  'flyleaf',
  'cv studio',
  'tanui commerce',
  'lyricsync',
];
$navProjects = array_values(array_filter($projects, static function (array $project) use ($navProjectAllowTitles): bool {
  $title = strtolower(trim((string) ($project['title'] ?? '')));
  return $title !== '' && in_array($title, $navProjectAllowTitles, true);
}));
// Preserve live page order when present.
usort($navProjects, static function (array $a, array $b) use ($navProjectAllowTitles): int {
  $ai = array_search(strtolower(trim((string) ($a['title'] ?? ''))), $navProjectAllowTitles, true);
  $bi = array_search(strtolower(trim((string) ($b['title'] ?? ''))), $navProjectAllowTitles, true);
  $ai = $ai === false ? 999 : $ai;
  $bi = $bi === false ? 999 : $bi;
  return $ai <=> $bi;
});

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
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Figtree:wght@600;700;800&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo appUrl('assets/css/styles.css'); ?>?v=<?php echo @filemtime(__DIR__ . '/../assets/css/styles.css'); ?>">
  <link rel="stylesheet" href="<?php echo appUrl('assets/css/extras.css'); ?>?v=<?php echo @filemtime(__DIR__ . '/../assets/css/extras.css'); ?>">
  <link rel="stylesheet" href="<?php echo appUrl('assets/css/print.css'); ?>?v=<?php echo @filemtime(__DIR__ . '/../assets/css/print.css'); ?>" media="print">
  <link rel="stylesheet" href="<?php echo appUrl('assets/css/brand-name.css'); ?>?v=<?php echo @filemtime(__DIR__ . '/../assets/css/brand-name.css'); ?>">
  <link rel="alternate" type="application/rss+xml" title="Blog RSS" href="<?php echo appUrl('feed.php'); ?>">
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
      transition: background 0.2s ease, box-shadow 0.2s ease;
    }
    .sh.is-scrolled {
      background: rgba(248,249,250,0.98);
      box-shadow: 0 1px 0 rgba(15,23,42,0.06);
    }
    .sh__inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      min-height: 72px;
      gap: 28px;
      padding-top: 0;
      padding-bottom: 0;
      transition: min-height 0.22s ease;
    }
    .sh.is-scrolled .sh__inner {
      min-height: 58px;
    }
    .sh.is-scrolled .sh__logo {
      width: 36px;
      height: 36px;
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
      font-family: var(--font-brand-name);
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

    /* Projects dropdown */
    .sh__drop {
      position: relative;
      display: flex;
      align-items: center;
    }
    .sh__drop-trigger {
      display: flex;
      align-items: center;
      gap: 2px;
      position: relative;
      white-space: nowrap;
    }
    .sh__drop-link {
      display: flex;
      align-items: center;
      gap: 5px;
      padding: 6px 4px 6px 11px;
      font-size: 0.77rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: var(--muted);
      text-decoration: none;
      transition: color 0.2s ease;
    }
    .sh__drop-link i.fa-code { font-size: 0.7rem; opacity: 0.65; }
    .sh__drop-toggle {
      display: grid;
      place-items: center;
      width: 26px;
      height: 28px;
      padding: 0;
      margin: 0;
      border: 0;
      background: transparent;
      color: var(--muted);
      cursor: pointer;
      transition: color 0.2s ease;
    }
    .sh__drop-chevron {
      font-size: 0.58rem;
      opacity: 0.7;
      transition: transform 0.2s ease;
    }
    .sh__drop-trigger::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 11px;
      right: 8px;
      height: 2px;
      background: var(--accent);
      transform: scaleX(0);
      transform-origin: right;
      transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .sh__drop-trigger:hover .sh__drop-link,
    .sh__drop-trigger:hover .sh__drop-toggle,
    .sh__drop.is-open .sh__drop-link,
    .sh__drop.is-open .sh__drop-toggle,
    .sh__drop-toggle[aria-expanded="true"] {
      color: var(--text);
    }
    .sh__drop-link:hover { opacity: 1; }
    .sh__drop-trigger:hover::after,
    .sh__drop.is-open .sh__drop-trigger::after,
    .sh__drop-trigger.is-active::after {
      transform: scaleX(1);
      transform-origin: left;
    }
    .sh__drop-trigger.is-active .sh__drop-link,
    .sh__drop-trigger.is-active .sh__drop-toggle {
      color: var(--accent) !important;
    }
    .sh__drop.is-open .sh__drop-chevron,
    .sh__drop-toggle[aria-expanded="true"] .sh__drop-chevron {
      transform: rotate(180deg);
    }
    .sh__drop-menu {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      min-width: 300px;
      max-width: min(92vw, 360px);
      max-height: min(70vh, 480px);
      overflow-y: auto;
      padding: 8px 0;
      background: #fff;
      border: 1px solid var(--light-gray);
      box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
      z-index: 400;
    }
    .sh__drop-menu.is-open {
      display: block;
    }
    .sh__drop-item {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      padding: 10px 16px;
      text-decoration: none;
      color: var(--text);
      transition: background 0.15s ease;
    }
    .sh__drop-item:hover {
      background: rgba(234, 88, 12, 0.06);
      opacity: 1;
    }
    .sh__drop-item__copy {
      display: flex;
      flex-direction: column;
      gap: 2px;
      min-width: 0;
    }
    .sh__drop-item__title {
      font-size: 0.84rem;
      font-weight: 700;
      letter-spacing: -0.01em;
      line-height: 1.25;
      color: var(--text);
    }
    .sh__drop-item__meta {
      font-size: 0.68rem;
      font-weight: 600;
      color: var(--muted);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 220px;
    }
    .sh__drop-item__icon {
      margin-top: 2px;
      color: var(--accent);
      font-size: 0.72rem;
      flex-shrink: 0;
      opacity: 0.85;
    }
    .sh__drop-foot {
      display: block;
      margin-top: 4px;
      padding: 11px 16px;
      border-top: 1px solid var(--light-gray);
      font-size: 0.72rem;
      font-weight: 800;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--accent);
      text-decoration: none;
    }
    .sh__drop-foot:hover {
      background: rgba(234, 88, 12, 0.05);
      opacity: 1;
    }
    .sh__drawer-sub {
      display: none;
      flex-direction: column;
      padding: 0 0 6px 28px;
      gap: 2px;
    }
    .sh__drawer-sub.is-open { display: flex; }
    .sh__drawer-sub a {
      display: block;
      padding: 8px 12px;
      font-size: 0.78rem;
      font-weight: 600;
      color: var(--muted);
      text-decoration: none;
      text-transform: none;
      letter-spacing: 0;
    }
    .sh__drawer-sub a:hover {
      color: var(--accent);
      opacity: 1;
    }
    .sh__drawer-projects-row {
      display: flex;
      align-items: center;
      gap: 0;
    }
    .sh__drawer-projects-row .sh__drawer-link {
      flex: 1;
      min-width: 0;
    }
    .sh__drawer-projects-btn {
      width: 44px;
      height: 42px;
      display: grid;
      place-items: center;
      padding: 0;
      font: inherit;
      color: var(--muted);
      background: transparent;
      border: 0;
      cursor: pointer;
      flex-shrink: 0;
    }
    .sh__drawer-projects-btn .sh__drop-chevron {
      color: var(--muted);
    }
    .sh__drawer-projects-btn.active,
    .sh__drawer-projects-btn[aria-expanded="true"] {
      color: var(--accent);
      background: rgba(234, 88, 12, 0.06);
    }
    .sh__drawer-projects-btn[aria-expanded="true"] .sh__drop-chevron {
      transform: rotate(180deg);
    }

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
      .sh__drop-link { padding: 6px 2px 6px 7px; font-size: 0.72rem; gap: 4px; }
      .sh__drop-toggle { width: 22px; height: 26px; }
      .sh__drop-menu { left: 0; right: auto; min-width: 260px; }
      .sh__contact-btn { padding: 8px 14px; font-size: 0.72rem; }
      .tb__item .tb__label { display: none; }
      .tb__item { padding: 0 14px; }
    }
    @media (max-width: 990px) {
      .sh__nav    { display: none; }
      .sh__actions { display: none; }
      .sh__toggle { display: flex; }
      /* Align with hamburger: contact lives in the drawer below this width */
      .tb { display: none; }
      .sh__inner { min-height: 64px; gap: 12px; }
      .sh__brand { min-width: 0; }
      .sh__brand-text { min-width: 0; overflow: hidden; }
      .sh__name {
        font-size: 1rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: min(52vw, 220px);
      }
    }
    @media (max-width: 420px) {
      .sh__logo { width: 36px; height: 36px; }
      .sh__name { font-size: 0.92rem; max-width: 46vw; }
      .sh__role { display: none; }
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

    function shPositionProjectsMenu() {
      const trigger = document.getElementById('sh-projects-trigger');
      const menu = document.getElementById('sh-projects-menu');
      if (!trigger || !menu) return;
      const rect = trigger.getBoundingClientRect();
      const menuWidth = Math.min(360, Math.max(280, menu.offsetWidth || 300));
      let left = rect.left;
      if (left + menuWidth > window.innerWidth - 12) {
        left = Math.max(12, window.innerWidth - menuWidth - 12);
      }
      menu.style.position = 'fixed';
      menu.style.top = Math.round(rect.bottom + 8) + 'px';
      menu.style.left = Math.round(left) + 'px';
      menu.style.right = 'auto';
      menu.style.minWidth = menuWidth + 'px';
    }

    function shToggleProjectsMenu(event) {
      if (event) {
        event.preventDefault();
        event.stopPropagation();
      }
      const drop = document.getElementById('sh-projects-drop');
      const menu = document.getElementById('sh-projects-menu');
      const trigger = document.getElementById('sh-projects-trigger');
      if (!drop || !menu || !trigger) return;
      const open = !menu.classList.contains('is-open');
      drop.classList.toggle('is-open', open);
      menu.classList.toggle('is-open', open);
      menu.hidden = !open;
      trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) shPositionProjectsMenu();
    }

    function shToggleDrawerProjects() {
      const btn = document.getElementById('sh-drawer-projects-btn');
      const sub = document.getElementById('sh-drawer-projects');
      if (!btn || !sub) return;
      const open = !sub.classList.contains('is-open');
      sub.classList.toggle('is-open', open);
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    (function initHeaderScroll() {
      const header = document.querySelector('.sh');
      if (!header) return;
      const onScroll = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 12);
      };
      onScroll();
      window.addEventListener('scroll', onScroll, { passive: true });
    })();

    document.addEventListener('DOMContentLoaded', function () {
      const drop = document.getElementById('sh-projects-drop');
      const trigger = document.getElementById('sh-projects-trigger');
      const menu = document.getElementById('sh-projects-menu');
      if (!drop || !trigger || !menu) return;

      function closeMenu() {
        drop.classList.remove('is-open');
        menu.classList.remove('is-open');
        menu.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
      }

      document.addEventListener('click', function (e) {
        if (!drop.contains(e.target) && !menu.contains(e.target)) {
          closeMenu();
        }
      });

      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
      });

      window.addEventListener('resize', function () {
        if (menu.classList.contains('is-open')) shPositionProjectsMenu();
      }, { passive: true });

      window.addEventListener('scroll', function () {
        if (menu.classList.contains('is-open')) shPositionProjectsMenu();
      }, { passive: true });
    });
  </script>
</head>
<body>
  <a class="skip-link" href="#main-content">Skip to content</a>

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
          <span class="tb__label">silatanuikipngetich@gmail.com</span>
        </a>
        <a class="tb__item" href="https://wa.me/254742178644" target="_blank" rel="noopener noreferrer" title="Chat on WhatsApp">
          <i class="fa-brands fa-whatsapp"></i>
          <span class="tb__label">+254 742 178 644</span>
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
        <div class="sh__drop" id="sh-projects-drop">
          <div class="sh__drop-trigger<?php echo $activePage === 'projects' ? ' is-active' : ''; ?>">
            <a
              class="sh__drop-link"
              href="<?php echo appUrl('projects.php'); ?>"
              <?php echo $activePage === 'projects' ? 'aria-current="page"' : ''; ?>
            >
              <i class="fa-solid fa-code" aria-hidden="true"></i>
              Projects
            </a>
            <button
              type="button"
              class="sh__drop-toggle"
              id="sh-projects-trigger"
              aria-label="Show projects menu"
              aria-expanded="false"
              aria-haspopup="true"
              aria-controls="sh-projects-menu"
              onclick="shToggleProjectsMenu(event)"
            >
              <i class="fa-solid fa-chevron-down sh__drop-chevron" aria-hidden="true"></i>
            </button>
          </div>
        </div>
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
      <div class="sh__drawer-projects-row">
        <a
          href="<?php echo appUrl('projects.php'); ?>"
          class="sh__drawer-link <?php echo $activePage === 'projects' ? 'active' : ''; ?>"
        ><i class="fa-solid fa-code"></i> Projects</a>
        <button
          type="button"
          class="sh__drawer-projects-btn <?php echo $activePage === 'projects' ? 'active' : ''; ?>"
          id="sh-drawer-projects-btn"
          aria-label="Show project links"
          aria-expanded="false"
          aria-controls="sh-drawer-projects"
          onclick="shToggleDrawerProjects()"
        >
          <i class="fa-solid fa-chevron-down sh__drop-chevron" aria-hidden="true"></i>
        </button>
      </div>
      <div class="sh__drawer-sub" id="sh-drawer-projects">
        <?php if (!empty($navProjects)): ?>
          <?php foreach ($navProjects as $navProject):
            $navTitle = trim((string) ($navProject['title'] ?? ''));
            if ($navTitle === '') {
              continue;
            }
            $navUrl = trim((string) ($navProject['url'] ?? ''));
            $navHref = $navUrl !== ''
              ? $navUrl
              : appUrl('projects.php') . '#project-' . (int) $navProject['id'];
            $navExternal = $navUrl !== '';
          ?>
            <a
              href="<?php echo htmlspecialchars($navHref, ENT_QUOTES, 'UTF-8'); ?>"
              <?php if ($navExternal): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
            ><?php echo htmlspecialchars($navTitle, ENT_QUOTES, 'UTF-8'); ?><?php echo $navExternal ? ' ↗' : ''; ?></a>
          <?php endforeach; ?>
        <?php endif; ?>
        <a href="<?php echo appUrl('projects.php'); ?>">Browse all projects</a>
      </div>
      <a href="<?php echo appUrl('blog.php'); ?>"       class="sh__drawer-link <?php echo $activePage === 'blog'       ? 'active' : ''; ?>"><i class="fa-solid fa-pen-nib"></i> Blog</a>
      <a href="<?php echo appUrl('contact.php'); ?>"    class="sh__drawer-link <?php echo $activePage === 'contact'    ? 'active' : ''; ?>"><i class="fa-solid fa-paper-plane"></i> Contact</a>
      <div class="sh__drawer-divider"></div>
      <a href="<?php echo appUrl('lab.php'); ?>" class="sh__drawer-link <?php echo $activePage === 'lab' ? 'active' : ''; ?>"><i class="fa-solid fa-flask"></i> Lab</a>
      <a href="<?php echo appUrl('saas.php'); ?>" class="sh__drawer-link <?php echo $activePage === 'saas' ? 'active' : ''; ?>"><i class="fa-solid fa-rocket"></i> SaaS</a>
      <a href="<?php echo appUrl('now.php'); ?>" class="sh__drawer-link <?php echo $activePage === 'now' ? 'active' : ''; ?>"><i class="fa-solid fa-bolt"></i> Now</a>
      <a href="<?php echo appUrl('tools.php'); ?>" class="sh__drawer-link <?php echo $activePage === 'tools' ? 'active' : ''; ?>"><i class="fa-solid fa-terminal"></i> Tools</a>
      <a href="<?php echo appUrl('sitemap.php'); ?>" class="sh__drawer-link"><i class="fa-solid fa-map"></i> Sitemap</a>
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

  <div class="sh__drop-menu" id="sh-projects-menu" role="menu" aria-label="Projects" hidden>
    <?php if (!empty($navProjects)): ?>
      <?php foreach ($navProjects as $navProject):
        $navTitle = trim((string) ($navProject['title'] ?? ''));
        if ($navTitle === '') {
          continue;
        }
        $navUrl = trim((string) ($navProject['url'] ?? ''));
        $navHref = $navUrl !== ''
          ? $navUrl
          : appUrl('projects.php') . '#project-' . (int) $navProject['id'];
        $navExternal = $navUrl !== '';
        $navHost = '';
        if ($navExternal) {
          $navHost = preg_replace('#^https?://#i', '', rtrim($navUrl, '/'));
          $navHost = preg_replace('#/.*$#', '', $navHost);
        }
      ?>
        <a
          class="sh__drop-item"
          role="menuitem"
          href="<?php echo htmlspecialchars($navHref, ENT_QUOTES, 'UTF-8'); ?>"
          <?php if ($navExternal): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
        >
          <span class="sh__drop-item__copy">
            <span class="sh__drop-item__title"><?php echo htmlspecialchars($navTitle, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php if ($navHost !== ''): ?>
              <span class="sh__drop-item__meta"><?php echo htmlspecialchars($navHost, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
          </span>
          <?php if ($navExternal): ?>
            <i class="fa-solid fa-arrow-up-right-from-square sh__drop-item__icon" aria-hidden="true"></i>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <span class="sh__drop-item" role="menuitem" aria-disabled="true">
        <span class="sh__drop-item__copy">
          <span class="sh__drop-item__title">No projects yet</span>
        </span>
      </span>
    <?php endif; ?>
    <a class="sh__drop-foot" role="menuitem" href="<?php echo appUrl('projects.php'); ?>">Browse all projects →</a>
  </div>
