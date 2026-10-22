<?php
$activePage = 'blog';
require_once __DIR__ . '/header.php';

// Fetch published blog posts with comment counts
$blogPosts = $pdo->query("
    SELECT p.*, 
           (SELECT COUNT(*) FROM blog_comments c WHERE c.post_id = p.id AND c.status = 'approved') AS comments_count 
    FROM blog_posts p 
    WHERE p.status = 'published' 
    ORDER BY p.published_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="container">
  <section id="blog" class="section-row" style="border-bottom:none;">
    <div class="section-label reveal-on-enter">Blog</div>
    <div class="section-content">
      <h2 class="reveal-on-enter">Writing &amp; Insights</h2>
      <div class="blog-list">
        <?php if (!empty($blogPosts)): ?>
          <?php foreach ($blogPosts as $index => $post): ?>
            <?php
              $cCount = (int)($post['comments_count'] ?? 0);
              $isFeatured = $index === 0;
            ?>
            <article class="blog-post reveal-on-enter<?php echo $isFeatured ? ' blog-post--featured' : ''; ?>">
              <?php if (!empty($post['featured_image'])): ?>
                <div class="img-skeleton-wrap blog-post__media">
                  <img 
                    src="<?php echo htmlspecialchars(portfolioAssetUrl($post['featured_image'])); ?>" 
                    alt="<?php echo htmlspecialchars($post['title']); ?>" 
                    loading="<?php echo $isFeatured ? 'eager' : 'lazy'; ?>"
                    decoding="async"
                    style="width: 100%; height: 100%; object-fit: cover; display: block;"
                    onload="this.closest('.img-skeleton-wrap').classList.add('loaded')"
                  >
                </div>
              <?php endif; ?>
              
              <div class="blog-post__row">
                <div class="blog-post__body">
                  <div class="blog-post__meta">
                    <?php if ($isFeatured): ?>
                      <div class="blog-post__meta-item blog-post__meta-item--date">Featured</div>
                    <?php endif; ?>
                    <div class="blog-post__meta-item<?php echo $isFeatured ? '' : ' blog-post__meta-item--date'; ?>">
                      <?php echo !empty($post['published_at']) ? date('M d, Y', strtotime($post['published_at'])) : 'Unpublished'; ?>
                    </div>
                    <?php if (!empty($post['category'])): ?>
                      <div class="blog-post__meta-item">
                        • <?php echo htmlspecialchars($post['category']); ?>
                      </div>
                    <?php endif; ?>
                    <?php if (!empty($post['reading_time'])): ?>
                      <div class="blog-post__meta-item">
                        • <?php echo (int)$post['reading_time']; ?> min read
                      </div>
                    <?php endif; ?>
                    <div class="blog-post__meta-item">
                      • <i class="fa-regular fa-comment" style="color: var(--accent); margin-right: 2px;"></i> <?php echo $cCount; ?> <?php echo $cCount === 1 ? 'comment' : 'comments'; ?>
                    </div>
                  </div>
                  
                  <h3 class="blog-post__title">
                    <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>">
                      <?php echo htmlspecialchars($post['title']); ?>
                    </a>
                  </h3>
                  
                  <?php if (!empty($post['excerpt'])): ?>
                    <p class="blog-post__excerpt">
                      <?php echo htmlspecialchars($post['excerpt']); ?>
                    </p>
                  <?php endif; ?>
                  
                  <?php if (!empty($post['tags'])): ?>
                    <div class="blog-post__tags">
                      <?php foreach (array_filter(array_map('trim', explode(',', $post['tags']))) as $tag): ?>
                        <span class="blog-post__tag">
                          #<?php echo htmlspecialchars($tag); ?>
                        </span>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <a href="post.php?slug=<?php echo urlencode($post['slug']); ?>" class="btn blog-post__cta">
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
