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
  $bits = [];
  if (!empty($post['category'])) {
    $bits = array_merge($bits, preg_split('/\s*,\s*/', $post['category']) ?: []);
  }
  if (!empty($post['tags'])) {
    $bits = array_merge($bits, preg_split('/\s*,\s*/', $post['tags']) ?: []);
  }
  foreach ($bits as $bit) {
    $bit = trim($bit);
    if ($bit !== '' && strlen($bit) <= 40) {
      $categories[$bit] = true;
    }
  }
}
$categories = array_keys($categories);
natcasesort($categories);
$categories = array_slice(array_values($categories), 0, 14);

$latest = $blogPosts[0] ?? null;
$topReads = array_slice($blogPosts, 1, 3);
$gridPosts = $blogPosts; // full list; JS filters/paginates

function bl_post_href(array $post): string {
  return 'post.php?slug=' . urlencode($post['slug'] ?? '');
}

function bl_post_date(array $post, string $fmt = 'F j, Y'): string {
  return !empty($post['published_at']) ? date($fmt, strtotime($post['published_at'])) : '';
}

function bl_post_img(array $post): string {
  return !empty($post['featured_image']) ? portfolioAssetUrl($post['featured_image']) : '';
}

function bl_post_tags(array $post): array {
  if (empty($post['tags'])) {
    return [];
  }
  return array_values(array_filter(array_map('trim', explode(',', $post['tags']))));
}
?>

