<?php
$activePage = 'projects';
require_once __DIR__ . '/header.php';

$projectCount = count($projects);
$allTags = [];
foreach ($projects as $p) {
  if (empty($p['tags'])) {
    continue;
  }
  foreach (array_map('trim', explode(',', $p['tags'])) as $tag) {
    if ($tag !== '') {
      $allTags[$tag] = true;
    }
  }
}
$allTags = array_keys($allTags);
natcasesort($allTags);
$allTags = array_values($allTags);

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
  if (stripos($p['title'] ?? '', 'cv') !== false) {
    return appUrl('public/cv_studio.png');
  }
  return '';
}
?>

<main id="main-content" class="container">
  <section id="projects" class="section-row proj-page" style="border-bottom:none;">
    <div class="section-label">Projects</div>
    <div class="section-content" style="width:100%;">

      <header class="proj-hero reveal-on-enter">
        <p class="proj-hero__eyebrow">Selected work</p>
        <h1 class="proj-hero__title">Engineering Work &amp; Software Systems</h1>
        <p class="proj-hero__lede">
          Production software, AI applications, and tools built for real-world use —
          each shipped with a clear problem, stack, and live surface.
        </p>
        <div class="proj-hero__meta">
          <span class="proj-hero__count"><?php echo (int) $projectCount; ?> project<?php echo $projectCount === 1 ? '' : 's'; ?></span>
          <?php if ($projectCount > 0): ?>
            <a class="proj-hero__jump" href="#project-list">Browse work ↓</a>
          <?php endif; ?>
        </div>
      </header>

      <?php if (empty($projects)): ?>
        <div class="proj-empty reveal-on-enter">
          <i class="fa-solid fa-code" aria-hidden="true"></i>
          <p>Projects are being updated. Check back shortly.</p>
          <a class="btn primary" href="<?php echo appUrl('public/contact.php'); ?>">Get in touch</a>
        </div>
      <?php else: ?>

        <?php if (count($allTags) > 1): ?>
          <div class="proj-filters reveal-on-enter" role="toolbar" aria-label="Filter projects by technology">
            <button type="button" class="proj-filter is-active" data-filter="all">All</button>
            <?php foreach ($allTags as $tag): ?>
              <button type="button" class="proj-filter" data-filter="<?php echo htmlspecialchars(strtolower($tag), ENT_QUOTES, 'UTF-8'); ?>">
                <?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?>
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div id="project-list" class="proj-list">
          <?php foreach ($projects as $index => $p):
            $pImg = project_resolve_image($p);
            $tagList = [];
            if (!empty($p['tags'])) {
              $tagList = array_values(array_filter(array_map('trim', explode(',', $p['tags']))));
            }
            $tagData = strtolower(implode(' ', $tagList));
            $num = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            $flip = ($index % 2 === 1) && !empty($pImg);
            $host = '';
            if (!empty($p['url'])) {
              $host = preg_replace('#^https?://#', '', rtrim($p['url'], '/'));
            }
            $slug = 'project-' . (int) $p['id'];
          ?>
            <article
              id="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>"
              class="proj-case reveal-on-enter<?php echo $flip ? ' proj-case--flip' : ''; ?><?php echo empty($pImg) ? ' proj-case--text' : ''; ?>"
              data-tags="<?php echo htmlspecialchars($tagData, ENT_QUOTES, 'UTF-8'); ?>"
            >
              <div class="proj-case__index" aria-hidden="true"><?php echo $num; ?></div>

              <div class="proj-case__grid">
                <?php if (!empty($pImg)): ?>
                  <div class="proj-case__media">
                    <div class="proj-mockup">
                      <div class="proj-mockup__bar">
                        <span class="proj-mockup__dots" aria-hidden="true"></span>
                        <span class="proj-mockup__url"><?php echo htmlspecialchars($host ?: 'tanuisila.dev', ENT_QUOTES, 'UTF-8'); ?></span>
                      </div>
                      <?php if (!empty($p['url'])): ?>
                        <a
                          class="proj-mockup__shot"
                          href="<?php echo htmlspecialchars($p['url'], ENT_QUOTES, 'UTF-8'); ?>"
                          target="_blank"
                          rel="noopener noreferrer"
                          title="Open <?php echo htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8'); ?>"
                        >
                      <?php else: ?>
                        <div class="proj-mockup__shot">
                      <?php endif; ?>
                          <div class="img-skeleton-wrap proj-mockup__frame">
                            <img
                              src="<?php echo htmlspecialchars($pImg, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo time(); ?>"
                              alt="<?php echo htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8'); ?> screenshot"
                              loading="lazy"
                              decoding="async"
                              onload="var w=this.closest('.img-skeleton-wrap'); if(w) w.classList.add('loaded')"
                            >
                          </div>
                      <?php if (!empty($p['url'])): ?>
                        </a>
                      <?php else: ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>

                <div class="proj-case__body">
                  <div class="proj-case__status">
                    <span class="proj-live"><i class="fa-solid fa-circle" aria-hidden="true"></i> Live</span>
                    <span class="proj-case__num">Project <?php echo $num; ?></span>
                  </div>

                  <h2 class="proj-case__title"><?php echo htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8'); ?></h2>

                  <p class="proj-case__desc">
                    <?php echo nl2br(htmlspecialchars($p['description'] ?? '', ENT_QUOTES, 'UTF-8')); ?>
                  </p>

                  <?php if (!empty($tagList)): ?>
                    <div class="proj-case__stack">
                      <span class="proj-case__stack-label">Stack</span>
                      <ul class="proj-tech">
                        <?php foreach ($tagList as $tag): ?>
                          <li><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                  <?php endif; ?>

                  <?php if (!empty($p['url'])): ?>
                    <div class="proj-case__actions">
                      <a
                        class="btn primary"
                        href="<?php echo htmlspecialchars($p['url'], ENT_QUOTES, 'UTF-8'); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        Launch live <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                      </a>
                      <span class="proj-case__host"><?php echo htmlspecialchars($host, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <p class="proj-filter-empty" id="proj-filter-empty" hidden>
          No projects match that filter. <button type="button" class="proj-filter-reset" data-filter="all">Show all</button>
        </p>

      <?php endif; ?>

      <aside class="proj-pipeline reveal-on-enter">
        <div class="proj-pipeline__mark" aria-hidden="true">
          <i class="fa-solid fa-layer-group"></i>
        </div>
        <div class="proj-pipeline__copy">
          <h2>Building what’s next</h2>
          <p>
            New production apps and tools ship regularly. If you have a problem worth engineering,
            let’s talk about scope and delivery.
          </p>
        </div>
        <a class="btn proj-pipeline__cta" href="<?php echo appUrl('public/contact.php'); ?>">
          Propose a project →
        </a>
      </aside>

    </div>
  </section>
</main>

<script>
(function () {
  var filters = document.querySelectorAll('.proj-filter, .proj-filter-reset');
  var cases = document.querySelectorAll('.proj-case');
  var emptyMsg = document.getElementById('proj-filter-empty');
  if (!filters.length || !cases.length) return;

  function applyFilter(value) {
    var visible = 0;
    cases.forEach(function (el) {
      var tags = (el.getAttribute('data-tags') || '');
      var show = value === 'all' || tags.indexOf(value) !== -1;
      el.classList.toggle('is-filtered-out', !show);
      if (show) visible++;
    });
    document.querySelectorAll('.proj-filter').forEach(function (btn) {
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
