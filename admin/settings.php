<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/sidebar.php';
require_once __DIR__ . '/topbar.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_settings') {
    try {
        $settings = [
            'site_title' => trim($_POST['site_title'] ?? 'Your Name'),
            'tagline' => trim($_POST['tagline'] ?? 'Developer • Designer • Creator'),
            'hero_text' => trim($_POST['hero_text'] ?? ''),
            'show_blog' => (string) ($_POST['show_blog'] ?? '1'),
            'theme' => trim($_POST['theme'] ?? 'light'),
        ];

        $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($settings as $key => $value) {
            $stmt->execute([$key, $value]);
        }
        $notice = 'Settings updated successfully.';
    } catch (Throwable $e) {
        $error = 'Unable to save settings. ' . $e->getMessage();
    }
}

$settings = [
    'site_title' => 'Your Name',
    'tagline' => 'Developer • Designer • Creator',
    'hero_text' => '',
    'show_blog' => '1',
    'theme' => 'light',
];

try {
    foreach ($pdo->query('SELECT setting_key, setting_value FROM settings') as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Throwable $e) {
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Website Settings</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('settings.php'); ?>
    <main class="main-panel">
      <?php echo renderTopbar('Website Settings'); ?>
      <?php if (!empty($notice)): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
      <?php if (!empty($error)): ?><div class="error-box"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <section class="panel">
        <h2>Portfolio settings</h2>
        <form method="post">
          <div class="grid-form">
            <div class="field"><label>Site title</label><input name="site_title" value="<?php echo htmlspecialchars($settings['site_title']); ?>"></div>
            <div class="field"><label>Tagline</label><input name="tagline" value="<?php echo htmlspecialchars($settings['tagline']); ?>"></div>
            <div class="field"><label>Show blog</label><select name="show_blog"><option value="1" <?php echo (($settings['show_blog'] ?? '1') === '1') ? 'selected' : ''; ?>>Yes</option><option value="0" <?php echo (($settings['show_blog'] ?? '1') === '0') ? 'selected' : ''; ?>>No</option></select></div>
            <div class="field"><label>Theme</label><select name="theme"><option value="light" <?php echo (($settings['theme'] ?? 'light') === 'light') ? 'selected' : ''; ?>>Light</option><option value="dark" <?php echo (($settings['theme'] ?? 'light') === 'dark') ? 'selected' : ''; ?>>Dark</option></select></div>
            <div class="field full"><label>Hero text</label><textarea name="hero_text"><?php echo htmlspecialchars($settings['hero_text']); ?></textarea></div>
          </div>
          <div class="form-actions"><button type="submit" class="btn primary">Save settings</button></div>
          <input type="hidden" name="action" value="save_settings">
        </form>
      </section>
    </main>
  </div>
</body>
</html>
