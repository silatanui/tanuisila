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
$editingProject = null;
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM research_projects WHERE id = ?');
    $stmt->execute([$editId]);
    $editingProject = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$editingProject) {
        $error = 'Research project not found.';
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_research_project') {
    try {
        $recordId = (int) ($_POST['record_id'] ?? 0);
        $sourceId = (int) ($_POST['source_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $yearLabel = trim($_POST['year_label'] ?? '');
        $shortDescription = trim($_POST['short_description'] ?? '');
        $longDescription = trim($_POST['long_description'] ?? '');
        $techStackInput = trim($_POST['tech_stack'] ?? '');
        $highlightsInput = trim($_POST['highlights'] ?? '');
        $demoUrl = trim($_POST['demo_url'] ?? '');
        $githubUrl = trim($_POST['github_url'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($sourceId <= 0) {
            throw new InvalidArgumentException('Source ID must be greater than zero.');
        }
        if ($title === '') {
            throw new InvalidArgumentException('Title is required.');
        }
        if ($category === '') {
            throw new InvalidArgumentException('Category is required.');
        }
        if ($yearLabel === '') {
            throw new InvalidArgumentException('Year label is required.');
        }
        if ($shortDescription === '') {
            throw new InvalidArgumentException('Short description is required.');
        }
        if ($longDescription === '') {
            throw new InvalidArgumentException('Long description is required.');
        }

        $techStack = array_values(array_filter(array_map('trim', explode(',', $techStackInput)), static function ($item) {
            return $item !== '';
        }));
        $highlights = array_values(array_filter(array_map('trim', explode(',', $highlightsInput)), static function ($item) {
            return $item !== '';
        }));

        if (empty($techStack)) {
            throw new InvalidArgumentException('Provide at least one tech stack item.');
        }

        if ($recordId > 0) {
            $stmt = $pdo->prepare('UPDATE research_projects SET source_id = ?, title = ?, category = ?, year_label = ?, short_description = ?, long_description = ?, tech_stack_json = ?, highlights_json = ?, demo_url = ?, github_url = ?, sort_order = ?, is_active = ? WHERE id = ?');
            $stmt->execute([
                $sourceId,
                $title,
                $category,
                $yearLabel,
                $shortDescription,
                $longDescription,
                json_encode($techStack, JSON_UNESCAPED_UNICODE),
                json_encode($highlights, JSON_UNESCAPED_UNICODE),
                $demoUrl,
                $githubUrl,
                $sortOrder,
                $isActive,
                $recordId,
            ]);
            $notice = 'Research project updated successfully.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO research_projects (source_id, title, category, year_label, short_description, long_description, tech_stack_json, highlights_json, demo_url, github_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $sourceId,
                $title,
                $category,
                $yearLabel,
                $shortDescription,
                $longDescription,
                json_encode($techStack, JSON_UNESCAPED_UNICODE),
                json_encode($highlights, JSON_UNESCAPED_UNICODE),
                $demoUrl,
                $githubUrl,
                $sortOrder,
                $isActive,
            ]);
            $notice = 'Research project created successfully.';
        }

        $editingProject = null;
        $editId = 0;
    } catch (Throwable $e) {
        $error = 'Unable to save research project. ' . $e->getMessage();
    }
}

if (isset($_GET['delete']) && $_GET['delete'] === 'research_projects') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare('DELETE FROM research_projects WHERE id = ?');
            $stmt->execute([$id]);
            $notice = 'Research project deleted.';
        } catch (Throwable $e) {
            $error = 'Unable to delete research project.';
        }
    }
}

$records = $pdo->query('SELECT * FROM research_projects ORDER BY sort_order ASC, source_id ASC')->fetchAll(PDO::FETCH_ASSOC);

$nextSourceId = 1;
if (!empty($records)) {
    $sourceIds = array_map(static function ($row) {
        return (int) ($row['source_id'] ?? 0);
    }, $records);
    $nextSourceId = max($sourceIds) + 1;
}

$formSourceId = (int) ($editingProject['source_id'] ?? $nextSourceId);
$formTitle = (string) ($editingProject['title'] ?? '');
$formCategory = (string) ($editingProject['category'] ?? '');
$formYearLabel = (string) ($editingProject['year_label'] ?? '');
$formShortDescription = (string) ($editingProject['short_description'] ?? '');
$formLongDescription = (string) ($editingProject['long_description'] ?? '');
$formTechStack = '';
$formHighlights = '';
$formDemoUrl = (string) ($editingProject['demo_url'] ?? '');
$formGithubUrl = (string) ($editingProject['github_url'] ?? '');
$formSortOrder = (int) ($editingProject['sort_order'] ?? 0);
$formIsActive = !isset($editingProject['is_active']) || (int) $editingProject['is_active'] === 1;

