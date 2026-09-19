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
$editingPost = null;
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

// Load post for editing if ID provided
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id = ?');
    $stmt->execute([$editId]);
    $editingPost = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$editingPost) {
        $error = 'Blog post not found.';
        $editId = 0;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_blog') {
    try {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $featured_image = trim($_POST['featured_image'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        $author_name = trim($_POST['author_name'] ?? 'Tanui Kipng\'etich Sila');
        $status = $_POST['status'] ?? 'draft';
        $reading_time = !empty($_POST['reading_time']) ? (int)$_POST['reading_time'] : NULL;
        $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : NULL;
        $seo_title = trim($_POST['seo_title'] ?? '');
        $seo_description = trim($_POST['seo_description'] ?? '');
        $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;
        $postId = !empty($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

        if ($title === '') {
            throw new InvalidArgumentException('Title is required.');
        }
        if ($content === '') {
            throw new InvalidArgumentException('Content is required.');
        }

        if ($slug === '') {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $title));
            $slug = trim($slug, '-');
        }

        if ($postId > 0) {
            // UPDATE existing post
            $stmt = $pdo->prepare('UPDATE blog_posts SET title=?, slug=?, excerpt=?, content=?, featured_image=?, category=?, tags=?, author_name=?, status=?, reading_time=?, published_at=?, seo_title=?, seo_description=?, allow_comments=? WHERE id=?');
            $stmt->execute([$title, $slug, $excerpt, $content, $featured_image, $category, $tags, $author_name, $status, $reading_time, $published_at, $seo_title, $seo_description, $allow_comments, $postId]);
            $notice = 'Blog post updated successfully.';
            $editId = 0;
            $editingPost = null;
        } else {
            // INSERT new post
            $stmt = $pdo->prepare('INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, category, tags, author_name, status, reading_time, published_at, seo_title, seo_description, allow_comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $slug, $excerpt, $content, $featured_image, $category, $tags, $author_name, $status, $reading_time, $published_at, $seo_title, $seo_description, $allow_comments]);
            $notice = 'Blog post created successfully.';
        }
    } catch (Throwable $e) {
        $error = 'Unable to save blog post. ' . $e->getMessage();
    }
}

if (isset($_GET['delete']) && $_GET['delete'] === 'blog_posts') {
    $id = (int) ($_GET['id'] ?? 0);
    if ($id > 0) {
        $pdo->prepare('DELETE FROM blog_posts WHERE id = ?')->execute([$id]);
        $notice = 'Blog post removed.';
    }
}

$posts = $pdo->query('
    SELECT p.*, 
           (SELECT COUNT(*) FROM blog_comments c WHERE c.post_id = p.id) AS total_comments,
           (SELECT COUNT(*) FROM blog_comments c WHERE c.post_id = p.id AND c.status = "pending") AS pending_comments
    FROM blog_posts p 
    ORDER BY p.created_at DESC, p.id DESC
')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Blog Posts - Admin</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
  <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js"></script>
  <style>
    /* Enforce Outfit font across entire admin page and TinyMCE container */
    body, button, input, select, textarea {
      font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    }

    /* TinyMCE Shell & UI Typography */
    .tox, .tox * {
      font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    }
    .tox .tox-toolbar, .tox .tox-menubar, .tox .tox-button, .tox .tox-tbtn, .tox .tox-mbtn, .tox .tox-statusbar {
      font-family: 'Outfit', sans-serif !important;
    }
    .tox .tox-placeholder {
      font-family: 'Outfit', sans-serif !important;
      font-size: 15px !important;
      color: #94a3b8 !important;
    }

    .ai-hero-box {
      background: linear-gradient(135deg, rgba(234, 88, 12, 0.07) 0%, rgba(15, 23, 42, 0.03) 100%);
      border: 1px solid rgba(234, 88, 12, 0.28);
      padding: 20px;
      margin-bottom: 24px;
      position: relative;
    }
    .btn-ai-hero {
      background: var(--primary);
      color: #ffffff !important;
      border: 1px solid var(--primary);
      padding: 12px 24px;
      font-weight: 800;
      font-size: 0.9rem;
      letter-spacing: 0.02em;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 14px rgba(234, 88, 12, 0.28);
    }
    .btn-ai-hero:hover {
      background: var(--primary-hover);
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(234, 88, 12, 0.35);
    }
    .btn-micro-ai {
      background: #ffffff;
      border: 1px solid var(--line-strong);
      color: var(--primary);
      font-size: 0.74rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      padding: 3px 10px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: all 0.15s ease;
    }
    .btn-micro-ai:hover {
      background: var(--primary);
      color: #ffffff;
      border-color: var(--primary);
    }
    .field-header-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 6px;
      flex-wrap: wrap;
      gap: 6px;
    }
    .field-header-row label {
      margin: 0;
      font-weight: 700;
    }
    .field-ai-highlight {
      animation: fieldAiHighlight 2s ease forwards;
    }
    @keyframes fieldAiHighlight {
      0% {
        background-color: rgba(234, 88, 12, 0.2);
        box-shadow: 0 0 0 2px var(--primary);
      }
      100% {
        background-color: #ffffff;
        box-shadow: none;
      }
    }
    .featured-step-guide {
      border: 1px dashed var(--primary-border);
      background: rgba(234, 88, 12, 0.02);
      padding: 16px;
      margin-top: 6px;
      transition: all 0.3s ease;
    }
    .featured-step-guide.highlighted {
      border: 2px solid var(--primary);
      background: rgba(234, 88, 12, 0.08);
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('blog.php'); ?>
    <main class="main-panel">
      <?php echo renderTopbar('Blog Posts'); ?>

      <?php if (!empty($notice)): ?><div class="notice"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
      <?php if (!empty($error)): ?><div class="error-box"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <!-- ═══════════════════════════════════════════════════════ -->
      <!-- BLOG POST EDITOR PANEL                                  -->
      <!-- ═══════════════════════════════════════════════════════ -->
      <section class="panel" id="post-editor-panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--line);">
          <h2 style="margin: 0;"><?php echo $editingPost ? '✏️ Edit Blog Post' : '📝 Create Blog Post'; ?></h2>
          <?php if ($editingPost): ?>
            <span style="font-size: 0.85rem; color: var(--muted);">
              Editing: <strong><?php echo htmlspecialchars($editingPost['title']); ?></strong> (ID: <?php echo (int)$editingPost['id']; ?>)
            </span>
          <?php endif; ?>
        </div>

        <!-- ═══ MASTER AI AUTOMATION BANNER ═══ -->
        <div class="ai-hero-box">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="flex: 1; min-width: 280px;">
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--primary); font-size: 1.15rem;"></i>
                <strong style="color: var(--text); font-size: 1.05rem;">Full AI Blog Automation</strong>
                <span style="background: var(--primary-soft); color: var(--primary); font-weight: 800; font-size: 0.72rem; padding: 2px 8px; border: 1px solid var(--primary-border); text-transform: uppercase;">
                  GPT-4o Mini
                </span>
              </div>
              <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem; line-height: 1.5;">
                Simply write or paste your article into the <strong>Content</strong> editor below. Click this button, and AI will automatically deduce and populate the <strong>Title, Abstract / Excerpt, Category, Tags, URL Slug, Reading Time, and SEO metadata</strong>. All you have to do is add your featured image!
              </p>
            </div>
            <div>
              <button type="button" class="btn-ai-hero" id="btn-master-ai" onclick="autoGenerateAllFromContent()">
                <i class="fa-solid fa-bolt-lightning"></i> ✨ Full AI Auto-Fill from Content
              </button>
            </div>
          </div>
          
          <div id="ai-status" style="margin-top: 14px; font-size: 0.88rem; display: none; padding: 12px 16px; border: 1px solid var(--line); background: #ffffff;"></div>
        </div>

        <form method="post" id="blog-form">
          <div class="grid-form">

            <!-- 1. Content Editor (Primary Input) -->
            <div class="field full">
              <div class="field-header-row">
                <label for="blog-content-editor"><i class="fa-solid fa-file-lines" style="color: var(--primary);"></i> Blog Content *</label>
                <div style="display: flex; gap: 6px; align-items: center;">
                  <button type="button" class="btn-micro-ai" onclick="autoGenerateAllFromContent()" title="Infer all metadata from content">
                    <i class="fa-solid fa-bolt-lightning"></i> AI Auto-Fill All
                  </button>
                  <button type="button" class="btn-micro-ai" onclick="analyzeContent()" title="Check word count & reading time">
                    <i class="fa-solid fa-chart-simple"></i> Analyze Stats
                  </button>
                </div>
              </div>
              <textarea id="blog-content-editor" name="content" placeholder="Write or paste your full article here..."><?php echo htmlspecialchars($editingPost['content'] ?? ''); ?></textarea>
            </div>

            <!-- 2. Title & Slug -->
            <div class="field full">
              <div class="field-header-row">
                <label for="field-title"><i class="fa-solid fa-heading" style="color: var(--primary);"></i> Title *</label>
                <button type="button" class="btn-micro-ai" onclick="generateTitle()">
                  <i class="fa-solid fa-wand-magic-sparkles"></i> AI Title
                </button>
              </div>
              <input id="field-title" name="title" required placeholder="Article Title (or let AI auto-generate from content)" value="<?php echo htmlspecialchars($editingPost['title'] ?? ''); ?>">
            </div>

            <div class="field">
              <label for="field-slug">URL Slug</label>
              <input id="field-slug" name="slug" placeholder="Auto-generated from title" value="<?php echo htmlspecialchars($editingPost['slug'] ?? ''); ?>">
            </div>

            <div class="field">
              <div class="field-header-row">
                <label for="field-category">Category</label>
                <button type="button" class="btn-micro-ai" onclick="generateCategory()">
                  <i class="fa-solid fa-wand-magic-sparkles"></i> AI Category
                </button>
              </div>
              <input id="field-category" name="category" placeholder="e.g. Artificial Intelligence, Software Engineering" value="<?php echo htmlspecialchars($editingPost['category'] ?? ''); ?>">
            </div>

            <div class="field">
              <label for="field-status">Status</label>
              <select id="field-status" name="status">
                <option value="draft" <?php echo ($editingPost['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="published" <?php echo ($editingPost['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                <option value="archived" <?php echo ($editingPost['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
              </select>
            </div>

            <!-- 3. Featured Image (The Only Manual Step) -->
            <div class="field full" id="featured-image-section">
              <div class="field-header-row">
                <label style="color: var(--text); font-size: 0.95rem;">
                  <i class="fa-solid fa-image" style="color: var(--primary);"></i> Featured Image
                  <span style="font-size: 0.78rem; font-weight: normal; color: var(--muted); margin-left: 6px;">(Upload file or paste image URL)</span>
                </label>
                <span style="font-size: 0.76rem; background: var(--primary-soft); color: var(--primary); padding: 2px 8px; font-weight: 800; text-transform: uppercase;">
                  Step 2: Add Image
                </span>
              </div>
              
              <div class="featured-step-guide" id="featured-guide-box">
                <div style="display: grid; grid-template-columns: 1fr 220px; gap: 16px; align-items: start;">
                  <div>
                    <div style="display: flex; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
                      <input type="file" id="featured-image-upload" accept="image/*" style="display: none;">
                      <button type="button" class="btn" onclick="document.getElementById('featured-image-upload').click()" style="background: var(--text); color: #ffffff; border-color: var(--text); font-weight: 700;">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Upload From Device
                      </button>
                    </div>
                    <small style="color: var(--muted); display: block; margin-bottom: 4px;">Or paste a direct image URL:</small>
                    <input type="text" name="featured_image" id="featured_image_url" placeholder="https://example.com/image.jpg" value="<?php echo htmlspecialchars($editingPost['featured_image'] ?? ''); ?>" style="width: 100%;">
                    <small style="display: block; color: var(--muted); margin-top: 6px;">Supported formats: JPG, PNG, GIF, WebP (Max 5MB)</small>
                  </div>
                  <div id="featured-preview-container" style="border: 1px solid var(--light-gray); background: #ffffff; padding: 8px; text-align: center; min-height: 140px; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($editingPost['featured_image'])): ?>
                      <img id="featured-preview" src="<?php echo htmlspecialchars($editingPost['featured_image']); ?>?v=<?php echo time(); ?>" style="max-width: 100%; max-height: 130px; object-fit: cover;">
                    <?php else: ?>
                      <small id="featured-preview-placeholder" style="color: var(--muted); text-align: center;">
                        <i class="fa-regular fa-image" style="font-size: 1.6rem; opacity: 0.4; display: block; margin-bottom: 4px;"></i>
                        No image selected
                      </small>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- 4. Abstract / Excerpt -->
            <div class="field full">
              <div class="field-header-row">
                <label for="field-excerpt"><i class="fa-solid fa-align-left" style="color: var(--primary);"></i> Abstract / Excerpt</label>
                <button type="button" class="btn-micro-ai" onclick="generateExcerpt()">
                  <i class="fa-solid fa-wand-magic-sparkles"></i> AI Abstract
                </button>
              </div>
              <textarea id="field-excerpt" name="excerpt" rows="3" placeholder="2-3 sentence executive abstract / summary of the post (auto-generated by AI)"><?php echo htmlspecialchars($editingPost['excerpt'] ?? ''); ?></textarea>
            </div>

            <!-- 5. Tags & Reading Time -->
            <div class="field full">
              <div class="field-header-row">
                <label for="field-tags"><i class="fa-solid fa-tags" style="color: var(--primary);"></i> Tags / Keywords</label>
                <button type="button" class="btn-micro-ai" onclick="generateTags()">
                  <i class="fa-solid fa-wand-magic-sparkles"></i> AI Tags
                </button>
              </div>
              <input id="field-tags" name="tags" placeholder="Comma-separated keywords (e.g. AI, Python, Software Engineering)" value="<?php echo htmlspecialchars($editingPost['tags'] ?? ''); ?>">
            </div>

            <div class="field">
              <label for="field-reading-time">Reading Time (minutes)</label>
              <input type="number" id="field-reading-time" name="reading_time" min="1" placeholder="e.g. 5" value="<?php echo htmlspecialchars($editingPost['reading_time'] ?? ''); ?>">
            </div>

            <div class="field">
              <label for="field-published-at">Published Date</label>
              <input type="datetime-local" id="field-published-at" name="published_at" value="<?php echo !empty($editingPost['published_at']) ? date('Y-m-d\TH:i', strtotime($editingPost['published_at'])) : ''; ?>">
            </div>

            <div class="field full">
              <label for="field-author-name">Author Name</label>
              <input id="field-author-name" name="author_name" value="<?php echo htmlspecialchars($editingPost['author_name'] ?? 'Tanui Kipng\'etich Sila'); ?>" placeholder="Author name">
            </div>

            <!-- 6. SEO Optimization Section -->
            <div class="field full">
              <div class="field-header-row">
                <label for="field-seo-title"><i class="fa-solid fa-magnifying-glass-chart" style="color: var(--primary);"></i> SEO Title</label>
                <button type="button" class="btn-micro-ai" onclick="generateSEOMeta()">
                  <i class="fa-solid fa-wand-magic-sparkles"></i> AI SEO Meta
                </button>
              </div>
              <input id="field-seo-title" name="seo_title" placeholder="SEO Title (50-60 characters)" value="<?php echo htmlspecialchars($editingPost['seo_title'] ?? ''); ?>">
            </div>

            <div class="field full">
              <label for="field-seo-desc">SEO Meta Description</label>
              <textarea id="field-seo-desc" name="seo_description" rows="2" placeholder="Search engine preview description (140-160 characters)"><?php echo htmlspecialchars($editingPost['seo_description'] ?? ''); ?></textarea>
            </div>

            <div class="field full" style="display: flex; align-items: center; gap: 10px; padding: 12px 0;">
              <input type="checkbox" id="allow_comments" name="allow_comments" <?php echo (!isset($editingPost['allow_comments']) || $editingPost['allow_comments']) ? 'checked' : ''; ?>>
              <label for="allow_comments" style="margin: 0; font-weight: 600; cursor: pointer;">
                Allow reader comments and discussions on this post
              </label>
            </div>
          </div>

          <div class="form-actions" style="margin-top: 24px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <button type="submit" class="btn primary" style="padding: 14px 28px; font-size: 0.92rem; font-weight: 800; letter-spacing: 0.04em;">
              <i class="fa-solid fa-floppy-disk"></i> <?php echo $editingPost ? 'Update Blog Post' : 'Save & Publish Post'; ?>
            </button>
            <?php if ($editingPost): ?>
              <a href="blog.php" class="btn" style="padding: 14px 20px;">Cancel Editing</a>
            <?php endif; ?>
          </div>

          <input type="hidden" name="action" value="save_blog">
          <?php if ($editingPost): ?>
            <input type="hidden" name="post_id" value="<?php echo (int)$editingPost['id']; ?>">
          <?php endif; ?>
        </form>
      </section>

      <!-- ═══════════════════════════════════════════════════════ -->
      <!-- ALL BLOG POSTS LIST                                     -->
      <!-- ═══════════════════════════════════════════════════════ -->
      <section class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--line);">
          <h2 style="margin: 0;"><i class="fa-solid fa-list-check" style="color: var(--primary);"></i> All Blog Posts (<?php echo count($posts); ?>)</h2>
          <a href="#post-editor-panel" class="btn small" style="background: var(--primary); color: #fff; border-color: var(--primary);">
            <i class="fa-solid fa-plus"></i> New Post
          </a>
        </div>

        <div class="list">
          <?php if (empty($posts)): ?>
            <div style="padding: 40px; text-align: center; color: var(--muted);">
              <i class="fa-regular fa-newspaper" style="font-size: 2.5rem; opacity: 0.3; margin-bottom: 12px; display: block;"></i>
              <p style="margin: 0; font-size: 1rem; font-weight: 600;">No blog posts yet. Write your first article above!</p>
            </div>
          <?php else: ?>
            <?php foreach ($posts as $post): ?>
              <div class="list-item">
                <div style="flex: 1;">
                  <strong style="font-size: 1.05rem; display: block; margin-bottom: 4px;">
                    <?php echo htmlspecialchars($post['title']); ?>
                  </strong>
                  <div style="font-size: 0.82rem; color: var(--muted); display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-bottom: 6px;">
                    <span style="text-transform: uppercase; font-weight: 700; color: <?php echo $post['status'] === 'published' ? 'var(--success)' : 'var(--muted)'; ?>;">
                      ● <?php echo htmlspecialchars($post['status']); ?>
                    </span>
                    <span>•</span>
                    <span><?php echo !empty($post['published_at']) ? date('M d, Y', strtotime($post['published_at'])) : 'Unpublished'; ?></span>
                    <?php if (!empty($post['reading_time'])): ?>
                      <span>•</span>
                      <span><i class="fa-regular fa-clock"></i> <?php echo (int)$post['reading_time']; ?> min read</span>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($post['excerpt'])): ?>
                    <p style="margin: 0; font-size: 0.88rem; color: var(--text-secondary); line-height: 1.4;">
                      <?php echo htmlspecialchars(substr($post['excerpt'], 0, 160)) . (strlen($post['excerpt']) > 160 ? '...' : ''); ?>
                    </p>
                  <?php endif; ?>
                </div>

                <div class="list-meta" style="align-items: center; gap: 8px;">
                  <span class="tag" style="background: var(--soft-bg); border: 1px solid var(--line); font-weight: 700;">
                    <?php echo htmlspecialchars($post['category'] ?? 'General'); ?>
                  </span>
                  
                  <a href="comments.php?post_id=<?php echo (int)$post['id']; ?>" class="tag" style="text-decoration: none; color: var(--text); border: 1px solid var(--line); background: var(--soft-bg);" title="View comments for this post">
                    <i class="fa-solid fa-comments" style="color: var(--primary);"></i> <?php echo (int)($post['total_comments'] ?? 0); ?>
                    <?php if (!empty($post['pending_comments'])): ?>
                      <span style="color: var(--warning); font-weight: 800;">(<?php echo (int)$post['pending_comments']; ?> pending)</span>
                    <?php endif; ?>
                  </a>

                  <a class="btn" href="?edit=<?php echo (int) $post['id']; ?>#post-editor-panel" title="Edit post"><i class="fa-solid fa-pen"></i> Edit</a>
                  <a class="btn danger" href="?delete=blog_posts&id=<?php echo (int) $post['id']; ?>" onclick="return confirm('Delete post &quot;<?php echo htmlspecialchars(addslashes($post['title'])); ?>&quot; permanently?');" title="Delete post"><i class="fa-solid fa-trash"></i></a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>
    </main>
  </div>

  <script>
    const statusDiv = document.getElementById('ai-status');

    function showStatus(message, isError = false, isSuccess = false) {
      statusDiv.style.display = 'block';
      statusDiv.innerHTML = message;
      if (isError) {
        statusDiv.style.background = '#fef2f2';
        statusDiv.style.borderColor = '#fecaca';
        statusDiv.style.color = '#991b1b';
      } else if (isSuccess) {
        statusDiv.style.background = '#ecfdf5';
        statusDiv.style.borderColor = '#a7f3d0';
        statusDiv.style.color = '#065f46';
      } else {
        statusDiv.style.background = 'rgba(234, 88, 12, 0.06)';
        statusDiv.style.borderColor = 'rgba(234, 88, 12, 0.25)';
        statusDiv.style.color = 'var(--primary)';
      }
    }

    function disableAllAI(disabled = true) {
      const heroBtn = document.getElementById('btn-master-ai');
      if (heroBtn) {
        heroBtn.disabled = disabled;
        heroBtn.style.opacity = disabled ? '0.6' : '1';
        heroBtn.style.cursor = disabled ? 'not-allowed' : 'pointer';
      }
      document.querySelectorAll('.btn-micro-ai').forEach(btn => {
        btn.disabled = disabled;
        btn.style.opacity = disabled ? '0.5' : '1';
      });
    }

    function highlightField(element) {
      if (!element) return;
      element.classList.remove('field-ai-highlight');
      void element.offsetWidth; // trigger reflow
      element.classList.add('field-ai-highlight');
    }

    function getEditorContent() {
      if (typeof tinymce !== 'undefined' && tinymce.get('blog-content-editor')) {
        return tinymce.get('blog-content-editor').getContent().trim();
      }
      const textarea = document.getElementById('blog-content-editor');
      return textarea ? textarea.value.trim() : '';
    }

    function updateFeaturedPreview(url) {
      const container = document.getElementById('featured-preview-container');
      if (!container) return;
      if (url) {
        let preview = document.getElementById('featured-preview');
        if (!preview) {
          preview = document.createElement('img');
          preview.id = 'featured-preview';
          preview.style.maxWidth = '100%';
          preview.style.maxHeight = '130px';
          preview.style.objectFit = 'cover';
          container.innerHTML = '';
          container.appendChild(preview);
        }
        preview.src = url + (url.includes('?') ? '&' : '?') + 'v=' + Date.now();
        preview.style.display = 'block';
        const placeholder = document.getElementById('featured-preview-placeholder');
        if (placeholder) placeholder.remove();
      } else {
        container.innerHTML = '<small id="featured-preview-placeholder" style="color: var(--muted); text-align: center;"><i class="fa-regular fa-image" style="font-size: 1.6rem; opacity: 0.4; display: block; margin-bottom: 4px;"></i>No image selected</small>';
      }
    }

    // Featured Image Upload Handler
    document.getElementById('featured-image-upload').addEventListener('change', async function(e) {
      const file = e.target.files[0];
      if (!file) return;

      if (file.size > 5 * 1024 * 1024) {
        alert('File is too large. Maximum size is 5MB.');
        return;
      }

      const formData = new FormData();
      formData.append('file', file);

      try {
        const response = await fetch('api_upload_image.php', {
          method: 'POST',
          body: formData
        });

        const data = await response.json();
        if (data.success && data.location) {
          document.getElementById('featured_image_url').value = data.location;
          updateFeaturedPreview(data.location);
          showStatus('<i class="fa-solid fa-circle-check"></i> Featured image uploaded successfully!', false, true);
        } else {
          alert('Upload failed: ' + (data.error || 'Unknown error'));
        }
      } catch (err) {
        alert('Upload error: ' + err.message);
      }
    });

    document.getElementById('featured_image_url').addEventListener('input', function(e) {
      updateFeaturedPreview(e.target.value.trim());
    });
    document.getElementById('featured_image_url').addEventListener('change', function(e) {
      updateFeaturedPreview(e.target.value.trim());
    });

    // ═══════════════════════════════════════════════════════════
    // CORE AI API COMMUNICATION
    // ═══════════════════════════════════════════════════════════
    async function executeAI(action) {
      const content = getEditorContent();
      const title = document.getElementById('field-title').value.trim();
      const category = document.getElementById('field-category').value.trim();

      if (!content || content.replace(/<[^>]*>/g, '').trim().length < 15) {
        showStatus('<i class="fa-solid fa-circle-info"></i> Please write or paste your article content into the <strong>Blog Content</strong> editor first.', true);
        const editorBox = document.getElementById('blog-content-editor');
        if (editorBox) editorBox.focus();
        return null;
      }

      disableAllAI(true);
      showStatus('<i class="fa-solid fa-spinner fa-spin"></i> AI is analyzing content and generating metadata...');

      try {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('content', content);
        formData.append('title', title);
        formData.append('category', category);

        const response = await fetch('api_blog_ai.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (!result.success) {
          showStatus('<i class="fa-solid fa-triangle-exclamation"></i> AI Error: ' + (result.error || 'Failed to process request'), true);
          disableAllAI(false);
          return null;
        }

        return result.data;
      } catch (error) {
        showStatus('<i class="fa-solid fa-triangle-exclamation"></i> Network error: ' + error.message, true);
        disableAllAI(false);
        return null;
      }
    }

    // ═══════════════════════════════════════════════════════════
    // 1-CLICK FULL AUTO-FILL FROM CONTENT
    // ═══════════════════════════════════════════════════════════
    async function autoGenerateAllFromContent() {
      showStatus('<i class="fa-solid fa-wand-magic-sparkles fa-spin"></i> Analyzing article content & generating Title, Abstract, Category, Tags, Slug, and SEO metadata...');
      
      const data = await executeAI('auto_generate_all');
      if (data) {
        const titleField = document.getElementById('field-title');
        const slugField = document.getElementById('field-slug');
        const catField = document.getElementById('field-category');
        const tagsField = document.getElementById('field-tags');
        const excerptField = document.getElementById('field-excerpt');
        const readTimeField = document.getElementById('field-reading-time');
        const seoTitleField = document.getElementById('field-seo-title');
        const seoDescField = document.getElementById('field-seo-desc');

        if (data.title) { titleField.value = data.title; highlightField(titleField); }
        if (data.slug) { slugField.value = data.slug; highlightField(slugField); }
        if (data.category) { catField.value = data.category; highlightField(catField); }
        if (data.tags) { tagsField.value = data.tags; highlightField(tagsField); }
        if (data.excerpt) { excerptField.value = data.excerpt; highlightField(excerptField); }
        if (data.reading_time) { readTimeField.value = data.reading_time; highlightField(readTimeField); }
        if (data.seo_title) { seoTitleField.value = data.seo_title; highlightField(seoTitleField); }
        if (data.seo_description) { seoDescField.value = data.seo_description; highlightField(seoDescField); }

        // Highlight Featured Image guide
        const guideBox = document.getElementById('featured-guide-box');
        if (guideBox) {
          guideBox.classList.add('highlighted');
          setTimeout(() => guideBox.classList.remove('highlighted'), 3500);
        }

        showStatus('<strong><i class="fa-solid fa-circle-check"></i> ✨ Complete AI Auto-Fill Finished!</strong> Title, Abstract, Category, Tags, Reading Time, and SEO metadata generated. <em>Now upload or select your Featured Image below and save your post.</em>', false, true);

        // Smoothly scroll to the Featured Image section
        const featuredSec = document.getElementById('featured-image-section');
        if (featuredSec) {
          featuredSec.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }
      disableAllAI(false);
    }

    // ═══════════════════════════════════════════════════════════
    // INDIVIDUAL AI ACTIONS
    // ═══════════════════════════════════════════════════════════
    async function generateTitle() {
      const data = await executeAI('generate_title');
      if (data && data.title) {
        const titleField = document.getElementById('field-title');
        const slugField = document.getElementById('field-slug');
        titleField.value = data.title;
        if (data.slug && !slugField.value) {
          slugField.value = data.slug;
        }
        highlightField(titleField);
        highlightField(slugField);
        showStatus('<i class="fa-solid fa-circle-check"></i> Title and slug generated from content!', false, true);
      }
      disableAllAI(false);
    }

    async function generateCategory() {
      const data = await executeAI('generate_category');
      if (data && data.category) {
        const catField = document.getElementById('field-category');
        catField.value = data.category;
        highlightField(catField);
        showStatus('<i class="fa-solid fa-circle-check"></i> Category generated from content!', false, true);
      }
      disableAllAI(false);
    }

    async function generateExcerpt() {
      const data = await executeAI('generate_excerpt');
      if (data && data.excerpt) {
        const excerptField = document.getElementById('field-excerpt');
        excerptField.value = data.excerpt;
        highlightField(excerptField);
        showStatus('<i class="fa-solid fa-circle-check"></i> Abstract / excerpt generated from content!', false, true);
      }
      disableAllAI(false);
    }

    async function generateTags() {
      const data = await executeAI('generate_tags');
      if (data && data.tags) {
        const tagsField = document.getElementById('field-tags');
        tagsField.value = data.tags;
        highlightField(tagsField);
        showStatus('<i class="fa-solid fa-circle-check"></i> Tags generated from content!', false, true);
      }
      disableAllAI(false);
    }

    async function generateSEOMeta() {
      const data = await executeAI('auto_generate_all');
      if (data) {
        const seoTitleField = document.getElementById('field-seo-title');
        const seoDescField = document.getElementById('field-seo-desc');
        if (data.seo_title) { seoTitleField.value = data.seo_title; highlightField(seoTitleField); }
        if (data.seo_description) { seoDescField.value = data.seo_description; highlightField(seoDescField); }
        showStatus('<i class="fa-solid fa-circle-check"></i> SEO title and meta description generated!', false, true);
      }
      disableAllAI(false);
    }

    async function analyzeContent() {
      const data = await executeAI('analyze_content');
      if (data) {
        const readTimeField = document.getElementById('field-reading-time');
        const readingTime = data.reading_time || 1;
        readTimeField.value = readingTime;
        highlightField(readTimeField);
        showStatus(`<i class="fa-solid fa-chart-simple"></i> Content Stats: <strong>${data.word_count || 0} words</strong>, ~${readingTime} min read, ${data.paragraph_count || 1} paragraphs.`, false, true);
      }
      disableAllAI(false);
    }

    // Initialize TinyMCE Rich Text Editor with Outfit Font
    tinymce.init({
      selector: '#blog-content-editor',
      plugins: 'image link lists table code preview fullscreen autolink media',
      toolbar: 'fontfamily fontsize | formatselect | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | table | code preview fullscreen',
      menubar: 'edit view insert format table tools',
      font_family_formats: 'Outfit=Outfit, sans-serif; Arial=arial,helvetica,sans-serif; Georgia=georgia,garamond,serif; Courier New=courier new,courier,monospace;',
      font_size_formats: '10px 12px 14px 15px 16px 18px 20px 22px 24px 28px 32px 36px 48px',
      height: 480,
      branding: false,
      content_css: [
        'https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap'
      ],
      mobile: {
        menubar: true,
        toolbar: 'formatselect | bold italic underline | bullist numlist | link image'
      },
      file_picker_types: 'image',
      file_picker_callback: function(callback, value, meta) {
        if (meta.filetype === 'image') {
          const input = document.createElement('input');
          input.setAttribute('type', 'file');
          input.setAttribute('accept', 'image/*');
          input.onchange = function() {
            const file = this.files[0];
            if (file.size > 5 * 1024 * 1024) {
              alert('File too large. Maximum 5MB allowed.');
              return;
            }
            
            const formData = new FormData();
            formData.append('file', file);
            
            fetch('api_upload_image.php', {
              method: 'POST',
              body: formData
            })
            .then(res => res.json())
            .then(data => {
              if (data.success && data.location) {
                callback(data.location, { title: file.name });
              } else {
                alert('Upload failed: ' + (data.error || 'Unknown error'));
              }
            })
            .catch(err => alert('Upload error: ' + err.message));
          };
          input.click();
        }
      },
      content_style: `
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
        
        * {
          font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
          box-sizing: border-box;
        }
        
        html, body { 
          font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; 
          font-size: 15.5px; 
          line-height: 1.75; 
          color: #0f172a;
          padding: 16px;
          background: #ffffff;
        }
        
        h1, h2, h3, h4, h5, h6 { 
          font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; 
          font-weight: 800; 
          color: #0f172a; 
          margin-top: 1.5em; 
          margin-bottom: 0.5em;
          line-height: 1.3;
        }
        
        p, li, blockquote, table, td, th, div, span, strong, em, a { 
          font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; 
        }
        
        p { 
          margin-bottom: 1.3em; 
          font-size: 15.5px;
          line-height: 1.75;
          color: #1e293b;
        }
        
        blockquote {
          border-left: 4px solid #ea580c;
          background: rgba(234, 88, 12, 0.04);
          padding: 14px 18px;
          margin: 1.5em 0;
          font-style: italic;
          color: #334155;
        }
        
        ul, ol {
          padding-left: 24px;
          margin-bottom: 1.3em;
        }
        
        li {
          margin-bottom: 0.5em;
          line-height: 1.7;
        }
        
        img { 
          max-width: 100%; 
          height: auto; 
          margin: 1.2em 0;
        }
        
        code { 
          font-family: 'Courier New', Courier, monospace !important; 
          background: #f1f5f9; 
          padding: 2px 6px; 
          font-size: 0.9em; 
          color: #0f172a;
        }
        
        pre {
          font-family: 'Courier New', Courier, monospace !important; 
          background: #0f172a;
          color: #f8fafc;
          padding: 16px;
          overflow-x: auto;
          line-height: 1.5;
          margin: 1.5em 0;
        }
        pre code {
          background: transparent !important;
          color: inherit !important;
          padding: 0 !important;
        }
      `,
      setup: function(editor) {
        editor.on('init change input keyup blur NodeChange SetContent', function() {
          tinymce.triggerSave();
        });
      }
    });

    // Explicit Form Submission Validation & Sync
    document.getElementById('blog-form').addEventListener('submit', function(e) {
      if (typeof tinymce !== 'undefined') {
        tinymce.triggerSave();
      }
      
      const title = document.getElementById('field-title').value.trim();
      const content = document.getElementById('blog-content-editor').value.trim();

      if (!title) {
        e.preventDefault();
        alert('Please enter a title for the blog post.');
        document.getElementById('field-title').focus();
        return false;
      }

      if (!content || content.replace(/<[^>]*>/g, '').trim().length === 0) {
        e.preventDefault();
        alert('Please write or paste your article content before saving.');
        if (typeof tinymce !== 'undefined' && tinymce.get('blog-content-editor')) {
          tinymce.get('blog-content-editor').focus();
        }
        return false;
      }
    });
  </script>
</body>
</html>
