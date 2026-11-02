<?php
$activePage = 'projects';
require_once __DIR__ . '/header.php';

$projectCount = count($projects);

function project_resolve_image(array $p): string {
  if (!empty($p['image'])) {
    $rawImg = trim($p['image']);
    if (preg_match('#^https?://#i', $rawImg)) {
      return $rawImg;
    }
    if (strpos($rawImg, '/') === 0) {
      return portfolioAssetUrl($rawImg);
    }
    if (file_exists(__DIR__ . '/../assets/images/projects/' . $rawImg)) {
      return appUrl('assets/images/projects/' . $rawImg);
    }
    return appUrl('public/' . $rawImg);
  }
  return '';
}

function project_category_label(array $p): string {
  $title = strtolower(trim((string) ($p['title'] ?? '')));
  $tags = strtolower((string) ($p['tags'] ?? ''));
  if (str_contains($title, 'commerce') || str_contains($tags, 'e-commerce') || str_contains($title, 'university')) {
    return 'Websites';
  }
  return 'Apps';
}

function project_short_desc(array $p, int $max = 88): string {
  $desc = preg_replace('/\s+/', ' ', trim((string) ($p['description'] ?? '')));
  if ($desc === '') {
    return 'Open the live project';
  }
  if (strlen($desc) <= $max) {
    return $desc;
  }
  return rtrim(substr($desc, 0, $max - 3)) . '...';
}

function project_commerce_slides(): array {
  $base = __DIR__ . '/../assets/images/projects';
  $slides = [
    [
      'label' => 'Fashion',
      'file' => 'commerce-fashion.png',
      'url' => 'https://tanuisila.dev/commerce/store/fashion',
    ],
    [
      'label' => 'Electronics',
      'file' => 'commerce-electronics.png',
      'url' => 'https://tanuisila.dev/commerce/store/electronics',
    ],
    [
      'label' => 'Home',
      'file' => 'commerce-home.png',
      'url' => 'https://tanuisila.dev/commerce/store/home',
    ],
    [
      'label' => 'Market',
      'file' => 'commerce-market.png',
      'url' => 'https://tanuisila.dev/commerce/store/market',
    ],
  ];

  $out = [];
  foreach ($slides as $slide) {
    $path = $base . '/' . $slide['file'];
    if (!is_file($path)) {
      continue;
    }
    $out[] = [
      'label' => $slide['label'],
      'url' => $slide['url'],
      'src' => appUrl('assets/images/projects/' . $slide['file']) . '?v=' . (int) filemtime($path),
    ];
  }
  return $out;
}

function project_subcategories(array $p): array {
  $title = strtolower(trim((string) ($p['title'] ?? '')));
  $tags = strtolower((string) ($p['tags'] ?? ''));
  $hay = $title . ' ' . $tags;
  $subs = [];

  $rules = [
    'AI' => ['ai', 'openai', 'assistant', 'detector', 'humanizer'],
    'Automation' => ['automation', 'rea'],
    'Developer Tools' => ['reverse engineering', 'rea', 'quire'],
    'Ecommerce' => ['commerce', 'e-commerce', 'store'],
    'Education' => ['university', 'moodle', 'education', 'lms', 'e-learning', 'edtech'],
    'LMS' => ['moodle', 'lms', 'learning management'],
    'Music' => ['lyric', 'music', 'song'],
    'Resume' => ['cv', 'resume', 'ats', 'cover letter'],
    'SaaS' => ['cv studio', 'flyleaf', 'saas', 'studio'],
  ];

  foreach ($rules as $label => $needles) {
    foreach ($needles as $needle) {
      if (str_contains($hay, $needle)) {
        $subs[$label] = true;
        break;
      }
    }
  }

  return array_keys($subs);
}

$subcategorySet = [];
foreach ($projects as $p) {
  foreach (project_subcategories($p) as $sub) {
    $subcategorySet[$sub] = true;
  }
}
$subcategories = array_keys($subcategorySet);
natcasesort($subcategories);
$subcategories = array_values($subcategories);
?>