<main id="main-content" class="bl-hub">
  <div class="bl-hub__bg" aria-hidden="true"></div>

  <div class="container bl-hub__inner">

    <?php if (empty($blogPosts)): ?>
      <div class="bl-hub-empty">
        <p>No published articles yet. Check back soon.</p>
      </div>
    <?php else: ?>

      <!-- ═══ THE LATEST + TOP READS ═══ -->
      <section class="bl-hub-top reveal-on-enter">
        <?php if ($latest):
          $latestHref = bl_post_href($latest);
          $latestImg = bl_post_img($latest);
          $latestDate = bl_post_date($latest);
        ?>
          <div class="bl-hub-latest">
            <h2 class="bl-hub-section-title">The Latest</h2>
            <article class="bl-hub-feature" data-category="<?php echo htmlspecialchars(strtolower(trim($latest['category'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>" data-search="<?php echo htmlspecialchars(strtolower(($latest['title'] ?? '') . ' ' . ($latest['excerpt'] ?? '') . ' ' . ($latest['category'] ?? '') . ' ' . ($latest['tags'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>">
              <a class="bl-hub-feature__media<?php echo $latestImg ? ' img-skeleton-wrap' : ' bl-hub-feature__media--empty'; ?>" href="<?php echo htmlspecialchars($latestHref, ENT_QUOTES, 'UTF-8'); ?>">
                <?php if ($latestImg): ?>
                  <img
                    src="<?php echo htmlspecialchars($latestImg, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo time(); ?>"
                    alt=""
                    loading="eager"
                    decoding="async"
                    onload="var w=this.closest('.img-skeleton-wrap'); if(w) w.classList.add('loaded')"
                  >
                <?php endif; ?>
              </a>
              <div class="bl-hub-feature__body">
                <h3 class="bl-hub-feature__title">
                  <a href="<?php echo htmlspecialchars($latestHref, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($latest['title'], ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                </h3>
                <div class="bl-hub-meta">
                  <?php if ($latestDate !== ''): ?>
                    <time datetime="<?php echo date('c', strtotime($latest['published_at'])); ?>"><?php echo $latestDate; ?></time>
                  <?php endif; ?>
                  <?php if (!empty($latest['author_name'])): ?>
                    <span class="bl-hub-meta__sep" aria-hidden="true">|</span>
                    <span><?php echo htmlspecialchars($latest['author_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endif; ?>
                </div>
                <?php if (!empty($latest['excerpt'])): ?>
                  <p class="bl-hub-feature__excerpt"><?php echo htmlspecialchars($latest['excerpt'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <a class="bl-hub-more" href="<?php echo htmlspecialchars($latestHref, ENT_QUOTES, 'UTF-8'); ?>">Read more</a>
              </div>
            </article>
          </div>
        <?php endif; ?>

        <?php if (!empty($topReads)): ?>
          <aside class="bl-hub-topreads">
            <h2 class="bl-hub-section-title">Top Reads</h2>
            <div class="bl-hub-topreads__list">
              <?php foreach ($topReads as $tr):
                $trHref = bl_post_href($tr);
                $trImg = bl_post_img($tr);
                $trDate = bl_post_date($tr);
              ?>
                <article class="bl-hub-topread">
                  <a class="bl-hub-topread__thumb<?php echo $trImg ? ' img-skeleton-wrap' : ' bl-hub-topread__thumb--empty'; ?>" href="<?php echo htmlspecialchars($trHref, ENT_QUOTES, 'UTF-8'); ?>" tabindex="-1" aria-hidden="true">
                    <?php if ($trImg): ?>
                      <img
                        src="<?php echo htmlspecialchars($trImg, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo time(); ?>"
                        alt=""
                        loading="lazy"
                        decoding="async"
                        onload="var w=this.closest('.img-skeleton-wrap'); if(w) w.classList.add('loaded')"
                      >
                    <?php endif; ?>
                  </a>
                  <div class="bl-hub-topread__copy">
                    <h3 class="bl-hub-topread__title">
                      <a href="<?php echo htmlspecialchars($trHref, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($tr['title'], ENT_QUOTES, 'UTF-8'); ?>
                      </a>
                    </h3>
                    <div class="bl-hub-meta bl-hub-meta--sm">
                      <?php if ($trDate !== ''): ?>
                        <time datetime="<?php echo date('c', strtotime($tr['published_at'])); ?>"><?php echo $trDate; ?></time>
                      <?php endif; ?>
                      <?php if (!empty($tr['author_name'])): ?>
                        <span class="bl-hub-meta__sep" aria-hidden="true">|</span>
                        <span><?php echo htmlspecialchars($tr['author_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                      <?php endif; ?>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </aside>
        <?php endif; ?>
      </section>

      <!-- ═══ BROWSE / SEARCH / CATEGORIES ═══ -->
      <section class="bl-hub-browse reveal-on-enter">
        <div class="bl-hub-browse__head">
          <h2 class="bl-hub-section-title">Browse by categories</h2>
          <label class="bl-hub-search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search" id="bl-hub-search" placeholder="Search articles…" autocomplete="off">
          </label>
        </div>

        <nav class="bl-hub-cats" aria-label="Filter by category">
          <button type="button" class="bl-hub-cat is-active" data-filter="all">All</button>
          <?php foreach ($categories as $cat): ?>
            <button type="button" class="bl-hub-cat" data-filter="<?php echo htmlspecialchars(strtolower($cat), ENT_QUOTES, 'UTF-8'); ?>">
              <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
            </button>
          <?php endforeach; ?>
        </nav>
      </section>

      <!-- ═══ GRID ═══ -->
      <section class="bl-hub-feed" aria-label="All articles">
        <div id="blog-list" class="bl-hub-grid">
          <?php foreach ($gridPosts as $index => $post):
            $href = bl_post_href($post);
            $img = bl_post_img($post);
            $date = bl_post_date($post);
            $tagList = bl_post_tags($post);
            if (empty($tagList) && !empty($post['category'])) {
              $tagList = array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', $post['category']) ?: [])));
            }
            $filterKeys = array_map('strtolower', $tagList);
            if (!empty($post['category'])) {
              foreach (preg_split('/\s*,\s*/', $post['category']) ?: [] as $cBit) {
                $cBit = strtolower(trim($cBit));
                if ($cBit !== '') {
                  $filterKeys[] = $cBit;
                }
              }
            }
            $filterKeys = array_values(array_unique($filterKeys));
            $searchBlob = strtolower(($post['title'] ?? '') . ' ' . ($post['excerpt'] ?? '') . ' ' . ($post['category'] ?? '') . ' ' . ($post['tags'] ?? '') . ' ' . ($post['author_name'] ?? ''));
          ?>
            <article
              class="bl-hub-card reveal-on-enter"
              data-category="<?php echo htmlspecialchars(implode(' ', $filterKeys), ENT_QUOTES, 'UTF-8'); ?>"
              data-search="<?php echo htmlspecialchars($searchBlob, ENT_QUOTES, 'UTF-8'); ?>"
            >
              <a class="bl-hub-card__media<?php echo $img ? ' img-skeleton-wrap' : ' bl-hub-card__media--empty'; ?>" href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">
                <?php if ($img): ?>
                  <img
                    src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo time(); ?>"
                    alt=""
                    loading="<?php echo $index < 6 ? 'eager' : 'lazy'; ?>"
                    decoding="async"
                    onload="var w=this.closest('.img-skeleton-wrap'); if(w) w.classList.add('loaded')"
                  >
                <?php else: ?>
                  <span class="bl-hub-card__letter" aria-hidden="true"><?php echo htmlspecialchars(strtoupper(substr($post['title'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
                <?php if (!empty($tagList)): ?>
                  <span class="bl-hub-card__tags">
                    <?php foreach (array_slice($tagList, 0, 3) as $tag): ?>
                      <span><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endforeach; ?>
                  </span>
                <?php endif; ?>
              </a>
              <div class="bl-hub-card__body">
                <?php if ($date !== ''): ?>
                  <time class="bl-hub-card__date" datetime="<?php echo date('c', strtotime($post['published_at'])); ?>"><?php echo $date; ?></time>
                <?php endif; ?>
                <h3 class="bl-hub-card__title">
                  <a href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                </h3>
                <?php if (!empty($post['excerpt'])): ?>
                  <p class="bl-hub-card__excerpt"><?php echo htmlspecialchars($post['excerpt'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <a class="bl-hub-more" href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>">Read more</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <p class="bl-hub-empty" id="bl-hub-empty" hidden>No articles match your search.</p>
        <nav class="bl-hub-pages" id="bl-hub-pages" aria-label="Blog pages" hidden></nav>
      </section>

    <?php endif; ?>
  </div>
</main>

<script>
(function () {
  var cards = Array.prototype.slice.call(document.querySelectorAll('.bl-hub-card'));
  var cats = document.querySelectorAll('.bl-hub-cat');
  var searchInput = document.getElementById('bl-hub-search');
  var emptyMsg = document.getElementById('bl-hub-empty');
  var pagesEl = document.getElementById('bl-hub-pages');
  if (!cards.length) return;

  var PAGE_SIZE = 6;
  var filter = 'all';
  var query = '';
  var page = 1;

  function matches(card) {
    var cat = ' ' + (card.getAttribute('data-category') || '') + ' ';
    var blob = card.getAttribute('data-search') || '';
    var okCat = filter === 'all' || cat.indexOf(' ' + filter + ' ') !== -1;
    var okSearch = !query || blob.indexOf(query) !== -1;
    return okCat && okSearch;
  }

  function render() {
    var matched = cards.filter(matches);
    var totalPages = Math.max(1, Math.ceil(matched.length / PAGE_SIZE));
    if (page > totalPages) page = totalPages;

    cards.forEach(function (card) { card.classList.add('is-hidden'); });
    matched.forEach(function (card, i) {
      var start = (page - 1) * PAGE_SIZE;
      var end = start + PAGE_SIZE;
      card.classList.toggle('is-hidden', !(i >= start && i < end));
    });

    if (emptyMsg) emptyMsg.hidden = matched.length > 0;

    if (!pagesEl) return;
    if (matched.length <= PAGE_SIZE) {
      pagesEl.hidden = true;
      pagesEl.innerHTML = '';
      return;
    }
    pagesEl.hidden = false;
    var html = '';
    for (var p = 1; p <= totalPages; p++) {
      html += '<button type="button" class="bl-hub-page' + (p === page ? ' is-active' : '') + '" data-page="' + p + '">' + p + '</button>';
    }
    pagesEl.innerHTML = html;
    pagesEl.querySelectorAll('[data-page]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        page = parseInt(btn.getAttribute('data-page'), 10) || 1;
        render();
        var feed = document.querySelector('.bl-hub-feed');
        if (feed) feed.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }

  cats.forEach(function (btn) {
    btn.addEventListener('click', function () {
      cats.forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      filter = btn.getAttribute('data-filter') || 'all';
      page = 1;
      render();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      query = (searchInput.value || '').toLowerCase().trim();
      page = 1;
      render();
    });
  }

  render();
})();
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
