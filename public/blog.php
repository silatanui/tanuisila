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

<main id="main-content" class="bl-newsroom">
  <header class="bl-nr-hero reveal-on-enter">
    <div class="container">
      <h1 class="bl-nr-hero__title">Writing &amp; Insights</h1>
      <p class="bl-nr-hero__lede">
        The latest notes, announcements, lessons, and learnings on software, AI, and systems.
      </p>
    </div>
  </header>

  <div class="container bl-nr-body">
    <nav class="bl-nr-nav reveal-on-enter" aria-label="Blog categories">
      <button type="button" class="bl-nr-nav__link is-active" data-filter="all">All posts</button>
      <?php foreach ($categories as $cat): ?>
        <button type="button" class="bl-nr-nav__link" data-filter="<?php echo htmlspecialchars(strtolower($cat), ENT_QUOTES, 'UTF-8'); ?>">
          <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
        </button>
      <?php endforeach; ?>
    </nav>

    <?php if (empty($blogPosts)): ?>
      <div class="bl-nr-empty reveal-on-enter">
        <p>No published articles yet. Check back soon.</p>
      </div>
    <?php else: ?>
      <div id="blog-list" class="bl-nr-grid">
        <?php foreach ($blogPosts as $index => $post):
          $catKey = strtolower(trim($post['category'] ?? ''));
          $href = 'post.php?slug=' . urlencode($post['slug']);
          $hasImage = !empty($post['featured_image']);
          $dateLabel = !empty($post['published_at'])
            ? strtoupper(date('F j, Y', strtotime($post['published_at'])))
            : '';
        ?>
          <article
            class="bl-nr-card reveal-on-enter"
            data-category="<?php echo htmlspecialchars($catKey, ENT_QUOTES, 'UTF-8'); ?>"
          >
            <a class="bl-nr-card__media<?php echo $hasImage ? ' img-skeleton-wrap' : ' bl-nr-card__media--placeholder'; ?>" href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" aria-hidden="true">
              <?php if ($hasImage): ?>
                <img
                  src="<?php echo htmlspecialchars(portfolioAssetUrl($post['featured_image']), ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo time(); ?>"
                  alt=""
                  loading="<?php echo $index < 3 ? 'eager' : 'lazy'; ?>"
                  decoding="async"
                  onload="var w=this.closest('.img-skeleton-wrap'); if(w) w.classList.add('loaded')"
                >
              <?php else: ?>
                <span class="bl-nr-card__placeholder-mark" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($post['title'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endif; ?>
            </a>

            <div class="bl-nr-card__body">
              <div class="bl-nr-card__meta">
                <?php if ($dateLabel !== ''): ?>
                  <time class="bl-nr-chip" datetime="<?php echo date('c', strtotime($post['published_at'])); ?>">
                    <?php echo $dateLabel; ?>
                  </time>
                <?php endif; ?>
                <?php if (!empty($post['category'])): ?>
                  <span class="bl-nr-chip bl-nr-chip--cat"><?php echo htmlspecialchars(strtoupper($post['category']), ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
              </div>

              <h2 class="bl-nr-card__title">
                <a href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">
                  <?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
              </h2>

              <?php if (!empty($post['excerpt'])): ?>
                <p class="bl-nr-card__excerpt"><?php echo htmlspecialchars($post['excerpt'], ENT_QUOTES, 'UTF-8'); ?></p>
              <?php endif; ?>

              <a class="bl-nr-card__more" href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">
                Read more <span aria-hidden="true">›</span>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <p class="bl-nr-filter-empty" id="bl-filter-empty" hidden>
        No articles in that category.
        <button type="button" class="bl-nr-filter-reset" data-filter="all">Show all posts</button>
      </p>
    <?php endif; ?>
  </div>
</main>

<script>
(function () {
  var filters = document.querySelectorAll('.bl-nr-nav__link, .bl-nr-filter-reset');
  var cards = document.querySelectorAll('.bl-nr-card');
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
    document.querySelectorAll('.bl-nr-nav__link').forEach(function (btn) {
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
