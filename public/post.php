<?php
$activePage = 'blog';
require_once __DIR__ . '/../config/config.php';

// Get post slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: blog.php');
    exit;
}

// Fetch the blog post by slug
$stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE slug = ? AND status = ?');
$stmt->execute([$slug, 'published']);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    http_response_code(404);
    $activePage = 'blog';
    require_once __DIR__ . '/header.php';
    echo '<main class="container"><section class="section-row"><div class="section-content"><h2>Post Not Found</h2><p>Sorry, this blog post does not exist.</p><p><a href="blog.php">← Back to Blog</a></p></div></section></main>';
    require_once __DIR__ . '/footer.php';
    exit;
}

// Increment views counter
$pdo->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = ?')->execute([$post['id']]);

require_once __DIR__ . '/header.php';
?>

<main class="container">
  <section id="blog-post" class="section-row" style="border-bottom: none;">
    <div class="section-label">Blog</div>
    <div class="section-content">
      <!-- Featured Image -->
      <?php if (!empty($post['featured_image'])): ?>
        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 100%; height: 400px; object-fit: cover; margin-bottom: 32px; display: block;">
      <?php endif; ?>

      <!-- Post Header -->
      <div style="margin-bottom: 32px;">
        <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
          <div style="color: var(--accent); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;">
            <?php echo !empty($post['published_at']) ? date('M d, Y', strtotime($post['published_at'])) : 'Unpublished'; ?>
          </div>
          <?php if (!empty($post['category'])): ?>
            <div style="color: var(--muted); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;">
              <?php echo htmlspecialchars($post['category']); ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($post['reading_time'])): ?>
            <div style="color: var(--muted); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;">
              <?php echo (int)$post['reading_time']; ?> min read
            </div>
          <?php endif; ?>
        </div>
        <h1 style="margin: 0 0 20px 0; font-size: 2rem; line-height: 1.2;">
          <?php echo htmlspecialchars($post['title']); ?>
        </h1>
        <div style="display: flex; gap: 16px; align-items: center; padding-bottom: 24px; border-bottom: 1px solid var(--light-gray);">
          <div>
            <div style="font-weight: 600; color: var(--text);">
              <?php echo htmlspecialchars($post['author_name']); ?>
            </div>
            <div style="font-size: 0.85rem; color: var(--muted);">
              <?php echo (int)($post['views'] ?? 0); ?> views
            </div>
          </div>
        </div>
      </div>

      <!-- Post Content -->
      <article style="max-width: 100%; margin-bottom: 40px; line-height: 1.8; color: var(--text);">
        <div style="font-size: 1rem;">
          <?php 
          // Display HTML content from rich editor
          echo $post['content']; 
          ?>
        </div>
      </article>

      <!-- Tags -->
      <?php if (!empty($post['tags'])): ?>
        <div style="padding: 20px; background: var(--soft-bg); border: 1px solid var(--light-gray); margin-bottom: 32px;">
          <div style="font-weight: 600; margin-bottom: 12px; color: var(--text);">Tags:</div>
          <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <?php foreach (array_filter(array_map('trim', explode(',', $post['tags']))) as $tag): ?>
              <span style="font-size: 0.85rem; color: var(--accent); background: rgba(234, 88, 12, 0.08); padding: 6px 12px; border: 1px solid rgba(234, 88, 12, 0.2);">
                #<?php echo htmlspecialchars($tag); ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Back Button -->
      <div style="padding-top: 20px; border-top: 1px solid var(--light-gray);">
        <a href="blog.php" style="color: var(--accent); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
          ← Back to Blog
        </a>
      </div>

      <!-- Related Posts (Optional - Shows 3 recent posts) -->
      <?php
      $relatedPosts = $pdo->prepare("
        SELECT * FROM blog_posts 
        WHERE status = 'published' AND id != ? 
        ORDER BY published_at DESC 
        LIMIT 3
      ");
      $relatedPosts->execute([$post['id']]);
      $relatedPostsData = $relatedPosts->fetchAll(PDO::FETCH_ASSOC);
      
      if (!empty($relatedPostsData)):
      ?>
        <section style="margin-top: 60px; padding-top: 40px; border-top: 1px solid var(--light-gray);">
          <h3 style="margin-bottom: 24px;">More Articles</h3>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <?php foreach ($relatedPostsData as $relatedPost): ?>
              <article style="padding: 20px; background: var(--soft-bg); border: 1px solid var(--light-gray);">
                <?php if (!empty($relatedPost['featured_image'])): ?>
                  <img src="<?php echo htmlspecialchars($relatedPost['featured_image']); ?>" alt="<?php echo htmlspecialchars($relatedPost['title']); ?>" style="width: 100%; height: 150px; object-fit: cover; margin: -20px -20px 12px -20px; display: block;">
                <?php endif; ?>
                <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                  <div style="font-size: 0.75rem; color: var(--muted); font-weight: 700; text-transform: uppercase;">
                    <?php echo !empty($relatedPost['published_at']) ? date('M d', strtotime($relatedPost['published_at'])) : ''; ?>
                  </div>
                  <?php if (!empty($relatedPost['reading_time'])): ?>
                    <div style="font-size: 0.75rem; color: var(--muted); font-weight: 700; text-transform: uppercase;">
                      <?php echo (int)$relatedPost['reading_time']; ?> min
                    </div>
                  <?php endif; ?>
                </div>
                <h4 style="margin: 0 0 8px 0; line-height: 1.3;">
                  <a href="post.php?slug=<?php echo urlencode($relatedPost['slug']); ?>" style="color: var(--text); text-decoration: none;">
                    <?php echo htmlspecialchars($relatedPost['title']); ?>
                  </a>
                </h4>
                <?php if (!empty($relatedPost['excerpt'])): ?>
                  <p style="margin: 0; font-size: 0.9rem; color: var(--muted); line-height: 1.4;">
                    <?php echo substr(htmlspecialchars($relatedPost['excerpt']), 0, 100) . '...'; ?>
                  </p>
                <?php endif; ?>
                <a href="post.php?slug=<?php echo urlencode($relatedPost['slug']); ?>" style="margin-top: 12px; display: inline-block; color: var(--accent); text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                  Read more →
                </a>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>
