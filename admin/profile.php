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

        // Keep legacy contact table synchronized
        $stmtContact = $pdo->prepare('INSERT INTO contact (id, email, location, phone) VALUES (1, ?, ?, ?) ON DUPLICATE KEY UPDATE email = VALUES(email), location = VALUES(location), phone = VALUES(phone)');
        $stmtContact->execute([$email, $location, $phone]);

        $notice = 'Personal & contact details saved successfully.';
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

$fullName = (string) ($profile['full_name'] ?? 'Your Name');
$headline = (string) ($profile['headline'] ?? '');
$bio = (string) ($profile['bio'] ?? '');
$email = (string) ($profile['email'] ?? '');
$phone = (string) ($profile['phone'] ?? '');
$location = (string) ($profile['location'] ?? '');
$website = (string) ($profile['website'] ?? '');
$linkedin = (string) ($profile['linkedin'] ?? '');
$github = (string) ($profile['github'] ?? '');
$summary = (string) ($profile['summary'] ?? '');
$initial = strtoupper(substr(trim($fullName) !== '' ? trim($fullName) : 'T', 0, 1));
$photoPath = __DIR__ . '/../public/profile.jpg';
$hasPhoto = is_file($photoPath);
$photoUrl = $hasPhoto ? '../public/profile.jpg?v=' . (string) filemtime($photoPath) : '';
$filled = 0;
foreach ([$fullName, $headline, $bio, $email, $phone, $location, $website, $linkedin, $github, $summary] as $value) {
    if (trim((string) $value) !== '') {
        $filled++;
    }
}
$completeness = (int) round(($filled / 10) * 100);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Personal Details · Admin</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=17">
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('profile.php'); ?>

    <main class="main-panel">
      <?php echo renderTopbar('Personal Details'); ?>

      <?php if (!empty($notice)): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
      <?php if (!empty($error)): ?><div class="error-box"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <section class="profile-hero panel">
        <div class="profile-hero__main">
          <div class="profile-hero__avatar" aria-hidden="true">
            <?php if ($hasPhoto): ?>
              <img src="<?php echo htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="">
            <?php else: ?>
              <span><?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
          </div>
          <div class="profile-hero__copy">
            <p class="profile-hero__kicker">Public identity</p>
            <h2><?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></h2>
            <?php if ($headline !== ''): ?>
              <p class="profile-hero__headline"><?php echo htmlspecialchars($headline, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <div class="profile-hero__chips">
              <?php if ($location !== ''): ?>
                <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i><?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endif; ?>
              <?php if ($email !== ''): ?>
                <span><i class="fa-solid fa-envelope" aria-hidden="true"></i><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endif; ?>
              <?php if ($phone !== ''): ?>
                <span><i class="fa-solid fa-phone" aria-hidden="true"></i><?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="profile-hero__side">
          <div class="profile-complete">
            <div class="profile-complete__row">
              <span>Profile fill</span>
              <strong><?php echo $completeness; ?>%</strong>
            </div>
            <div class="profile-complete__bar" aria-hidden="true">
              <span style="width: <?php echo $completeness; ?>%;"></span>
            </div>
            <p class="profile-complete__hint">These fields power the homepage, about page, and footer.</p>
          </div>
          <div class="profile-hero__actions">
            <a class="btn" href="../public/about.php" target="_blank" rel="noopener">
              <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View About
            </a>
            <a class="btn" href="../public/index.php" target="_blank" rel="noopener">
              <i class="fa-solid fa-house" aria-hidden="true"></i> Homepage
            </a>
          </div>
        </div>
      </section>

      <form method="post" class="profile-form">
        <section class="panel profile-section">
          <div class="profile-section__head">
            <div>
              <h2>Identity</h2>
              <p>Name and short intro visitors see first.</p>
            </div>
          </div>
          <div class="grid-form">
            <div class="field">
              <label for="full_name">Full name</label>
              <input id="full_name" name="full_name" value="<?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?>" required placeholder="Tanui Kipng'etich Sila">
            </div>
            <div class="field">
              <label for="headline">Headline</label>
              <input id="headline" name="headline" value="<?php echo htmlspecialchars($headline, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Engineer · Builder · Writer">
              <span class="field-hint">One line under your name on the site.</span>
            </div>
            <div class="field full">
              <label for="bio">Short biography</label>
              <textarea id="bio" name="bio" rows="5" placeholder="A concise bio for cards and intro sections."><?php echo htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
          </div>
        </section>

        <section class="panel profile-section">
          <div class="profile-section__head">
            <div>
              <h2>Contact</h2>
              <p>How people reach you. Also syncs to the contact table.</p>
            </div>
          </div>
          <div class="grid-form">
            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" placeholder="you@example.com" autocomplete="email">
            </div>
            <div class="field">
              <label for="phone">Phone</label>
              <input id="phone" name="phone" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" placeholder="+254 …" autocomplete="tel">
            </div>
            <div class="field full">
              <label for="location">Location</label>
              <input id="location" name="location" value="<?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nairobi, Kenya · Budapest, Hungary">
            </div>
          </div>
        </section>

        <section class="panel profile-section">
          <div class="profile-section__head">
            <div>
              <h2>Online presence</h2>
              <p>Links shown in the portfolio footer and about page.</p>
            </div>
          </div>
          <div class="grid-form">
            <div class="field">
              <label for="website">Website</label>
              <div class="input-with-icon">
                <i class="fa-solid fa-globe" aria-hidden="true"></i>
                <input id="website" name="website" type="url" value="<?php echo htmlspecialchars($website, ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://…">
              </div>
            </div>
            <div class="field">
              <label for="linkedin">LinkedIn</label>
              <div class="input-with-icon">
                <i class="fa-brands fa-linkedin-in" aria-hidden="true"></i>
                <input id="linkedin" name="linkedin" value="<?php echo htmlspecialchars($linkedin, ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://linkedin.com/in/…">
              </div>
            </div>
            <div class="field">
              <label for="github">GitHub</label>
              <div class="input-with-icon">
                <i class="fa-brands fa-github" aria-hidden="true"></i>
                <input id="github" name="github" value="<?php echo htmlspecialchars($github, ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://github.com/…">
              </div>
            </div>
          </div>
        </section>

        <section class="panel profile-section">
          <div class="profile-section__head">
            <div>
              <h2>Professional summary</h2>
              <p>Longer narrative for the about page and CV-style sections.</p>
            </div>
          </div>
          <div class="grid-form">
            <div class="field full">
              <label for="summary">Summary</label>
              <textarea id="summary" name="summary" rows="8" placeholder="Experience, focus areas, and what you want visitors to remember."><?php echo htmlspecialchars($summary, ENT_QUOTES, 'UTF-8'); ?></textarea>
              <span class="field-hint">Aim for a few clear paragraphs, not a full resume dump.</span>
            </div>
          </div>
        </section>

        <div class="profile-savebar">
          <div class="profile-savebar__copy">
            <strong>Ready to publish?</strong>
            <span>Saving updates the live portfolio identity immediately.</span>
          </div>
          <div class="form-actions profile-savebar__actions">
            <button type="submit" class="btn primary">
              <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save profile
            </button>
          </div>
        </div>
      </form>
    </main>
  </div>
</body>
</html>