<main id="main-content" class="proj-shell">
  <div class="proj-shell__bg" aria-hidden="true"></div>

  <section id="projects" class="container proj-page">
    <header class="proj-hero reveal-on-enter">
      <div class="proj-hero__copy">
        <p class="proj-hero__eyebrow">Selected work</p>
        <h1 class="proj-hero__title">Projects</h1>
        <p class="proj-hero__lede">
          Live products and tools. Open any card to launch the hosted project.
        </p>
      </div>
    </header>

    <?php if (empty($projects)): ?>
      <div class="proj-empty reveal-on-enter">
        <i class="fa-solid fa-code" aria-hidden="true"></i>
        <p>Projects are being updated. Check back shortly.</p>
        <a class="btn primary" href="<?php echo appUrl('public/contact.php'); ?>">Get in touch</a>
      </div>
    <?php else: ?>

      <div class="proj-layout">
        <aside class="proj-sidepanel reveal-on-enter" aria-label="Filter projects">
          <div class="proj-sidepanel__group">
            <h2 class="proj-sidepanel__heading">Category</h2>
            <div class="proj-sidepanel__cats" role="tablist" aria-label="Project category">
              <button type="button" class="proj-sidepanel__cat is-active" data-category="all" role="tab" aria-selected="true">All</button>
              <button type="button" class="proj-sidepanel__cat" data-category="websites" role="tab" aria-selected="false">Websites</button>
              <button type="button" class="proj-sidepanel__cat" data-category="apps" role="tab" aria-selected="false">Apps</button>
            </div>
          </div>

          <?php if (!empty($subcategories)): ?>
            <div class="proj-sidepanel__group">
              <h2 class="proj-sidepanel__heading">Subcategory</h2>
              <div class="proj-sidepanel__subs" role="group" aria-label="Project subcategories">
                <?php foreach ($subcategories as $sub):
                  $subId = 'proj-sub-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($sub));
                ?>
                  <label class="proj-sidepanel__sub" for="<?php echo htmlspecialchars($subId, ENT_QUOTES, 'UTF-8'); ?>">
                    <input
                      id="<?php echo htmlspecialchars($subId, ENT_QUOTES, 'UTF-8'); ?>"
                      class="proj-sidepanel__check"
                      type="checkbox"
                      value="<?php echo htmlspecialchars(strtolower($sub), ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    <span class="proj-sidepanel__box" aria-hidden="true"></span>
                    <span class="proj-sidepanel__label"><?php echo htmlspecialchars($sub, ENT_QUOTES, 'UTF-8'); ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </aside>

        <div class="proj-main">
          <div class="proj-main__bar">
            <h2 class="proj-main__title" id="proj-results-title">All projects (<?php echo (int) $projectCount; ?>)</h2>
            <button type="button" class="proj-main__clear" id="proj-clear-filters" hidden>Clear filters</button>
          </div>

          <div id="project-list" class="proj-gallery">
            <?php foreach ($projects as $p):
              $pImg = project_resolve_image($p);
              $tagList = [];
              if (!empty($p['tags'])) {
                $tagList = array_values(array_filter(array_map('trim', explode(',', $p['tags']))));
              }
              $subs = project_subcategories($p);
              $subsData = strtolower(implode(',', $subs));
              $tagData = strtolower(implode(' ', $tagList));
              $category = project_category_label($p);
              $short = project_short_desc($p);
              $href = !empty($p['url'])
                ? $p['url']
                : appUrl('projects.php') . '#project-' . (int) $p['id'];
              $external = !empty($p['url']);
              $slug = 'project-' . (int) $p['id'];
              $isCommerce = strcasecmp(trim((string) ($p['title'] ?? '')), 'Tanui Commerce') === 0;
              $commerceSlides = $isCommerce ? project_commerce_slides() : [];
              $hasCarousel = count($commerceSlides) > 1;
            ?>
              <article
                id="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>"
                class="proj-gallery__card reveal-on-enter<?php echo $hasCarousel ? ' proj-gallery__card--carousel' : ''; ?>"
                data-category="<?php echo htmlspecialchars(strtolower($category), ENT_QUOTES, 'UTF-8'); ?>"
                data-subcats="<?php echo htmlspecialchars($subsData, ENT_QUOTES, 'UTF-8'); ?>"
                data-tags="<?php echo htmlspecialchars($tagData . ' ' . strtolower($category), ENT_QUOTES, 'UTF-8'); ?>"
                data-project-id="<?php echo (int) $p['id']; ?>"
              >
                <?php if ($hasCarousel): ?>
                  <div class="proj-carousel" data-proj-carousel>
                    <div class="proj-carousel__viewport">
                      <div class="proj-carousel__track">
                        <?php foreach ($commerceSlides as $i => $slide): ?>
                          <a
                            class="proj-carousel__slide js-project-launch<?php echo $i === 0 ? ' is-active' : ''; ?>"
                            href="<?php echo htmlspecialchars($slide['url'], ENT_QUOTES, 'UTF-8'); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            data-project-id="<?php echo (int) $p['id']; ?>"
                            data-label="<?php echo htmlspecialchars($slide['label'], ENT_QUOTES, 'UTF-8'); ?>"
                            aria-label="Open Tanui <?php echo htmlspecialchars($slide['label'], ENT_QUOTES, 'UTF-8'); ?> store"
                          >
                            <img
                              src="<?php echo htmlspecialchars($slide['src'], ENT_QUOTES, 'UTF-8'); ?>"
                              alt="Tanui <?php echo htmlspecialchars($slide['label'], ENT_QUOTES, 'UTF-8'); ?> storefront"
                              loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>"
                              decoding="async"
                              width="960"
                              height="660"
                            >
                          </a>
                        <?php endforeach; ?>
                      </div>
                    </div>
                    <button type="button" class="proj-carousel__nav proj-carousel__nav--prev" aria-label="Previous store">
                      <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="proj-carousel__nav proj-carousel__nav--next" aria-label="Next store">
                      <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </button>
                    <div class="proj-carousel__chrome">
                      <span class="proj-carousel__badge" data-carousel-label><?php echo htmlspecialchars($commerceSlides[0]['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                      <div class="proj-carousel__dots" role="tablist" aria-label="Tanui Commerce stores">
                        <?php foreach ($commerceSlides as $i => $slide): ?>
                          <button
                            type="button"
                            class="proj-carousel__dot<?php echo $i === 0 ? ' is-active' : ''; ?>"
                            data-index="<?php echo (int) $i; ?>"
                            aria-label="Show <?php echo htmlspecialchars($slide['label'], ENT_QUOTES, 'UTF-8'); ?>"
                            aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                          ></button>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                  <a
                    class="proj-gallery__meta-link js-project-launch"
                    href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>"
                    <?php if ($external): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
                    data-project-id="<?php echo (int) $p['id']; ?>"
                  >
                    <div class="proj-gallery__meta">
                      <div class="proj-gallery__copy">
                        <h2 class="proj-gallery__title"><?php echo htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="proj-gallery__desc"><?php echo htmlspecialchars($short, ENT_QUOTES, 'UTF-8'); ?></p>
                      </div>
                      <span class="proj-gallery__tag"><?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                  </a>
                <?php else: ?>
                  <a
                    class="proj-gallery__link js-project-launch"
                    href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>"
                    <?php if ($external): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
                    data-project-id="<?php echo (int) $p['id']; ?>"
                    title="<?php echo htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8'); ?>"
                  >
                    <div class="proj-gallery__media<?php echo $pImg ? '' : ' proj-gallery__media--empty'; ?>">
                      <?php if ($pImg): ?>
                        <img
                          class="proj-gallery__img"
                          src="<?php echo htmlspecialchars($pImg, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo @filemtime(__DIR__ . '/../assets/images/projects/' . basename((string) ($p['image'] ?? 'x'))); ?>"
                          alt="<?php echo htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8'); ?> screenshot"
                          loading="lazy"
                          decoding="async"
                          width="960"
                          height="660"
                        >
                      <?php else: ?>
                        <?php echo media_placeholder($p['title'] ?? '', 'media-ph--project'); ?>
                      <?php endif; ?>
                    </div>

                    <div class="proj-gallery__meta">
                      <div class="proj-gallery__copy">
                        <h2 class="proj-gallery__title"><?php echo htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="proj-gallery__desc"><?php echo htmlspecialchars($short, ENT_QUOTES, 'UTF-8'); ?></p>
                      </div>
                      <span class="proj-gallery__tag"><?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                  </a>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>

          <p class="proj-filter-empty" id="proj-filter-empty" hidden>
            No projects match those filters.
            <button type="button" class="proj-filter-reset" id="proj-filter-reset">Show all</button>
          </p>
        </div>
      </div>

    <?php endif; ?>

    <aside class="proj-pipeline reveal-on-enter">
      <div class="proj-pipeline__mark" aria-hidden="true">
        <i class="fa-solid fa-layer-group"></i>
      </div>
      <div class="proj-pipeline__copy">
        <h2>Building what is next</h2>
        <p>
          New production apps and tools ship regularly. If you have a problem worth engineering,
          we can talk scope and delivery.
        </p>
      </div>
      <a class="btn proj-pipeline__cta" href="<?php echo appUrl('public/contact.php'); ?>">
        Propose a project
      </a>
    </aside>
  </section>
</main>

<script>
(function () {
  var trackUrl = <?php echo json_encode(appUrl('public/track-project.php'), JSON_UNESCAPED_SLASHES); ?>;
  var seenViews = {};

  function sendProjectEvent(projectId, event) {
    if (!projectId) return;
    var body = JSON.stringify({ project_id: projectId, event: event });
    try {
      if (navigator.sendBeacon) {
        navigator.sendBeacon(trackUrl, new Blob([body], { type: 'application/json' }));
        return;
      }
    } catch (e) {}
    try {
      fetch(trackUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: body,
        keepalive: true,
        credentials: 'same-origin'
      });
    } catch (e2) {}
  }

  document.querySelectorAll('.js-project-launch').forEach(function (link) {
    link.addEventListener('click', function () {
      sendProjectEvent(parseInt(link.getAttribute('data-project-id') || '0', 10), 'click');
    });
  });

  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var id = parseInt(entry.target.getAttribute('data-project-id') || '0', 10);
        if (!id || seenViews[id]) return;
        seenViews[id] = true;
        sendProjectEvent(id, 'view');
        io.unobserve(entry.target);
      });
    }, { threshold: 0.45 });
    document.querySelectorAll('.proj-gallery__card[data-project-id]').forEach(function (el) {
      io.observe(el);
    });
  }

  var catButtons = document.querySelectorAll('.proj-sidepanel__cat');
  var subChecks = document.querySelectorAll('.proj-sidepanel__check');
  var cards = document.querySelectorAll('.proj-gallery__card');
  var titleEl = document.getElementById('proj-results-title');
  var emptyEl = document.getElementById('proj-filter-empty');
  var clearBtn = document.getElementById('proj-clear-filters');
  var resetBtn = document.getElementById('proj-filter-reset');
  if (!catButtons.length || !cards.length) return;

  var activeCategory = 'all';

  function selectedSubs() {
    return Array.prototype.filter.call(subChecks, function (c) { return c.checked; })
      .map(function (c) { return (c.value || '').toLowerCase(); });
  }

  function titleFor(category, count) {
    if (category === 'websites') return 'Websites (' + count + ')';
    if (category === 'apps') return 'Apps (' + count + ')';
    return 'All projects (' + count + ')';
  }

  function applyFilters() {
    var subs = selectedSubs();
    var visible = 0;

    cards.forEach(function (card) {
      var cat = (card.getAttribute('data-category') || '').toLowerCase();
      var cardSubs = (card.getAttribute('data-subcats') || '').toLowerCase().split(',').filter(Boolean);
      var catOk = activeCategory === 'all' || cat === activeCategory;
      var subOk = !subs.length || subs.some(function (s) { return cardSubs.indexOf(s) !== -1; });
      var show = catOk && subOk;
      card.classList.toggle('is-filtered-out', !show);
      if (show) visible++;
    });

    if (titleEl) titleEl.textContent = titleFor(activeCategory, visible);
    if (emptyEl) emptyEl.hidden = visible > 0;
    if (clearBtn) clearBtn.hidden = !(activeCategory !== 'all' || subs.length);
  }

  function setCategory(value) {
    activeCategory = value || 'all';
    catButtons.forEach(function (btn) {
      var on = (btn.getAttribute('data-category') || '') === activeCategory;
      btn.classList.toggle('is-active', on);
      btn.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    applyFilters();
  }

  function clearFilters() {
    subChecks.forEach(function (c) { c.checked = false; });
    setCategory('all');
  }

  catButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      setCategory(btn.getAttribute('data-category') || 'all');
    });
  });

  subChecks.forEach(function (c) {
    c.addEventListener('change', applyFilters);
  });

  if (clearBtn) clearBtn.addEventListener('click', clearFilters);
  if (resetBtn) resetBtn.addEventListener('click', clearFilters);

  applyFilters();

  document.querySelectorAll('[data-proj-carousel]').forEach(function (carousel) {
    var track = carousel.querySelector('.proj-carousel__track');
    var slides = carousel.querySelectorAll('.proj-carousel__slide');
    var dots = carousel.querySelectorAll('.proj-carousel__dot');
    var label = carousel.querySelector('[data-carousel-label]');
    var prev = carousel.querySelector('.proj-carousel__nav--prev');
    var next = carousel.querySelector('.proj-carousel__nav--next');
    if (!track || slides.length < 2) return;

    var index = 0;
    var timer = null;

    function goTo(nextIndex) {
      index = (nextIndex + slides.length) % slides.length;
      track.style.transform = 'translateX(' + (-index * 100) + '%)';
      slides.forEach(function (slide, i) {
        slide.classList.toggle('is-active', i === index);
      });
      dots.forEach(function (dot, i) {
        var on = i === index;
        dot.classList.toggle('is-active', on);
        dot.setAttribute('aria-selected', on ? 'true' : 'false');
      });
      if (label) {
        label.textContent = slides[index].getAttribute('data-label') || '';
      }
    }

    function start() {
      stop();
      timer = window.setInterval(function () {
        goTo(index + 1);
      }, 4200);
    }

    function stop() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    if (prev) {
      prev.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        goTo(index - 1);
        start();
      });
    }

    if (next) {
      next.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        goTo(index + 1);
        start();
      });
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        goTo(parseInt(dot.getAttribute('data-index') || '0', 10));
        start();
      });
    });

    carousel.addEventListener('mouseenter', stop);
    carousel.addEventListener('mouseleave', start);
    carousel.addEventListener('focusin', stop);
    carousel.addEventListener('focusout', start);

    goTo(0);
    start();
  });
})();
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
