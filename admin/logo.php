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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_logo') {
    try {
        if (empty($_FILES['logo_file']) || $_FILES['logo_file']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No file uploaded.');
        }

        $tmp = $_FILES['logo_file']['tmp_name'];
        $name = basename($_FILES['logo_file']['name']);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

        if (!in_array($ext, $allowed, true)) {
            throw new RuntimeException('Invalid file type.');
        }

        $target = __DIR__ . '/../Tanui-Sila-Logo.' . $ext;
        if (!move_uploaded_file($tmp, $target)) {
            throw new RuntimeException('Failed to upload the logo.');
        }

        $notice = 'Logo uploaded successfully.';
    } catch (Throwable $e) {
        $error = 'Unable to upload logo. ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Brand Logo — Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('logo.php'); ?>
    <main class="main-panel">
      <?php echo renderTopbar('Logo'); ?>

      <?php if (!empty($notice)): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
      <?php if (!empty($error)): ?><div class="error-box"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <section class="panel">
        <h2>Upload Logo</h2>
        <form method="post" enctype="multipart/form-data">
          <div class="grid-form">
            <div class="field full">
              <label>Select Image</label>
              <input type="file" name="logo_file" accept="image/*" required>
            </div>
          </div>
          <div class="form-actions"><button type="submit" class="btn primary">Upload Logo</button></div>
          <input type="hidden" name="action" value="upload_logo">
        </form>
      </section>
    </main>
  </div>
</body>
</html>
