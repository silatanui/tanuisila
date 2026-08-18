<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/education.php';
ensureEducationDetails($pdo);
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
    $stmt = $pdo->prepare('SELECT * FROM education WHERE id = ?');
    $stmt->execute([$editId]);
    $editingEntry = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$editingEntry) {
        $error = 'Education entry not found.';
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_education') {
    try {
        $institution = trim($_POST['institution'] ?? '');
        $degree = trim($_POST['degree'] ?? '');
        $field_name = trim($_POST['field_name'] ?? '');
        $start_year = trim($_POST['start_year'] ?? '');
        $end_year = trim($_POST['end_year'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $education_details = trim($_POST['education_details'] ?? '');
        $sort_order = (int) ($_POST['sort_order'] ?? 0);
        $entryId = !empty($_POST['entry_id']) ? (int)$_POST['entry_id'] : 0;

        if ($institution === '') {
            throw new InvalidArgumentException('Institution is required.');
        }

        if ($entryId > 0) {
            // UPDATE existing entry
            $stmt = $pdo->prepare('UPDATE education SET institution=?, degree=?, field_name=?, start_year=?, end_year=?, description=?, education_details=?, sort_order=? WHERE id=?');
            $stmt->execute([$institution, $degree, $field_name, $start_year, $end_year, $description, $education_details, $sort_order, $entryId]);
            $notice = 'Education entry updated successfully.';
            $editId = 0;
            $editingEntry = null;
        } else {
            // INSERT new entry
            $stmt = $pdo->prepare('INSERT INTO education (institution, degree, field_name, start_year, end_year, description, education_details, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$institution, $degree, $field_name, $start_year, $end_year, $description, $education_details, $sort_order]);
            $notice = 'Education entry added successfully.';
        }
    } catch (Throwable $e) {
        $error = 'Unable to save education. ' . $e->getMessage();
    }
}

if (isset($_GET['delete']) && $_GET['delete'] === 'education') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM education WHERE id = ?')->execute([$id]);
        $notice = 'Education entry removed.';
    }
}

$education = $pdo->query('SELECT * FROM education ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Education</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('education.php'); ?>

    <main class="main-panel">
      <?php echo renderTopbar('Education'); ?>

      <?php if (!empty($notice)): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
      <?php if (!empty($error)): ?><div class="error-box"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <section class="panel">
        <h2><?php echo $editingEntry ? '✏️ Edit Education' : '📝 Add Education'; ?></h2>
        <?php if ($editingEntry): ?><div style="background: rgba(15, 118, 110, 0.08); border: 1px solid rgba(15, 118, 110, 0.2); padding: 12px; margin-bottom: 16px; font-size: 0.9rem; color: var(--text);">Editing: <strong><?php echo htmlspecialchars($editingEntry['institution']); ?></strong> (ID: <?php echo (int)$editingEntry['id']; ?>)</div><?php endif; ?>
        <form method="post">
          <div class="grid-form">
            <div class="field">
              <label>Institution</label>
              <input name="institution" required value="<?php echo htmlspecialchars($editingEntry['institution'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>Degree</label>
              <input name="degree" value="<?php echo htmlspecialchars($editingEntry['degree'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>Field</label>
              <input name="field_name" value="<?php echo htmlspecialchars($editingEntry['field_name'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>Start year</label>
              <input name="start_year" value="<?php echo htmlspecialchars($editingEntry['start_year'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>End year</label>
              <input name="end_year" value="<?php echo htmlspecialchars($editingEntry['end_year'] ?? ''); ?>">
            </div>
            <div class="field">
              <label>Sort order</label>
              <input type="number" name="sort_order" value="<?php echo htmlspecialchars($editingEntry['sort_order'] ?? '0'); ?>">
            </div>
            <div class="field full">
              <label>Academic details</label>
              <textarea name="education_details" rows="12" placeholder="Add grades, credits, website, core courses, thesis, or other academic details..."><?php echo htmlspecialchars($editingEntry['education_details'] ?? ''); ?></textarea>
              <small class="muted-copy">Use a new line for each course or detail. These details are displayed on the public Education page.</small>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn primary"><?php echo $editingEntry ? 'Update Entry' : 'Add Education'; ?></button>
            <?php if ($editingEntry): ?><a href="education.php" class="btn">Cancel</a><?php endif; ?>
          </div>
          <input type="hidden" name="action" value="save_education">
          <?php if ($editingEntry): ?><input type="hidden" name="entry_id" value="<?php echo (int)$editingEntry['id']; ?>"><?php endif; ?>
        </form>
      </section>

      <section class="panel">
        <h2>Saved entries</h2>
        <?php if (empty($education)): ?>
          <p class="muted-copy">No education entries added yet.</p>
        <?php else: ?>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Institution</th>
                  <th>Degree</th>
                  <th>Field</th>
                  <th>Period</th>
                  <th>Academic details</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($education as $item): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($item['institution']); ?></td>
                    <td><?php echo htmlspecialchars($item['degree'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($item['field_name'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($item['start_year'] ?: '—'); ?> - <?php echo htmlspecialchars($item['end_year'] ?: 'Present'); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($item['education_details'] ?: $item['description'] ?: '—')); ?></td>
                    <td>
                      <a class="btn small" href="?edit=<?php echo (int) $item['id']; ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                      <a class="btn danger small" href="?delete=education&id=<?php echo (int) $item['id']; ?>" onclick="return confirm('Delete this entry?');"><i class="fa-solid fa-trash"></i> Delete</a>
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
