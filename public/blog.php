<?php
$activePage = 'blog';
require_once __DIR__ . '/header.php';

// Fetch published blog posts
$blogPosts = $pdo->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container">
  <section id="blog" class="section-row" style="border-bottom:none;">
    <div class="section-label">Blog</div>
    <div class="section-content">
      <h2>Writing & Insights</h2>
      <div class="blog-list">
        <?php if (!empty($blogPosts)): ?>
          <?php foreach ($blogPosts as $post): ?>
            <article class="blog-post" style="padding: 24px; background: var(--soft-bg); border: 1px solid var(--light-gray); margin-bottom: 20px;">
              <?php if (!empty($post['featured_image'])): ?>
                <img src="<?php echo htmlspecialchars(portfolioAssetUrl($post['featured_image'])); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="width: 100%; height: 240px; object-fit: cover; margin: -24px -24px 16px -24px; display: block;">
              <?php endif; ?>
              <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                <div style="flex: 1;">
                  <div style="display: flex; gap: 12px; margin-bottom: 12px; flex-wrap: wrap;">
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
                  <h3 style="margin: 0 0 12px 0; line-height: 1.2;"><?php echo htmlspecialchars($post['title']); ?></h3>
                  <?php if (!empty($post['excerpt'])): ?>
                    <p style="margin: 0 0 12px 0; color: var(--muted); line-height: 1.45;"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                  <?php endif; ?>
                  <?php if (!empty($post['tags'])): ?>
                    <div style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">
                      <?php foreach (array_filter(array_map('trim', explode(',', $post['tags']))) as $tag): ?>
                        <span style="font-size: 0.8rem; color: var(--accent); background: rgba(234, 88, 12, 0.08); padding: 4px 8px; border: 1px solid rgba(234, 88, 12, 0.2);">#<?php echo htmlspecialchars($tag); ?></span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
                <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" style="flex-shrink: 0; white-space: nowrap; padding: 10px 14px; border: 1px solid var(--text); background: transparent; color: var(--text); font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: all 0.2s ease;">
                  Read Article →
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="text-align: center; color: var(--muted); padding: 40px 0;">No published blog posts yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>
