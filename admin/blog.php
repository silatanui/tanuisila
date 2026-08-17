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

$posts = $pdo->query('SELECT * FROM blog_posts ORDER BY created_at DESC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Blog Posts — Admin</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
  <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js"></script>
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('blog.php'); ?>
    <main class="main-panel">
      <?php echo renderTopbar('Blog Posts'); ?>

      <?php if (!empty($notice)): ?><div class="notice"><?php echo htmlspecialchars($notice); ?></div><?php endif; ?>
      <?php if (!empty($error)): ?><div class="error-box"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <section class="panel">
        <h2><?php echo $editingPost ? '✏️ Edit Blog Post' : '📝 Create Blog Post'; ?></h2>
        <?php if ($editingPost): ?><div style="background: rgba(15, 118, 110, 0.08); border: 1px solid rgba(15, 118, 110, 0.2); padding: 12px; margin-bottom: 16px; font-size: 0.9rem; color: var(--text);">Editing: <strong><?php echo htmlspecialchars($editingPost['title']); ?></strong> (ID: <?php echo (int)$editingPost['id']; ?>)</div><?php endif; ?>
        <form method="post">
          <div class="grid-form">
            <div class="field full"><label>Title *</label><input name="title" required value="<?php echo htmlspecialchars($editingPost['title'] ?? ''); ?>"></div>
            <div class="field"><label>Slug</label><input name="slug" placeholder="Auto-generated if empty" value="<?php echo htmlspecialchars($editingPost['slug'] ?? ''); ?>"></div>
            <div class="field"><label>Category</label><input name="category" placeholder="e.g., Technology, Research" value="<?php echo htmlspecialchars($editingPost['category'] ?? ''); ?>"></div>
            <div class="field"><label>Status</label><select name="status"><option value="draft" <?php echo ($editingPost['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option><option value="published" <?php echo ($editingPost['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option><option value="archived" <?php echo ($editingPost['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option></select></div>
            <div class="field full">
              <label>Featured Image</label>
              <div style="display: grid; grid-template-columns: 1fr 200px; gap: 16px; align-items: start;">
                <div>
                  <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                    <input type="file" id="featured-image-upload" accept="image/*" style="flex: 1;">
                    <button type="button" class="btn" onclick="document.getElementById('featured-image-upload').click()">
                      <i class="fa-solid fa-cloud-arrow-up"></i> Upload
                    </button>
                  </div>
                  <small style="color: var(--muted);">Or paste an image URL:</small>
                  <input type="text" name="featured_image" id="featured_image_url" placeholder="https://example.com/image.jpg" value="<?php echo htmlspecialchars($editingPost['featured_image'] ?? ''); ?>" style="width: 100%; margin-top: 4px;">
                  <small style="display: block; color: var(--muted); margin-top: 4px;">Max 5MB. Formats: JPG, PNG, GIF, WebP</small>
                </div>
                <div id="featured-preview-container" style="border: 1px solid var(--light-gray); background: var(--soft-bg); padding: 8px; text-align: center; min-height: 150px; display: flex; align-items: center; justify-content: center;">
                  <?php if (!empty($editingPost['featured_image'])): ?>
                    <img id="featured-preview" src="<?php echo htmlspecialchars($editingPost['featured_image']); ?>?v=<?php echo time(); ?>" style="max-width: 100%; max-height: 150px; object-fit: cover;">
                  <?php else: ?>
                    <small style="color: var(--muted); text-align: center;">No image selected</small>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="field"><label>Reading Time (minutes)</label><input type="number" name="reading_time" min="1" placeholder="e.g., 5" value="<?php echo htmlspecialchars($editingPost['reading_time'] ?? ''); ?>"></div>
            <div class="field"><label>Published Date</label><input type="datetime-local" name="published_at" value="<?php echo !empty($editingPost['published_at']) ? date('Y-m-d\TH:i', strtotime($editingPost['published_at'])) : ''; ?>"></div>
            <div class="field full"><label>Tags</label><input name="tags" placeholder="Comma-separated tags, e.g.: AI, Python, Development" value="<?php echo htmlspecialchars($editingPost['tags'] ?? ''); ?>"></div>
            <div class="field full"><label>Excerpt</label><textarea name="excerpt" placeholder="Brief summary of the post"><?php echo htmlspecialchars($editingPost['excerpt'] ?? ''); ?></textarea></div>
            <div class="field full"><label>Content *</label><textarea id="blog-content-editor" name="content" required><?php echo htmlspecialchars($editingPost['content'] ?? ''); ?></textarea></div>
            <div class="field full"><label>Author Name</label><input name="author_name" value="<?php echo htmlspecialchars($editingPost['author_name'] ?? 'Tanui Kipng\'etich Sila'); ?>" placeholder="Author name"></div>
            <div class="field full"><label>SEO Title</label><input name="seo_title" placeholder="Custom SEO title (optional)" value="<?php echo htmlspecialchars($editingPost['seo_title'] ?? ''); ?>"></div>
            <div class="field full"><label>SEO Description</label><textarea name="seo_description" placeholder="Meta description for search engines (max 500 chars)"><?php echo htmlspecialchars($editingPost['seo_description'] ?? ''); ?></textarea></div>
            <div class="field full" style="display: flex; align-items: center; gap: 10px;">
              <input type="checkbox" id="allow_comments" name="allow_comments" <?php echo (!isset($editingPost['allow_comments']) || $editingPost['allow_comments']) ? 'checked' : ''; ?>>
              <label for="allow_comments" style="margin: 0;">Allow comments on this post</label>
            </div>
          </div>

          <!-- AI Automation Section -->
          <div style="background: rgba(234, 88, 12, 0.04); border: 1px solid rgba(234, 88, 12, 0.2); padding: 16px; margin: 20px 0; border-radius: 0;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
              <i class="fa-solid fa-wand-magic-sparkles" style="color: var(--primary);"></i>
              <strong style="color: var(--primary);">AI Blog Automation</strong>
              <span style="font-size: 0.8rem; color: var(--muted);"> — Powered by GPT</span>
            </div>
            <p style="margin: 0 0 12px 0; font-size: 0.9rem; color: var(--muted);">Automatically generate SEO-optimized content, excerpts, tags, and analyze readability.</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px;">
              <button type="button" class="btn" onclick="generateExcerpt()" id="btn-excerpt">
                <i class="fa-solid fa-lightbulb"></i> Generate Excerpt
              </button>
              <button type="button" class="btn" onclick="generateSEOTitle()" id="btn-seo-title">
                <i class="fa-solid fa-search"></i> SEO Title
              </button>
              <button type="button" class="btn" onclick="generateSEODescription()" id="btn-seo-desc">
                <i class="fa-solid fa-file-text"></i> SEO Description
              </button>
              <button type="button" class="btn" onclick="generateTags()" id="btn-tags">
                <i class="fa-solid fa-tag"></i> Generate Tags
              </button>
              <button type="button" class="btn" onclick="analyzeContent()" id="btn-analyze">
                <i class="fa-solid fa-chart-bar"></i> Analyze Content
              </button>
              <button type="button" class="btn" style="background: var(--primary); color: white; border-color: var(--primary);" onclick="fullOptimization()" id="btn-full-opt">
                <i class="fa-solid fa-magic"></i> Full Optimization
              </button>
            </div>
            <div id="ai-status" style="margin-top: 12px; font-size: 0.85rem; color: var(--muted); display: none;"></div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn primary"><?php echo $editingPost ? 'Update Post' : 'Create Post'; ?></button>
            <?php if ($editingPost): ?><a href="blog.php" class="btn">Cancel</a><?php endif; ?>
          </div>
          <input type="hidden" name="action" value="save_blog">
          <?php if ($editingPost): ?><input type="hidden" name="post_id" value="<?php echo (int)$editingPost['id']; ?>"><?php endif; ?>
        </form>
      </section>

      <section class="panel">
        <h2>All Blog Posts</h2>
        <div class="list">
          <?php if (empty($posts)): ?>
            <p style="text-align: center; color: var(--muted); padding: 20px;">No blog posts yet. Create your first post above!</p>
          <?php else: ?>
            <?php foreach ($posts as $post): ?>
              <div class="list-item">
                <div>
                  <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                  <small><?php echo htmlspecialchars($post['status']); ?> • <?php echo !empty($post['published_at']) ? date('M d, Y', strtotime($post['published_at'])) : 'Unpublished'; ?></small>
                  <?php if (!empty($post['excerpt'])): ?><small><?php echo substr(htmlspecialchars($post['excerpt']), 0, 100) . '...'; ?></small><?php endif; ?>
                </div>
                <div class="list-meta">
                  <span class="tag"><?php echo htmlspecialchars($post['category'] ?? 'Uncategorized'); ?></span>
                  <?php if (!empty($post['reading_time'])): ?><span class="tag"><?php echo (int)$post['reading_time']; ?> min read</span><?php endif; ?>
                  <a class="btn" href="?edit=<?php echo (int) $post['id']; ?>"><i class="fa-solid fa-pen"></i> Edit</a>
                  <a class="btn danger" href="?delete=blog_posts&id=<?php echo (int) $post['id']; ?>" onclick="return confirm('Delete this post?');"><i class="fa-solid fa-trash"></i> Delete</a>
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

    function showStatus(message, isError = false) {
      statusDiv.style.display = 'block';
      statusDiv.textContent = message;
      statusDiv.style.color = isError ? '#b42318' : '#0f766e';
    }

    function disableAIButtons(disabled = true) {
      const buttons = ['btn-excerpt', 'btn-seo-title', 'btn-seo-desc', 'btn-tags', 'btn-analyze', 'btn-full-opt'];
      buttons.forEach(id => {
        const btn = document.getElementById(id);
        btn.disabled = disabled;
        btn.style.opacity = disabled ? '0.6' : '1';
      });
    }

    // Featured Image Upload Handler
    document.getElementById('featured-image-upload').addEventListener('change', async function(e) {
      const file = e.target.files[0];
      if (!file) return;

      if (file.size > 5 * 1024 * 1024) {
        alert('File too large. Maximum 5MB allowed.');
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
          // Update the URL field
          document.getElementById('featured_image_url').value = data.location;
          // Update preview
          const preview = document.getElementById('featured-preview');
          preview.src = data.location + '?v=' + Date.now();
          preview.style.display = 'block';
          document.querySelector('#featured-preview-container small')?.remove();
          alert('Featured image uploaded successfully!');
        } else {
          alert('Upload failed: ' + (data.error || 'Unknown error'));
        }
      } catch (err) {
        alert('Upload error: ' + err.message);
      }
    });

    // Update featured image preview when URL changes
    document.getElementById('featured_image_url').addEventListener('change', function(e) {
      const url = e.target.value.trim();
      const container = document.getElementById('featured-preview-container');
      if (url) {
        let preview = document.getElementById('featured-preview');
        if (!preview) {
          preview = document.createElement('img');
          preview.id = 'featured-preview';
          preview.style.maxWidth = '100%';
          preview.style.maxHeight = '150px';
          preview.style.objectFit = 'cover';
          container.innerHTML = '';
          container.appendChild(preview);
        }
        preview.src = url + '?v=' + Date.now();
      }
    });


    async function callAI(action) {
      const title = document.querySelector('input[name="title"]').value.trim();
      // Get content from TinyMCE editor
      const content = tinymce.get('blog-content-editor') ? 
        tinymce.get('blog-content-editor').getContent({format: 'text'}).trim() : 
        document.querySelector('textarea[name="content"]').value.trim();
      const category = document.querySelector('input[name="category"]').value.trim();

      if (!title) {
        showStatus('Please enter a title first', true);
        return;
      }
      if (!content) {
        showStatus('Please enter content first', true);
        return;
      }

      disableAIButtons(true);
      showStatus('⏳ Processing with AI...');

      try {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('title', title);
        formData.append('content', content);
        formData.append('category', category);

        const response = await fetch('api_blog_ai.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (!result.success) {
          showStatus('Error: ' + result.error, true);
          disableAIButtons(false);
          return;
        }

        return result.data;
      } catch (error) {
        showStatus('Network error: ' + error.message, true);
        disableAIButtons(false);
        return null;
      }
    }

    async function generateExcerpt() {
      const data = await callAI('generate_excerpt');
      if (data && data.excerpt) {
        document.querySelector('textarea[name="excerpt"]').value = data.excerpt;
        showStatus('✓ Excerpt generated successfully');
      }
      disableAIButtons(false);
    }

    async function generateSEOTitle() {
      const data = await callAI('generate_seo_title');
      if (data && data.seo_title) {
        document.querySelector('input[name="seo_title"]').value = data.seo_title;
        showStatus('✓ SEO title generated successfully');
      }
      disableAIButtons(false);
    }

    async function generateSEODescription() {
      const data = await callAI('generate_seo_description');
      if (data && data.seo_description) {
        document.querySelector('textarea[name="seo_description"]').value = data.seo_description;
        showStatus('✓ SEO description generated successfully');
      }
      disableAIButtons(false);
    }

    async function generateTags() {
      const data = await callAI('generate_tags');
      if (data && data.tags) {
        document.querySelector('input[name="tags"]').value = data.tags;
        showStatus('✓ Tags generated successfully');
      }
      disableAIButtons(false);
    }

    async function analyzeContent() {
      const data = await callAI('analyze_content');
      if (data && data.analysis) {
        const analysis = data.analysis;
        const readingTime = analysis.reading_time || 1;
        document.querySelector('input[name="reading_time"]').value = readingTime;
        showStatus(`✓ Content analyzed: ${analysis.word_count} words, ${readingTime} min read`);
      }
      disableAIButtons(false);
    }

    async function fullOptimization() {
      const data = await callAI('full_optimization');
      if (data) {
        if (data.excerpt) document.querySelector('textarea[name="excerpt"]').value = data.excerpt;
        if (data.seo_title) document.querySelector('input[name="seo_title"]').value = data.seo_title;
        if (data.seo_description) document.querySelector('textarea[name="seo_description"]').value = data.seo_description;
        if (data.tags) document.querySelector('input[name="tags"]').value = data.tags;
        if (data.analysis && data.analysis.reading_time) {
          document.querySelector('input[name="reading_time"]').value = data.analysis.reading_time;
        }
        showStatus('✓ Full optimization complete! Review all fields and save.');
      }
      disableAIButtons(false);
    }

    // Initialize TinyMCE Rich Text Editor
    tinymce.init({
      selector: '#blog-content-editor',
      plugins: 'image link lists table code preview fullscreen',
      toolbar: 'formatselect | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | table | code preview fullscreen',
      menubar: 'edit view insert format table tools',
      font_size_formats: '8px 10px 12px 14px 16px 18px 20px 22px 24px 28px 32px 36px',
      font_formats: 'Outfit=Outfit, sans-serif; Arial=arial,helvetica,sans-serif; Georgia=georgia,garamond,serif; Courier New=courier new,courier,monospace; AkrutiSans=Akruti Sans',
      height: 400,
      branding: false,
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
        body { 
          font-family: Outfit, sans-serif; 
          font-size: 14px; 
          line-height: 1.6; 
          color: #0f172a;
        }
        img { max-width: 100%; height: auto; }
      `,
      // Update content in textarea when editor changes
      setup: function(editor) {
        editor.on('change', function() {
          tinymce.triggerSave();
        });
      }
    });
  </script>
</body>
</html>