if ($editingProject) {
    $decodedTech = json_decode((string) ($editingProject['tech_stack_json'] ?? '[]'), true);
    $decodedHighlights = json_decode((string) ($editingProject['highlights_json'] ?? '[]'), true);
    $formTechStack = is_array($decodedTech) ? implode(', ', $decodedTech) : '';
    $formHighlights = is_array($decodedHighlights) ? implode(', ', $decodedHighlights) : '';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Research Projects</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('research_projects.php'); ?>
    <main class="main-panel">
      <?php echo renderTopbar('Research Projects'); ?>

      <?php if (!empty($notice)): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
      <?php if (!empty($error)): ?><div class="error-box"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <section class="panel">
        <h2><?php echo $editingProject ? 'Edit research project' : 'Add research project'; ?></h2>
        <form method="post">
          <div class="grid-form">
            <div class="field"><label>Source ID</label><input type="number" name="source_id" min="1" required value="<?php echo (int) $formSourceId; ?>"></div>
            <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="<?php echo (int) $formSortOrder; ?>"></div>
            <div class="field full"><label>Title</label><input name="title" required value="<?php echo htmlspecialchars($formTitle); ?>"></div>
            <div class="field"><label>Category</label><input name="category" required value="<?php echo htmlspecialchars($formCategory); ?>"></div>
            <div class="field"><label>Year Label</label><input name="year_label" required value="<?php echo htmlspecialchars($formYearLabel); ?>"></div>
            <div class="field full"><label>Short Description</label><textarea name="short_description" required><?php echo htmlspecialchars($formShortDescription); ?></textarea></div>
            <div class="field full"><label>Long Description</label><textarea name="long_description" required><?php echo htmlspecialchars($formLongDescription); ?></textarea></div>
            <div class="field full"><label>Tech Stack (comma separated)</label><input name="tech_stack" required value="<?php echo htmlspecialchars($formTechStack); ?>"></div>
            <div class="field full"><label>Highlights (comma separated)</label><input name="highlights" value="<?php echo htmlspecialchars($formHighlights); ?>"></div>
            <div class="field"><label>Demo URL</label><input type="url" name="demo_url" value="<?php echo htmlspecialchars($formDemoUrl); ?>"></div>
            <div class="field"><label>GitHub URL</label><input type="url" name="github_url" value="<?php echo htmlspecialchars($formGithubUrl); ?>"></div>
            <div class="field full" style="display:flex; align-items:center; gap:10px;">
              <input type="checkbox" id="is_active" name="is_active" <?php echo $formIsActive ? 'checked' : ''; ?>>
              <label for="is_active" style="margin:0;">Visible on website</label>
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn primary"><?php echo $editingProject ? 'Update project' : 'Create project'; ?></button>
            <?php if ($editingProject): ?><a href="research_projects.php" class="btn">Cancel</a><?php endif; ?>
          </div>
          <input type="hidden" name="action" value="save_research_project">
          <?php if ($editingProject): ?><input type="hidden" name="record_id" value="<?php echo (int) $editingProject['id']; ?>"><?php endif; ?>
        </form>
      </section>

      <section class="panel">
        <h2>Saved research projects</h2>
        <div class="list">
          <?php foreach ($records as $item): ?>
            <?php
              $stack = json_decode((string) ($item['tech_stack_json'] ?? '[]'), true);
              $stackText = is_array($stack) ? implode(', ', $stack) : '';
            ?>
            <div class="list-item">
              <div>
                <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                <small><?php echo htmlspecialchars($item['category']); ?> • <?php echo htmlspecialchars($item['year_label']); ?> • Source ID <?php echo (int) $item['source_id']; ?></small>
                <small><?php echo htmlspecialchars($item['short_description']); ?></small>
                <?php if ($stackText !== ''): ?><small>Stack: <?php echo htmlspecialchars($stackText); ?></small><?php endif; ?>
              </div>
              <div class="list-meta">
                <span class="tag"><?php echo (int) $item['sort_order']; ?></span>
                <span class="tag"><?php echo (int) $item['is_active'] === 1 ? 'Active' : 'Hidden'; ?></span>
                <?php if (!empty($item['demo_url']) && $item['demo_url'] !== '#'): ?><a class="btn" href="<?php echo htmlspecialchars($item['demo_url']); ?>" target="_blank">Demo</a><?php endif; ?>
                <?php if (!empty($item['github_url']) && $item['github_url'] !== '#'): ?><a class="btn" href="<?php echo htmlspecialchars($item['github_url']); ?>" target="_blank">GitHub</a><?php endif; ?>
                <a class="btn" href="?edit=<?php echo (int) $item['id']; ?>">Edit</a>
                <a class="btn danger" href="?delete=research_projects&id=<?php echo (int) $item['id']; ?>" onclick="return confirm('Delete this research project?');">Delete</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
