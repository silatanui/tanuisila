<?php
$activePage = 'blog';
require_once __DIR__ . '/header.php';

$blogPosts = $pdo->query("
    SELECT p.*,
           (SELECT COUNT(*) FROM blog_comments c WHERE c.post_id = p.id AND c.status = 'approved') AS comments_count
    FROM blog_posts p
    WHERE p.status = 'published'
    ORDER BY p.published_at DESC, p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$postCount = count($blogPosts);
$categories = [];
foreach ($blogPosts as $post) {
  if (!empty($post['category'])) {
    $categories[trim($post['category'])] = true;
  }
}
$categories = array_keys($categories);
natcasesort($categories);
$categories = array_values($categories);
?>

<main id="main-content" class="container">
  <section id="blog" class="section-row bl-page" style="border-bottom:none;">
    <div class="section-label">Blog</div>
    <div class="section-content" style="width:100%;">

      <header class="bl-hero reveal-on-enter">
        <p class="bl-hero__eyebrow">Writing</p>
        <h1 class="bl-hero__title">Writing &amp; Insights</h1>
        <p class="bl-hero__lede">
          Notes on software, AI, systems, and building things that hold up in the real world.
        </p>
        <div class="bl-hero__meta">
          <span class="bl-hero__count"><?php echo (int) $postCount; ?> article<?php echo $postCount === 1 ? '' : 's'; ?></span>
          <?php if ($postCount > 0): ?>
            <a class="bl-hero__jump" href="#blog-list">Read the latest ↓</a>
          <?php endif; ?>
        </div>
      </header>

      <?php if (empty($blogPosts)): ?>
        <div class="bl-empty reveal-on-enter">
          <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
          <p>No published articles yet. Check back soon.</p>
        </div>
      <?php else: ?>

        <?php if (count($categories) > 1): ?>
          <div class="bl-filters reveal-on-enter" role="toolbar" aria-label="Filter articles by category">
            <button type="button" class="bl-filter is-active" data-filter="all">All</button>
            <?php foreach ($categories as $cat): ?>
              <button type="button" class="bl-filter" data-filter="<?php echo htmlspecialchars(strtolower($cat), ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div id="blog-list" class="bl-list">
          <?php foreach ($blogPosts as $index => $post):
            $cCount = (int) ($post['comments_count'] ?? 0);
            $isFeatured = $index === 0;
            $catKey = strtolower(trim($post['category'] ?? ''));
            $href = 'post.php?slug=' . urlencode($post['slug']);
            $tags = !empty($post['tags'])
              ? array_values(array_filter(array_map('trim', explode(',', $post['tags']))))
              : [];
          ?>
            <article
              class="bl-card reveal-on-enter<?php echo $isFeatured ? ' bl-card--featured' : ''; ?><?php echo empty($post['featured_image']) ? ' bl-card--text' : ''; ?>"
              data-category="<?php echo htmlspecialchars($catKey, ENT_QUOTES, 'UTF-8'); ?>"
            >
              <?php if (!empty($post['featured_image'])): ?>
                <a class="bl-card__media img-skeleton-wrap" href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" aria-hidden="true">
                  <img
                    src="<?php echo htmlspecialchars(portfolioAssetUrl($post['featured_image']), ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo time(); ?>"
                    alt=""
                    loading="<?php echo $isFeatured ? 'eager' : 'lazy'; ?>"
                    decoding="async"
                    onload="var w=this.closest('.img-skeleton-wrap'); if(w) w.classList.add('loaded')"
                  >
                </a>
              <?php endif; ?>

              <div class="bl-card__body">
                <div class="bl-card__meta">
                  <?php if ($isFeatured): ?>
                    <span class="bl-card__badge">Latest</span>
                  <?php endif; ?>
                  <time datetime="<?php echo !empty($post['published_at']) ? date('c', strtotime($post['published_at'])) : ''; ?>">
                    <?php echo !empty($post['published_at']) ? date('M d, Y', strtotime($post['published_at'])) : 'Draft'; ?>
                  </time>
                  <?php if (!empty($post['category'])): ?>
                    <span class="bl-card__dot" aria-hidden="true">·</span>
                    <span><?php echo htmlspecialchars($post['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endif; ?>
                  <?php if (!empty($post['reading_time'])): ?>
                    <span class="bl-card__dot" aria-hidden="true">·</span>
                    <span><?php echo (int) $post['reading_time']; ?> min read</span>
                  <?php endif; ?>
                  <span class="bl-card__dot" aria-hidden="true">·</span>
                  <span><i class="fa-regular fa-comment" aria-hidden="true"></i> <?php echo $cCount; ?></span>
                </div>

                <h2 class="bl-card__title">
                  <a href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                </h2>

                <?php if (!empty($post['excerpt'])): ?>
                  <p class="bl-card__excerpt"><?php echo htmlspecialchars($post['excerpt'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <?php if (!empty($tags)): ?>
                  <ul class="bl-card__tags">
                    <?php foreach (array_slice($tags, 0, 5) as $tag): ?>
                      <li>#<?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>

                <a class="bl-card__cta" href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">
                  Read article <span aria-hidden="true">→</span>
                </a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <p class="bl-filter-empty" id="bl-filter-empty" hidden>
          No articles in that category. <button type="button" class="bl-filter-reset" data-filter="all">Show all</button>
        </p>

      <?php endif; ?>
    </div>
  </section>
</main>

<script>
(function () {
  var filters = document.querySelectorAll('.bl-filter, .bl-filter-reset');
  var cards = document.querySelectorAll('.bl-card');
  var emptyMsg = document.getElementById('bl-filter-empty');
  if (!filters.length || !cards.length) return;

  function applyFilter(value) {
    var visible = 0;
    cards.forEach(function (el) {
      var cat = (el.getAttribute('data-category') || '');
      var show = value === 'all' || cat === value;
      el.classList.toggle('is-filtered-out', !show);
      if (show) visible++;
    });
    document.querySelectorAll('.bl-filter').forEach(function (btn) {
      btn.classList.toggle('is-active', btn.getAttribute('data-filter') === value);
    });
    if (emptyMsg) emptyMsg.hidden = visible > 0;
  }

  filters.forEach(function (btn) {
    btn.addEventListener('click', function () {
      applyFilter(btn.getAttribute('data-filter') || 'all');
    });
  });
})();
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
