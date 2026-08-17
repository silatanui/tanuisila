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
$editingEntry = null;
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

// Load entry for editing if ID provided
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM experience WHERE id = ?');
    $stmt->execute([$editId]);
    $editingEntry = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$editingEntry) {
        $error = 'Work experience entry not found.';
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_experience') {
    try {
        $company = trim($_POST['company'] ?? '');
        $role_name = trim($_POST['role_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $start_date = trim($_POST['start_date'] ?? '');
        $end_date = trim($_POST['end_date'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sort_order = (int) ($_POST['sort_order'] ?? 0);
        $entryId = !empty($_POST['entry_id']) ? (int)$_POST['entry_id'] : 0;

        if ($company === '') {
            throw new InvalidArgumentException('Company is required.');
        }

        if ($entryId > 0) {
            // UPDATE existing entry
            $stmt = $pdo->prepare('UPDATE experience SET company=?, role_name=?, location=?, start_date=?, end_date=?, description=?, sort_order=? WHERE id=?');
            $stmt->execute([$company, $role_name, $location, $start_date, $end_date, $description, $sort_order, $entryId]);
            $notice = 'Work experience entry updated successfully.';
            $editId = 0;
            $editingEntry = null;
        } else {
            // INSERT new entry
            $stmt = $pdo->prepare('INSERT INTO experience (company, role_name, location, start_date, end_date, description, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$company, $role_name, $location, $start_date, $end_date, $description, $sort_order]);
            $notice = 'Work experience entry added successfully.';
        }
    } catch (Throwable $e) {
        $error = 'Unable to save work experience. ' . $e->getMessage();
    }
}

if (isset($_GET['delete']) && $_GET['delete'] === 'experience') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM experience WHERE id = ?')->execute([$id]);
        $notice = 'Experience entry removed.';
    }
}

$experience = $pdo->query('SELECT * FROM experience ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Work Experience</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('experience.php'); ?>

    <main class="main-panel">
      <?php echo renderTopbar('Work Experience'); ?>

      <?php if (!empty($notice)): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
      <?php if (!empty($error)): ?><div class="error-box"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <section class="panel">
        <h2><?php echo $editingEntry ? '✏️ Edit Experience' : '📝 Add Experience'; ?></h2>
        <?php if ($editingEntry): ?><div style="background: rgba(15, 118, 110, 0.08); border: 1px solid rgba(15, 118, 110, 0.2); padding: 12px; margin-bottom: 16px; font-size: 0.9rem; color: var(--text);">Editing: <strong><?php echo htmlspecialchars($editingEntry['company']); ?></strong> (ID: <?php echo (int)$editingEntry['id']; ?>)</div><?php endif; ?>
        <form method="post">
          <div class="grid-form">
            <div class="field">
              <label>Company</label>
              <input name="company" required value="<?php echo htmlspecialchars($editingEntry['company'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>Role</label>
              <input name="role_name" value="<?php echo htmlspecialchars($editingEntry['role_name'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>Location</label>
              <input name="location" value="<?php echo htmlspecialchars($editingEntry['location'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>Start date</label>
              <input name="start_date" value="<?php echo htmlspecialchars($editingEntry['start_date'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>End date</label>
              <input name="end_date" placeholder="Present" value="<?php echo htmlspecialchars($editingEntry['end_date'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>Sort order</label>
              <input type="number" name="sort_order" value="<?php echo htmlspecialchars($editingEntry['sort_order'] ?? '0'); ?>">
            </div>
            <div class="field full">
              <label>Description</label>
              <textarea name="description"><?php echo htmlspecialchars($editingEntry['description'] ?? ''); ?></textarea>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn primary"><?php echo $editingEntry ? 'Update Entry' : 'Add Experience'; ?></button>
            <?php if ($editingEntry): ?><a href="experience.php" class="btn">Cancel</a><?php endif; ?>
          </div>
          <input type="hidden" name="action" value="save_experience">
          <?php if ($editingEntry): ?><input type="hidden" name="entry_id" value="<?php echo (int)$editingEntry['id']; ?>"><?php endif; ?>
        </form>
      </section>

      <section class="panel">
        <h2>Saved entries</h2>
        <?php if (empty($experience)): ?>
          <p class="muted-copy">No work experience entries added yet.</p>
        <?php else: ?>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Company</th>
                  <th>Role</th>
                  <th>Location</th>
                  <th>Period</th>
                  <th>Description</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($experience as $item): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($item['company']); ?></td>
                    <td><?php echo htmlspecialchars($item['role_name'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($item['location'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($item['start_date'] ?: '—'); ?> - <?php echo htmlspecialchars($item['end_date'] ?: 'Present'); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($item['description'] ?: '—')); ?></td>
                    <td>
                      <a class="btn small" href="?edit=<?php echo (int) $item['id']; ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                      <a class="btn danger small" href="?delete=experience&id=<?php echo (int) $item['id']; ?>" onclick="return confirm('Delete this entry?');"><i class="fa-solid fa-trash"></i> Delete</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>
</body>
</html>
