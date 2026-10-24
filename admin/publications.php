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
  <link rel="stylesheet" href="../assets/css/admin.css?v=15">
  <?php require_once __DIR__ . '/datatables.php'; adminDataTablesHead(); ?>
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('publications.php'); ?>
    <main class="main-panel">
      <?php echo renderTopbar('Publications'); ?>

      <?php if (!empty($notice)): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
      <?php if (!empty($error)): ?><div class="error-box"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <?php $publication_form_panel_open = !empty($error); ?>


      <section class="panel">
        <div class="admin-list-head">
          <h2>Saved entries</h2>
          <button type="button" class="btn primary admin-form-toggle" data-form-target="publication-form-panel" data-label-show="Add Publication" data-label-hide="Hide Form" aria-controls="publication-form-panel">
            <i class="fa-solid fa-plus"></i> Add Publication
          </button>
        </div>
        <?php if (empty($publications)): ?>
          <p class="muted-copy">No publications added yet.</p>
        <?php else: ?>
          <div class="table-wrap">
            <table id="publications-table" class="data-table display" style="width:100%" data-page-length="10">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Venue</th>
                  <th>Year</th>
                  <th>Summary</th>
                  <th>Sort</th>
                  <th class="no-sort">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($publications as $item): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($item['title']); ?></strong></td>
                    <td><?php echo htmlspecialchars($item['venue'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($item['publication_year'] ?: '—'); ?></td>
                    <td><?php
                      $sum = (string)($item['summary'] ?? '');
                      echo htmlspecialchars(strlen($sum) > 120 ? substr($sum, 0, 117) . '…' : $sum);
                    ?></td>
                    <td data-order="<?php echo (int) $item['sort_order']; ?>">#<?php echo (int) $item['sort_order']; ?></td>
                    <td class="actions-cell">
                      <?php if (!empty($item['url'])): ?>
                        <a class="btn small" href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank" rel="noopener">Open</a>
                      <?php endif; ?>
                      <a class="btn danger small" href="?delete=publications&id=<?php echo (int) $item['id']; ?>" onclick="return confirm('Delete this entry?');">Delete</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>

      <section class="panel admin-form-panel" id="publication-form-panel"<?php echo $publication_form_panel_open ? "" : " hidden"; ?>>
        <div class="admin-list-head">
          <h2>New Publication</h2>
          <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
              <button type="button" class="btn small admin-form-close" data-form-target="publication-form-panel">Hide</button>
          </div>
        </div>
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

    </main>
  </div>
  <?php adminDataTablesFooter(); ?>
</body>
</html>
