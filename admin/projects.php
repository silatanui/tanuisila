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

// Check if in Edit mode
$editId = (int) ($_GET['edit'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$editId]);
    $editingProject = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$editingProject) {
        $error = 'The requested project was not found.';
    }
}

// Handle Form Submissions (Create or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_project') {
    try {
        $projectId   = (int) ($_POST['project_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $url         = trim($_POST['url'] ?? '');
        $image       = trim($_POST['image'] ?? '');
        $tags        = trim($_POST['tags'] ?? '');
        $sort_order  = (int) ($_POST['sort_order'] ?? 0);

        if ($title === '') {
            throw new InvalidArgumentException('Project title is required.');
        }

        if ($projectId > 0) {
            // Update existing project
            $stmt = $pdo->prepare('UPDATE projects SET title = ?, description = ?, url = ?, image = ?, tags = ?, sort_order = ? WHERE id = ?');
            $stmt->execute([$title, $description, $url, $image, $tags, $sort_order, $projectId]);
            $notice = 'Project "' . htmlspecialchars($title) . '" updated successfully.';
            
            // Refresh editing state
            $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
            $stmt->execute([$projectId]);
            $editingProject = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            // Insert new project
            $stmt = $pdo->prepare('INSERT INTO projects (title, description, url, image, tags, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $description, $url, $image, $tags, $sort_order]);
            $notice = 'Project "' . htmlspecialchars($title) . '" created successfully.';
        }
    } catch (Throwable $e) {
        $error = 'Unable to save project. ' . $e->getMessage();
    }
}

// Handle Delete Action
if (isset($_GET['delete']) && $_GET['delete'] === 'projects') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
        $notice = 'Project removed successfully.';
        if ($editId === $id) {
            $editingProject = null;
        }
    }
}

