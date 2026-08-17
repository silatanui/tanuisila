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
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'save_profile':
                $full_name = trim($_POST['full_name'] ?? 'Your Name');
                $headline = trim($_POST['headline'] ?? '');
                $bio = trim($_POST['bio'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $location = trim($_POST['location'] ?? '');
                $website = trim($_POST['website'] ?? '');
                $linkedin = trim($_POST['linkedin'] ?? '');
                $github = trim($_POST['github'] ?? '');
                $summary = trim($_POST['summary'] ?? '');

                $stmt = $pdo->prepare(
                    'INSERT INTO profile (id, full_name, headline, bio, email, phone, location, website, linkedin, github, summary) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), headline = VALUES(headline), bio = VALUES(bio), email = VALUES(email), phone = VALUES(phone), location = VALUES(location), website = VALUES(website), linkedin = VALUES(linkedin), github = VALUES(github), summary = VALUES(summary)'
                );
                $stmt->execute([$full_name, $headline, $bio, $email, $phone, $location, $website, $linkedin, $github, $summary]);
                $notice = 'Personal details saved successfully.';
                break;

            case 'save_settings':
                $settings = [
                    'site_title' => trim($_POST['site_title'] ?? 'Your Name'),
                    'tagline' => trim($_POST['tagline'] ?? 'Developer • Designer • Creator'),
                    'hero_text' => trim($_POST['hero_text'] ?? ''),
                    'show_blog' => (string)($_POST['show_blog'] ?? '1'),
                    'theme' => trim($_POST['theme'] ?? 'light'),
                ];

                $stmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
                foreach ($settings as $key => $value) {
                    $stmt->execute([$key, $value]);
                }
                $notice = 'Portfolio settings saved successfully.';
                break;

            case 'save_education':
                $institution = trim($_POST['institution'] ?? '');
                $degree = trim($_POST['degree'] ?? '');
                $field = trim($_POST['field'] ?? '');
                $start_year = trim($_POST['start_year'] ?? '');
                $end_year = trim($_POST['end_year'] ?? '');
                $description = trim($_POST['education_description'] ?? '');
                $sort_order = (int)($_POST['sort_order'] ?? 0);

                $stmt = $pdo->prepare('INSERT INTO education (institution, degree, field_name, start_year, end_year, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$institution, $degree, $field, $start_year, $end_year, $description, $sort_order]);
                $notice = 'Education entry added successfully.';
                break;

            case 'save_experience':
                $company = trim($_POST['company'] ?? '');
                $role = trim($_POST['role'] ?? '');
                $location = trim($_POST['experience_location'] ?? '');
                $start_date = trim($_POST['start_date'] ?? '');
                $end_date = trim($_POST['end_date'] ?? '');
                $description = trim($_POST['experience_description'] ?? '');
                $sort_order = (int)($_POST['experience_sort_order'] ?? 0);

                $stmt = $pdo->prepare('INSERT INTO experience (company, role_name, location, start_date, end_date, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$company, $role, $location, $start_date, $end_date, $description, $sort_order]);
                $notice = 'Work experience entry added successfully.';
                break;

            case 'save_publication':
                $title = trim($_POST['publication_title'] ?? '');
                $venue = trim($_POST['publication_venue'] ?? '');
                $year = trim($_POST['publication_year'] ?? '');
                $url = trim($_POST['publication_url'] ?? '');
                $summary = trim($_POST['publication_summary'] ?? '');
                $sort_order = (int)($_POST['publication_sort_order'] ?? 0);

                $stmt = $pdo->prepare('INSERT INTO publications (title, venue, publication_year, url, summary, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$title, $venue, $year, $url, $summary, $sort_order]);
                $notice = 'Publication added successfully.';
                break;

            case 'save_blog':
                $title = trim($_POST['blog_title'] ?? '');
                $slug = trim($_POST['blog_slug'] ?? '');
                $excerpt = trim($_POST['blog_excerpt'] ?? '');
                $content = trim($_POST['blog_content'] ?? '');
                $status = $_POST['blog_status'] ?? 'draft';
                $published_at = trim($_POST['published_at'] ?? date('Y-m-d H:i:s'));
                $sort_order = (int)($_POST['blog_sort_order'] ?? 0);

                if ($slug === '') {
                    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
                    $slug = trim($slug, '-');
                }

                $stmt = $pdo->prepare('INSERT INTO blog_posts (title, slug, excerpt, content, status, published_at, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$title, $slug, $excerpt, $content, $status, $published_at, $sort_order]);
                $notice = 'Blog post saved successfully.';
                break;

            case 'save_project':
                $title = trim($_POST['project_title'] ?? '');
                $description = trim($_POST['project_description'] ?? '');
                $url = trim($_POST['project_url'] ?? '');
                $sort_order = (int)($_POST['project_sort_order'] ?? 0);

                $stmt = $pdo->prepare('INSERT INTO projects (title, description, url, sort_order) VALUES (?, ?, ?, ?)');
                $stmt->execute([$title, $description, $url, $sort_order]);
                $notice = 'Project added successfully.';
                break;

            case 'upload_logo':
                if (!empty($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
                    $tmp = $_FILES['logo_file']['tmp_name'];
                    $name = basename($_FILES['logo_file']['name']);
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

                    if (!in_array($ext, $allowed, true)) {
                        throw new RuntimeException('Invalid file type. Please upload PNG, JPG, GIF, or WEBP.');
                    }

                    $target = __DIR__ . '/../Tanui-Sila-Logo.' . $ext;
                    if (!move_uploaded_file($tmp, $target)) {
                        throw new RuntimeException('Failed to move uploaded file.');
                    }

                    $notice = 'Logo uploaded successfully.';
                } else {
                    throw new RuntimeException('No file uploaded.');
                }
                break;

            default:
                throw new RuntimeException('Unsupported action.');
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if (isset($_GET['delete'])) {
    $entity = $_GET['delete'];
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        try {
            $allowed = ['education', 'experience', 'publications', 'blog_posts', 'projects'];
            if (in_array($entity, $allowed, true)) {
                $stmt = $pdo->prepare('DELETE FROM ' . $entity . ' WHERE id = ?');
                $stmt->execute([$id]);
                $notice = 'Entry deleted successfully.';
            }
        } catch (Throwable $e) {
            $error = 'Unable to delete this entry.';
        }
    }
}

$profile = [
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

try {
    $row = $pdo->query('SELECT * FROM profile WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $profile = $row;
    }
} catch (Throwable $e) {
    $profile = $profile;
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
    $settings = $settings;
}

$projects = $pdo->query('SELECT * FROM projects ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
$education = $pdo->query('SELECT * FROM education ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
$experience = $pdo->query('SELECT * FROM experience ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
$publications = $pdo->query('SELECT * FROM publications ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
$blog_posts = $pdo->query('SELECT * FROM blog_posts ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
$messages = [];
$unreadMessagesCount = 0;
try {
    $messages = $pdo->query('SELECT * FROM messages ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    $unreadMessagesCount = (int)$pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
} catch (Throwable $e) {}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portfolio Dashboard</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
  <style>
    .quick-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 16px;
    }

    .quick-card {
      display: block;
      background: var(--soft);
      border: 1px solid var(--line);
      padding: 18px;
      color: var(--text);
      box-shadow: 0 4px 12px var(--shadow);
    }

    .quick-card .icon-wrap {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 42px;
      height: 42px;
      margin-bottom: 12px;
      background: var(--primary-soft);
      border: 1px solid #dbe8ff;
      color: var(--primary);
    }

    .quick-card h3 {
      margin: 0 0 8px;
      font-size: 1.08rem;
    }

    .quick-card p {
      margin: 0;
      color: var(--muted);
      line-height: 1.6;
      font-size: 0.93rem;
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('index.php'); ?>

    <main class="main-panel">
      <?php echo renderTopbar('Dashboard'); ?>

      <?php if (!empty($notice)): ?>
        <div class="notice"><?php echo htmlspecialchars($notice); ?></div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if ($unreadMessagesCount > 0): ?>
        <div style="background: #fee2e2; border: 1px solid #fca5a5; padding: 14px; margin-bottom: 20px; color: #991b1b; font-weight: 600; display: flex; align-items: center; justify-content: space-between;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-bell fa-bounce" style="font-size: 1.1rem; color: #ef4444;"></i>
            <span>You have received <?php echo $unreadMessagesCount; ?> unread client message(s) in your Inbox!</span>
          </div>
          <a href="messages.php" class="btn" style="padding: 6px 12px; background: #991b1b; color: #fff; font-size: 0.8rem; border: none; text-decoration: none;">View Messages</a>
        </div>
      <?php endif; ?>

      <section class="stats-grid">
        <div class="stat-card" style="border-left: 4px solid #ef4444;">
          <span class="label">Unread Messages</span>
          <div class="value" style="color: #ef4444;"><?php echo $unreadMessagesCount; ?></div>
        </div>
        <div class="stat-card">
          <span class="label">Education</span>
          <div class="value"><?php echo count($education); ?></div>
        </div>
        <div class="stat-card">
          <span class="label">Experience</span>
          <div class="value"><?php echo count($experience); ?></div>
        </div>
        <div class="stat-card">
          <span class="label">Publications</span>
          <div class="value"><?php echo count($publications); ?></div>
        </div>
        <div class="stat-card">
          <span class="label">Blog Posts</span>
          <div class="value"><?php echo count($blog_posts); ?></div>
        </div>
        <div class="stat-card">
          <span class="label">Projects</span>
          <div class="value"><?php echo count($projects); ?></div>
        </div>
      </section>

      <section class="panel">
        <h2>Manage portfolio sections</h2>
        <div class="quick-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
          <a href="profile.php" class="quick-card" style="display:block; background: var(--panel-alt); border:1px solid var(--line); padding:18px; color: var(--text); box-shadow: 0 4px 12px var(--shadow);">
            <div class="icon-wrap" style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; margin-bottom:12px; background: var(--soft); border:1px solid #dbe8ff; color: var(--primary);"><i class="fa-solid fa-user"></i></div>
            <h3 style="margin:0 0 8px; font-size:1.08rem;">Personal Details</h3>
            <p style="margin:0; color: var(--muted); line-height:1.6; font-size:0.93rem;">Update your bio, contact details, and profile summary.</p>
          </a>

          <a href="education.php" class="quick-card" style="display:block; background: var(--panel-alt); border:1px solid var(--line); padding:18px; color: var(--text); box-shadow: 0 4px 12px var(--shadow);">
            <div class="icon-wrap" style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; margin-bottom:12px; background: var(--soft); border:1px solid #dbe8ff; color: var(--primary);"><i class="fa-solid fa-graduation-cap"></i></div>
            <h3 style="margin:0 0 8px; font-size:1.08rem;">Education</h3>
            <p style="margin:0; color: var(--muted); line-height:1.6; font-size:0.93rem;">Manage schools, degrees, and academic background.</p>
          </a>

          <a href="experience.php" class="quick-card" style="display:block; background: var(--panel-alt); border:1px solid var(--line); padding:18px; color: var(--text); box-shadow: 0 4px 12px var(--shadow);">
            <div class="icon-wrap" style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; margin-bottom:12px; background: var(--soft); border:1px solid #dbe8ff; color: var(--primary);"><i class="fa-solid fa-briefcase"></i></div>
            <h3 style="margin:0 0 8px; font-size:1.08rem;">Experience</h3>
            <p style="margin:0; color: var(--muted); line-height:1.6; font-size:0.93rem;">Track employers, roles, timelines, and responsibilities.</p>
          </a>

          <a href="publications.php" class="quick-card" style="display:block; background: var(--panel-alt); border:1px solid var(--line); padding:18px; color: var(--text); box-shadow: 0 4px 12px var(--shadow);">
            <div class="icon-wrap" style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; margin-bottom:12px; background: var(--soft); border:1px solid #dbe8ff; color: var(--primary);"><i class="fa-solid fa-book-open"></i></div>
            <h3 style="margin:0 0 8px; font-size:1.08rem;">Publications</h3>
            <p style="margin:0; color: var(--muted); line-height:1.6; font-size:0.93rem;">Add papers, talks, venues, and publication links.</p>
          </a>

          <a href="blog.php" class="quick-card" style="display:block; background: var(--panel-alt); border:1px solid var(--line); padding:18px; color: var(--text); box-shadow: 0 4px 12px var(--shadow);">
            <div class="icon-wrap" style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; margin-bottom:12px; background: var(--soft); border:1px solid #dbe8ff; color: var(--primary);"><i class="fa-solid fa-pen-nib"></i></div>
            <h3 style="margin:0 0 8px; font-size:1.08rem;">Blog Posts</h3>
            <p style="margin:0; color: var(--muted); line-height:1.6; font-size:0.93rem;">Create and manage your latest writing and updates.</p>
          </a>

          <a href="projects.php" class="quick-card" style="display:block; background: var(--panel-alt); border:1px solid var(--line); padding:18px; color: var(--text); box-shadow: 0 4px 12px var(--shadow);">
            <div class="icon-wrap" style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; margin-bottom:12px; background: var(--soft); border:1px solid #dbe8ff; color: var(--primary);"><i class="fa-solid fa-rocket"></i></div>
            <h3 style="margin:0 0 8px; font-size:1.08rem;">Projects</h3>
            <p style="margin:0; color: var(--muted); line-height:1.6; font-size:0.93rem;">Showcase your featured work and portfolio highlights.</p>
          </a>

          <a href="settings.php" class="quick-card" style="display:block; background: var(--panel-alt); border:1px solid var(--line); padding:18px; color: var(--text); box-shadow: 0 4px 12px var(--shadow);">
            <div class="icon-wrap" style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; margin-bottom:12px; background: var(--soft); border:1px solid #dbe8ff; color: var(--primary);"><i class="fa-solid fa-gear"></i></div>
            <h3 style="margin:0 0 8px; font-size:1.08rem;">Settings</h3>
            <p style="margin:0; color: var(--muted); line-height:1.6; font-size:0.93rem;">Adjust portfolio title, tagline, theme, and site preferences.</p>
          </a>

          <a href="logo.php" class="quick-card" style="display:block; background: var(--panel-alt); border:1px solid var(--line); padding:18px; color: var(--text); box-shadow: 0 4px 12px var(--shadow);">
            <div class="icon-wrap" style="display:inline-flex; align-items:center; justify-content:center; width:42px; height:42px; margin-bottom:12px; background: var(--soft); border:1px solid #dbe8ff; color: var(--primary);"><i class="fa-solid fa-image"></i></div>
            <h3 style="margin:0 0 8px; font-size:1.08rem;">Logo</h3>
            <p style="margin:0; color: var(--muted); line-height:1.6; font-size:0.93rem;">Upload or replace the brand image used on the portfolio.</p>
          </a>
        </div>
      </section>

    </main>
  </div>
</body>
</html>
