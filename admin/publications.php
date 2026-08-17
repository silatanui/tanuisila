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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_publication') {
    try {
        $title = trim($_POST['title'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $publication_year = trim($_POST['publication_year'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $sort_order = (int) ($_POST['sort_order'] ?? 0);

        if ($title === '') {
            throw new InvalidArgumentException('Title is required.');
        }

        $stmt = $pdo->prepare('INSERT INTO publications (title, venue, publication_year, url, summary, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $venue, $publication_year, $url, $summary, $sort_order]);
        $notice = 'Publication added successfully.';
    } catch (Throwable $e) {
        $error = 'Unable to save publication. ' . $e->getMessage();
    }
}

if (isset($_GET['delete']) && $_GET['delete'] === 'publications') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM publications WHERE id = ?')->execute([$id]);
        $notice = 'Publication removed.';
    }
}

$publications = $pdo->query('SELECT * FROM publications ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Publications</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('publications.php'); ?>
    <main class="main-panel">
      <?php echo renderTopbar('Publications'); ?>

      <?php if (!empty($notice)): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
      <?php if (!empty($error)): ?><div class="error-box"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <section class="panel">
        <h2>Add publication</h2>
        <form method="post">
          <div class="grid-form">
            <div class="field">
              <label>Title</label>
              <input name="title" required>
            </div>
            <div class="field">
              <label>Venue</label>
              <input name="venue">
            </div>
            <div class="field">
              <label>Year</label>
              <input name="publication_year">
            </div>
            <div class="field">
              <label>URL</label>
              <input name="url" type="url">
            </div>
            <div class="field">
              <label>Sort order</label>
              <input type="number" name="sort_order" value="0">
            </div>
            <div class="field full">
              <label>Summary</label>
              <textarea name="summary"></textarea>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn primary">Add publication</button>
          </div>
          <input type="hidden" name="action" value="save_publication">
        </form>
      </section>

      <section class="panel">
        <h2>Saved entries</h2>
        <div class="list">
          <?php foreach ($publications as $item): ?>
            <div class="list-item">
              <div>
                <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                <small><?php echo htmlspecialchars($item['venue']); ?> • <?php echo htmlspecialchars($item['publication_year']); ?></small>
                <?php if (!empty($item['summary'])): ?><small><?php echo nl2br(htmlspecialchars($item['summary'])); ?></small><?php endif; ?>
              </div>
              <div class="list-meta">
                <span class="tag">#<?php echo (int) $item['sort_order']; ?></span>
                <?php if (!empty($item['url'])): ?><a class="btn" href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank">Open</a><?php endif; ?>
                <a class="btn danger" href="?delete=publications&id=<?php echo (int) $item['id']; ?>" onclick="return confirm('Delete this entry?');">Delete</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