// Fetch all saved projects
$projects = $pdo->query('SELECT * FROM projects ORDER BY sort_order DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $editingProject ? 'Edit Project' : 'Projects'; ?> - Admin</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
  <style>
    /* Drag & Drop Zone Styles */
    .dropzone-container {
      border: 2px dashed var(--line);
      background: var(--panel-alt);
      padding: 24px 16px;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
      margin-bottom: 10px;
    }
    .dropzone-container:hover,
    .dropzone-container.dragover {
      border-color: var(--primary);
      background: rgba(234, 88, 12, 0.05);
    }
    .dropzone-icon {
      font-size: 2rem;
      color: var(--primary);
      margin-bottom: 8px;
    }
    .dropzone-text {
      font-weight: 700;
      color: var(--text);
      font-size: 0.9rem;
      margin-bottom: 4px;
    }
    .dropzone-sub {
      font-size: 0.76rem;
      color: var(--muted);
    }
    .image-preview-card {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 12px;
      background: var(--soft-bg);
      border: 1px solid var(--light-gray);
      margin-top: 10px;
    }
    .image-preview-thumb {
      width: 120px;
      height: 70px;
      object-fit: cover;
      border: 1px solid var(--light-gray);
      background: #fff;
    }
    .project-row-card {
      display: grid;
      grid-template-columns: 80px 1fr auto;
      gap: 16px;
      align-items: center;
      background: var(--panel-alt);
      border: 1px solid var(--line);
      padding: 16px;
      margin-bottom: 12px;
    }
    .project-thumb-mini {
      width: 80px;
      height: 54px;
      object-fit: cover;
      border: 1px solid var(--line);
      background: #fff;
    }
    .upload-progress {
      display: none;
      margin-top: 8px;
      font-size: 0.8rem;
      color: var(--primary);
      font-weight: 700;
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('projects.php'); ?>
    <main class="main-panel">
      <?php echo renderTopbar('Projects'); ?>
      
      <?php if (!empty($notice)): ?>
        <div class="notice"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($notice); ?></div>
      <?php endif; ?>
      
      <?php if (!empty($error)): ?>
        <div class="error-box"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <!-- ═══════════════════════════════════════════════════════ -->
      <!-- PROJECT FORM (ADD / EDIT)                              -->
      <!-- ═══════════════════════════════════════════════════════ -->
      <section class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="margin: 0;">
            <?php if ($editingProject): ?>
              <i class="fa-solid fa-pen-to-square" style="color: var(--primary);"></i> Edit Project #<?php echo (int) $editingProject['id']; ?>
            <?php else: ?>
              <i class="fa-solid fa-plus" style="color: var(--primary);"></i> Add New Project
            <?php endif; ?>
          </h2>

          <?php if ($editingProject): ?>
            <a href="projects.php" class="btn small" style="background: transparent; border: 1px solid var(--line);">
              <i class="fa-solid fa-xmark"></i> Cancel Edit
            </a>
          <?php endif; ?>
        </div>

        <?php if ($editingProject): ?>
          <div style="background: rgba(234, 88, 12, 0.08); border-left: 3px solid var(--primary); padding: 10px 14px; margin-bottom: 20px; font-size: 0.88rem; color: var(--text);">
            Currently modifying: <strong><?php echo htmlspecialchars($editingProject['title']); ?></strong>
          </div>
        <?php endif; ?>

        <form method="post" id="project-form">
          <input type="hidden" name="action" value="save_project">
          <input type="hidden" name="project_id" value="<?php echo $editingProject ? (int)$editingProject['id'] : '0'; ?>">

          <div class="grid-form">
            <div class="field">
              <label>Project Title *</label>
              <input name="title" required value="<?php echo htmlspecialchars($editingProject['title'] ?? ''); ?>" placeholder="e.g. CV Studio">
            </div>

            <div class="field">
              <label>Live URL / Repository Link</label>
              <input name="url" type="url" value="<?php echo htmlspecialchars($editingProject['url'] ?? ''); ?>" placeholder="https://tanuisila.dev/cv_studio/">
            </div>

            <div class="field">
              <label>Technology Tags (Comma Separated)</label>
              <input name="tags" value="<?php echo htmlspecialchars($editingProject['tags'] ?? ''); ?>" placeholder="e.g. AI Assistant, ATS Engine, React, PDF Export">
            </div>

            <div class="field">
              <label>Sort Order / Display Priority</label>
              <input type="number" name="sort_order" value="<?php echo htmlspecialchars($editingProject['sort_order'] ?? '0'); ?>" placeholder="Higher numbers appear first">
            </div>

            <!-- DRAG & DROP IMAGE UPLOAD AREA -->
            <div class="field full">
              <label>Project Screenshot / Cover Image</label>
              
              <!-- Dropzone Box -->
              <div class="dropzone-container" id="project-dropzone" onclick="document.getElementById('project-file-input').click()">
                <input type="file" id="project-file-input" accept="image/*" style="display: none;">
                <div class="dropzone-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <div class="dropzone-text">Drag &amp; Drop project screenshot here, or click to browse</div>
                <div class="dropzone-sub">Supports PNG, JPG, WebP, GIF up to 10MB</div>
                <div class="upload-progress" id="upload-progress-text"><i class="fa-solid fa-spinner fa-spin"></i> Uploading image...</div>
              </div>

              <!-- Manual / Auto Image URL Input -->
              <div style="display: flex; gap: 8px; align-items: center; margin-top: 6px;">
                <input type="text" name="image" id="project-image-url" value="<?php echo htmlspecialchars($editingProject['image'] ?? ''); ?>" placeholder="Image path or URL (e.g. cv_studio.png or https://...)" style="flex: 1;">
                <button type="button" class="btn small" onclick="document.getElementById('project-file-input').click()">
                  <i class="fa-solid fa-folder-open"></i> Browse
                </button>
              </div>

              <!-- Live Image Preview Card -->
              <div id="image-preview-wrap" style="<?php echo empty($editingProject['image']) ? 'display:none;' : ''; ?>">
                <div class="image-preview-card">
                  <?php 
                    $previewSrc = '';
                    if (!empty($editingProject['image'])) {
                        $imgVal = $editingProject['image'];
                        if (preg_match('#^https?://#i', $imgVal)) {
                            $previewSrc = $imgVal;
                        } elseif (strpos($imgVal, '/') === 0) {
                            $previewSrc = $imgVal;
                        } else {
                            $previewSrc = '../public/' . $imgVal;
                        }
                    }
                  ?>
                  <img id="preview-img" src="<?php echo htmlspecialchars($previewSrc); ?>" class="image-preview-thumb" alt="Preview">
                  <div style="flex: 1;">
                    <strong style="font-size: 0.85rem; display: block; color: var(--text);" id="preview-filename">
                      <?php echo htmlspecialchars($editingProject['image'] ?? 'Project Image'); ?>
                    </strong>
                    <small style="color: var(--primary); font-weight: 600;">✓ Image ready for display</small>
                  </div>
                  <button type="button" class="btn small danger" onclick="clearProjectImage()">
                    <i class="fa-solid fa-trash"></i> Remove
                  </button>
                </div>
              </div>
            </div>

            <div class="field full">
              <label>Detailed Project Description</label>
              <textarea name="description" rows="4" placeholder="Overview of the system, problem solved, features implemented, and architectural details..."><?php echo htmlspecialchars($editingProject['description'] ?? ''); ?></textarea>
            </div>
          </div>

          <div class="form-actions" style="margin-top: 16px; display: flex; gap: 12px;">
            <button type="submit" class="btn primary">
              <i class="fa-solid fa-floppy-disk"></i> <?php echo $editingProject ? 'Save Changes' : 'Add Project'; ?>
            </button>
            <?php if ($editingProject): ?>
              <a href="projects.php" class="btn" style="background: transparent; border: 1px solid var(--line);">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </section>

      <!-- ═══════════════════════════════════════════════════════ -->
      <!-- SAVED PROJECTS LIST                                    -->
      <!-- ═══════════════════════════════════════════════════════ -->
      <section class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h2 style="margin: 0;"><i class="fa-solid fa-list-check" style="color: var(--primary);"></i> Saved Projects (<?php echo count($projects); ?>)</h2>
        </div>

        <?php if (empty($projects)): ?>
          <p style="color: var(--muted); font-size: 0.9rem;">No projects saved yet. Add your first project above.</p>
        <?php else: ?>
          <div>
            <?php foreach ($projects as $item): ?>
              <?php 
                $itemThumb = '';
                if (!empty($item['image'])) {
                    $imgVal = $item['image'];
                    if (preg_match('#^https?://#i', $imgVal)) {
                        $itemThumb = $imgVal;
                    } elseif (strpos($imgVal, '/') === 0) {
                        $itemThumb = $imgVal;
                    } else {
                        $itemThumb = '../public/' . $imgVal;
                    }
                }
              ?>
              <div class="project-row-card" style="<?php echo ($editingProject && $editingProject['id'] == $item['id']) ? 'border-color: var(--primary); background: rgba(234, 88, 12, 0.04);' : ''; ?>">
                
                <!-- Thumbnail -->
                <div>
                  <?php if (!empty($itemThumb)): ?>
                    <img src="<?php echo htmlspecialchars($itemThumb); ?>?v=<?php echo time(); ?>" class="project-thumb-mini" alt="Thumb">
                  <?php else: ?>
                    <div style="width: 80px; height: 54px; background: var(--panel); border: 1px solid var(--line); display: flex; align-items: center; justify-content: center; color: var(--muted); font-size: 1.2rem;">
                      <i class="fa-solid fa-code"></i>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Info -->
                <div>
                  <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <strong style="font-size: 1.05rem; color: var(--text);"><?php echo htmlspecialchars($item['title']); ?></strong>
                    <span class="tag" style="font-size: 0.7rem;">Sort #<?php echo (int) $item['sort_order']; ?></span>
                    <?php if ($editingProject && $editingProject['id'] == $item['id']): ?>
                      <span style="font-size: 0.7rem; font-weight: 800; color: var(--primary); text-transform: uppercase;">(Editing Now)</span>
                    <?php endif; ?>
                  </div>

                  <?php if (!empty($item['tags'])): ?>
                    <div style="margin: 4px 0;">
                      <small style="color: var(--primary); font-weight: 600;"><i class="fa-solid fa-tag"></i> <?php echo htmlspecialchars($item['tags']); ?></small>
                    </div>
                  <?php endif; ?>

                  <p style="margin: 4px 0 0; font-size: 0.85rem; color: var(--muted); line-height: 1.4;">
                    <?php echo htmlspecialchars($item['description']); ?>
                  </p>

                  <?php if (!empty($item['url'])): ?>
                    <div style="margin-top: 4px;">
                      <a href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank" style="font-size: 0.78rem; color: var(--primary); font-family: monospace; text-decoration: underline;">
                        <?php echo htmlspecialchars($item['url']); ?>
                      </a>
                    </div>
                  <?php endif; ?>
                </div>

                <!-- Actions -->
                <div style="display: flex; flex-direction: column; gap: 6px; align-items: flex-end;">
                  <a class="btn small" href="?edit=<?php echo (int) $item['id']; ?>" style="width: 100%; text-align: center;">
                    <i class="fa-solid fa-pen"></i> Edit
                  </a>
                  <?php if (!empty($item['url'])): ?>
                    <a class="btn small" href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank" style="width: 100%; text-align: center; background: transparent; border: 1px solid var(--line);">
                      <i class="fa-solid fa-arrow-up-right-from-square"></i> Open
                    </a>
                  <?php endif; ?>
                  <a class="btn danger small" href="?delete=projects&id=<?php echo (int) $item['id']; ?>" onclick="return confirm('Are you sure you want to delete project: <?php echo htmlspecialchars($item['title']); ?>?');" style="width: 100%; text-align: center;">
                    <i class="fa-solid fa-trash"></i> Delete
                  </a>
                </div>

              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

    </main>
  </div>

  <script>
    // ═══════════════════════════════════════════════════════
    // DRAG & DROP + INSTANT FILE UPLOAD HANDLER
    // ═══════════════════════════════════════════════════════
    const dropzone = document.getElementById('project-dropzone');
    const fileInput = document.getElementById('project-file-input');
    const urlInput = document.getElementById('project-image-url');
    const previewWrap = document.getElementById('image-preview-wrap');
    const previewImg = document.getElementById('preview-img');
    const previewFilename = document.getElementById('preview-filename');
    const progressText = document.getElementById('upload-progress-text');

    // Prevent defaults for drag events
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, preventDefaults, false);
      document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    // Highlight dropzone on dragover
    ['dragenter', 'dragover'].forEach(eventName => {
      dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'), false);
    });

    // Handle dropped files
    dropzone.addEventListener('drop', (e) => {
      const dt = e.dataTransfer;
      const files = dt.files;
      if (files.length > 0) {
        handleImageUpload(files[0]);
      }
    });

    // Handle file picker selection
    fileInput.addEventListener('change', function() {
      if (this.files.length > 0) {
        handleImageUpload(this.files[0]);
      }
    });

    // Upload function
    async function handleImageUpload(file) {
      if (!file.type.match('image.*')) {
        alert('Please drop or select a valid image file (PNG, JPG, WebP, GIF, SVG).');
        return;
      }

      if (file.size > 10 * 1024 * 1024) {
        alert('File size exceeds the 10MB limit.');
        return;
      }

      progressText.style.display = 'block';

      const formData = new FormData();
      formData.append('file', file);
      formData.append('type', 'projects');

      try {
        const response = await fetch('api_upload_image.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();
        progressText.style.display = 'none';

        if (data.success) {
          // Set filename or location in input
          urlInput.value = data.filename || data.location;
          
          // Update preview
          previewImg.src = (data.location || data.url) + '?v=' + Date.now();
          previewFilename.textContent = data.filename || file.name;
          previewWrap.style.display = 'block';
        } else {
          alert('Upload failed: ' + (data.error || 'Unknown error occurred.'));
        }
      } catch (err) {
        progressText.style.display = 'none';
        alert('Upload network error: ' + err.message);
      }
    }

    // Update preview if image URL input changes manually
    urlInput.addEventListener('input', function() {
      const val = this.value.trim();
      if (val) {
        let src = val;
        if (!src.startsWith('http') && !src.startsWith('/')) {
          src = '../public/' + src;
        }
        previewImg.src = src;
        previewFilename.textContent = val;
        previewWrap.style.display = 'block';
      } else {
        previewWrap.style.display = 'none';
      }
    });

    function clearProjectImage() {
      urlInput.value = '';
      previewWrap.style.display = 'none';
      previewImg.src = '';
      fileInput.value = '';
    }
  </script>
</body>
</html>
