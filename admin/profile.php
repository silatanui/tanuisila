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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $full_name = trim($_POST['full_name'] ?? 'Your Name');
        $headline = trim($_POST['headline'] ?? 'Developer • Designer • Creator');
        $bio = trim($_POST['bio'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $linkedin = trim($_POST['linkedin'] ?? '');
        $github = trim($_POST['github'] ?? '');
        $summary = trim($_POST['summary'] ?? '');

        $stmt = $pdo->prepare('INSERT INTO profile (id, full_name, headline, bio, email, phone, location, website, linkedin, github, summary) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), headline = VALUES(headline), bio = VALUES(bio), email = VALUES(email), phone = VALUES(phone), location = VALUES(location), website = VALUES(website), linkedin = VALUES(linkedin), github = VALUES(github), summary = VALUES(summary)');
        $stmt->execute([$full_name, $headline, $bio, $email, $phone, $location, $website, $linkedin, $github, $summary]);
        $notice = 'Personal details saved successfully.';
    } catch (Throwable $e) {
        $error = 'Unable to save profile. ' . $e->getMessage();
    }
}

try {
    $profile = $pdo->query('SELECT * FROM profile WHERE id = 1')->fetch(PDO::FETCH_ASSOC) ?: [
        'full_name' => 'Your Name',
        'headline' => 'Developer • Designer • Creator',
        'bio' => '',
        'email' => '',
        'phone' => '',
        'location' => '',
        'website' => '',
        'linkedin' => '',
        'github' => '',
        'summary' => '',
    ];
} catch (Throwable $e) {
    $profile = ['full_name' => 'Your Name', 'headline' => 'Developer • Designer • Creator', 'bio' => '', 'email' => '', 'phone' => '', 'location' => '', 'website' => '', 'linkedin' => '', 'github' => '', 'summary' => ''];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profile Management</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('profile.php'); ?>

    <main class="main-panel">
      <?php echo renderTopbar('Personal Details'); ?>

      <?php if (!empty($notice)): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
      <?php if (!empty($error)): ?><div class="error-box"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <section class="panel">
        <h2>Update profile</h2>
        <form method="post">
          <div class="grid-form">
            <div class="field">
              <label>Full name</label>
              <input name="full_name" value="<?php echo htmlspecialchars($profile['full_name'] ?? 'Your Name'); ?>" required>
            </div>
            <div class="field">
              <label>Headline</label>
              <input name="headline" value="<?php echo htmlspecialchars($profile['headline'] ?? 'Developer • Designer • Creator'); ?>">
            </div>
            <div class="field full">
              <label>Short biography</label>
              <textarea name="bio"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
            </div>
            <div class="field">
              <label>Email</label>
              <input name="email" type="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>Phone</label>
              <input name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>Location</label>
              <input name="location" value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>Website</label>
              <input name="website" type="url" value="<?php echo htmlspecialchars($profile['website'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>LinkedIn</label>
              <input name="linkedin" value="<?php echo htmlspecialchars($profile['linkedin'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>GitHub</label>
              <input name="github" value="<?php echo htmlspecialchars($profile['github'] ?? ''); ?>">
            </div>
            <div class="field full">
              <label>Professional summary</label>
              <textarea name="summary"><?php echo htmlspecialchars($profile['summary'] ?? ''); ?></textarea>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn primary">Save profile</button>
          </div>
        </form>
      </section>
    </main>
  </div>
</body>
</html>
