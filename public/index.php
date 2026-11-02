<?php
$activePage = 'home';
require_once __DIR__ . '/header.php';

// Safe self-healing localized copy mechanism to handle server asset permissions
$sourceImg = __DIR__ . '/../IMG_20260625_115331.jpg';
$destImg   = __DIR__ . '/profile.jpg';
if (file_exists($sourceImg)) {
    if (!file_exists($destImg) || filesize($sourceImg) !== filesize($destImg)) {
        copy($sourceImg, $destImg);
        @copy($sourceImg, __DIR__ . '/../profile.jpg');
    }
}

$totalProjects     = 0;
$totalPublications = 0;
try {
    $totalProjects     = (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
    $totalPublications = (int) $pdo->query('SELECT COUNT(*) FROM publications')->fetchColumn();
} catch (Throwable $e) {}

$featuredPool = !empty($navProjects) ? $navProjects : ($projects ?? []);
$featuredProject = !empty($featuredPool) && is_array($featuredPool) ? $featuredPool[0] : null;
$workProjects = array_slice(is_array($featuredPool) ? $featuredPool : [], 0, 4);

$eduPreview = array_slice(is_array($education ?? null) ? $education : [], 0, 2);
$expPreview = array_slice(is_array($experience ?? null) ? $experience : [], 0, 2);
$skillPreview = array_slice(is_array($skills ?? null) ? $skills : [], 0, 8);

$latestPosts = [];
try {
    $latestPosts = $pdo->query("
        SELECT title, slug, excerpt, published_at
        FROM blog_posts
        WHERE status = 'published'
        ORDER BY published_at DESC, id DESC
        LIMIT 2
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $latestPosts = [];
}

$heroSummary = trim((string) (
    $settings['hero_text']
    ?: $profile['summary']
    ?: 'Curious about AI, research, and the many ways technology can be put to good use.'
));

function hp_project_image(array $p): string {
    if (empty($p['image'])) {
        return '';
    }
    $rawImg = trim((string) $p['image']);
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

function hp_short(string $text, int $max = 140): string {
    $text = preg_replace('/\s+/', ' ', trim($text)) ?? '';
    if (strlen($text) <= $max) {
        return $text;
    }
    return rtrim(substr($text, 0, $max - 3)) . '...';
}

function hp_edu_years(array $item): string {
    return trim(($item['start_year'] ?? '') . ' - ' . ($item['end_year'] ?? ''), ' -');
}

function hp_exp_when(array $item): string {
    $start = trim((string) ($item['start_date'] ?? ''));
    $end = trim((string) ($item['end_date'] ?? ''));
    $current = $end === '' || strcasecmp($end, 'present') === 0;
    return trim($start . ' - ' . ($current ? 'Present' : $end), ' -');
}
?>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- HERO                                                       -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section class="hp-hero">
  <div class="hp-hero__atmosphere" aria-hidden="true">
    <span class="hp-hero__orb hp-hero__orb--a"></span>
    <span class="hp-hero__orb hp-hero__orb--b"></span>
    <span class="hp-hero__grain"></span>
  </div>

  <div class="hp-hero__plane">
    <div class="container hp-hero__inner">

      <div class="hp-hero__copy">
        <p class="hp-hero__role reveal-on-enter">
          <span class="hp-hero__pulse" aria-hidden="true"></span>
          Computer Scientist
        </p>

        <h1 class="hp-hero__brand">
          <span class="hp-brand-line"><?php echo htmlspecialchars($profile['full_name']); ?></span>
        </h1>

        <p class="hp-hero__tagline">Building technology with purpose.</p>

        <p class="hp-hero__sub"><?php echo htmlspecialchars($heroSummary); ?></p>

        <div class="hp-hero__ctas">
          <a class="hp-cta hp-cta--primary" href="<?php echo appUrl('public/projects.php'); ?>">
            View Projects <span class="hp-cta-arrow">→</span>
          </a>
          <a class="hp-cta hp-cta--secondary" href="<?php echo appUrl('public/contact.php'); ?>">
            Get in Touch
          </a>
        </div>
      </div>

      <div class="hp-hero__visual">
        <div class="hp-photo-frame" id="hero-photo-wrap">
          <div class="hp-photo-accent" aria-hidden="true"></div>
          <div class="hp-photo-inner img-skeleton-wrap" id="hero-photo-skeleton">
            <img
              src="<?php echo appUrl('public/profile.jpg'); ?>?v=<?php echo @filemtime(__DIR__ . '/profile.jpg'); ?>"
              alt="<?php echo htmlspecialchars($profile['full_name']); ?>"
              class="hp-photo"
              fetchpriority="high"
              decoding="async"
              width="520"
              height="640"
              onload="this.closest('.img-skeleton-wrap').classList.add('loaded')"
            >
          </div>
          <p class="hp-photo-caption">
            <span>Portfolio</span>
            <span><?php echo (int) date('Y'); ?></span>
          </p>
        </div>
      </div>

    </div>
  </div>

  <div class="hp-scroll-indicator" aria-hidden="true">
    <span>Scroll</span>
    <span class="hp-scroll-line"></span>
  </div>
</section>

<main id="main-content">

  <!-- Site map / reflect other pages -->
  <section class="hp-map" aria-label="Explore the portfolio">
    <div class="container">
      <div class="hp-section-label">
        <span class="hp-section-label__text">Explore</span>
      </div>
      <div class="hp-map__head">
        <h2 class="hp-section-title">The rest of the story, one click away.</h2>
        <p class="hp-section-lede">Jump into education, experience, skills, writing, and contact - the same pages you will find in the navigation, introduced here with a bit of context.</p>
      </div>

      <div class="hp-map__grid">
        <a class="hp-map__item" href="<?php echo appUrl('public/education.php'); ?>">
          <span class="hp-map__kicker">Academic path</span>
          <strong>Education</strong>
          <span><?php echo max(count($education ?? []), 1); ?> credential<?php echo count($education ?? []) === 1 ? '' : 's'; ?> from foundations to graduate research.</span>
          <span class="hp-map__go">Open education →</span>
        </a>
        <a class="hp-map__item" href="<?php echo appUrl('public/experience.php'); ?>">
          <span class="hp-map__kicker">Professional path</span>
          <strong>Experience</strong>
          <span><?php echo max(count($experience ?? []), 1); ?> role<?php echo count($experience ?? []) === 1 ? '' : 's'; ?> across teaching, support, and customer success.</span>
          <span class="hp-map__go">Open experience →</span>
        </a>
        <a class="hp-map__item" href="<?php echo appUrl('public/projects.php'); ?>">
          <span class="hp-map__kicker">Selected work</span>
          <strong>Projects</strong>
          <span><?php echo max(count($navProjects ?: ($projects ?? [])), 1); ?> live builds with screenshots, stack notes, and launch links.</span>
          <span class="hp-map__go">Browse projects →</span>
        </a>
        <a class="hp-map__item" href="<?php echo appUrl('public/skills.php'); ?>">
          <span class="hp-map__kicker">Toolkit</span>
          <strong>Skills</strong>
          <span><?php echo max(count($skills ?? []), 1); ?> skills spanning languages, data, cloud, and delivery tools.</span>
          <span class="hp-map__go">View skills →</span>
        </a>
        <a class="hp-map__item" href="<?php echo appUrl('blog.php'); ?>">
          <span class="hp-map__kicker">Writing</span>
          <strong>Blog</strong>
          <span>Notes on reverse engineering, tooling, and how this portfolio is run.</span>
          <span class="hp-map__go">Read the blog →</span>
        </a>
        <a class="hp-map__item hp-map__item--accent" href="<?php echo appUrl('public/contact.php'); ?>">
          <span class="hp-map__kicker">Next step</span>
          <strong>Contact</strong>
          <span>Have a role, collaboration, or question? Send a message.</span>
          <span class="hp-map__go">Get in touch →</span>
        </a>
      </div>
    </div>
  </section>

  <!-- Path preview: Education + Experience -->
  <?php if ($eduPreview || $expPreview): ?>
  <section class="hp-path">
    <div class="container hp-path__inner">
      <div class="hp-path__col">
        <div class="hp-section-label">
          <span class="hp-section-label__text">Education</span>
        </div>
        <h2 class="hp-path__title">Academic path</h2>
        <ul class="hp-path__list">
          <?php foreach ($eduPreview as $item): ?>
            <li>
              <p class="hp-path__when"><?php echo htmlspecialchars(hp_edu_years($item), ENT_QUOTES, 'UTF-8'); ?></p>
              <h3><?php echo htmlspecialchars((string) ($item['degree'] ?? 'Degree'), ENT_QUOTES, 'UTF-8'); ?></h3>
              <p><?php echo htmlspecialchars((string) ($item['institution'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
        <a class="hp-text-link" href="<?php echo appUrl('public/education.php'); ?>">Full education timeline <span>→</span></a>
      </div>

      <div class="hp-path__col">
        <div class="hp-section-label">
          <span class="hp-section-label__text">Experience</span>
        </div>
        <h2 class="hp-path__title">Professional path</h2>
        <ul class="hp-path__list">
          <?php foreach ($expPreview as $item): ?>
            <li>
              <p class="hp-path__when"><?php echo htmlspecialchars(hp_exp_when($item), ENT_QUOTES, 'UTF-8'); ?></p>
              <h3><?php echo htmlspecialchars((string) ($item['role_name'] ?? 'Role'), ENT_QUOTES, 'UTF-8'); ?></h3>
              <p><?php echo htmlspecialchars((string) ($item['company'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
            </li>
          <?php endforeach; ?>
        </ul>
        <a class="hp-text-link" href="<?php echo appUrl('public/experience.php'); ?>">Full experience timeline <span>→</span></a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Projects -->
  <?php if ($workProjects): ?>
  <section class="hp-work">
    <div class="container">
      <div class="hp-work__header">
        <div>
          <div class="hp-section-label">
            <span class="hp-section-label__text">Selected work</span>
          </div>
          <h2 class="hp-section-title">Live projects worth opening.</h2>
          <p class="hp-section-lede">A short cut into the gallery - each one is hosted and ready to explore.</p>
        </div>
        <a class="hp-cta hp-cta--primary" href="<?php echo appUrl('public/projects.php'); ?>">
          All projects <span class="hp-cta-arrow">→</span>
        </a>
      </div>

      <div class="hp-work__rail">
        <?php foreach ($workProjects as $i => $proj):
          $img = hp_project_image($proj);
          $url = trim((string) ($proj['url'] ?? ''));
          $href = $url !== '' ? $url : appUrl('public/projects.php');
          $external = $url !== '';
        ?>
          <a
            class="hp-work__card"
            href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>"
            <?php if ($external): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
          >
            <div class="hp-work__shot<?php echo $img ? '' : ' hp-work__shot--empty'; ?> img-skeleton-wrap">
              <?php if ($img): ?>
                <img
                  src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>"
                  alt=""
                  loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>"
                  decoding="async"
                  onload="this.closest('.img-skeleton-wrap').classList.add('loaded')"
                >
              <?php else: ?>
                <?php echo media_placeholder($proj['title'] ?? 'Project', 'media-ph--project'); ?>
              <?php endif; ?>
            </div>
            <div class="hp-work__meta">
              <strong><?php echo htmlspecialchars((string) ($proj['title'] ?? 'Project'), ENT_QUOTES, 'UTF-8'); ?></strong>
              <?php if (!empty($proj['description'])): ?>
                <span><?php echo htmlspecialchars(hp_short((string) $proj['description'], 110), ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endif; ?>
              <em><?php echo $external ? 'Launch live →' : 'View in gallery →'; ?></em>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php elseif ($featuredProject):
    $fpImg = hp_project_image($featuredProject);
  ?>
  <section class="hp-featured">
    <div class="container hp-featured__inner">
      <div class="hp-featured__copy">
        <div class="hp-section-label">
          <span class="hp-section-label__text">Selected work</span>
        </div>
        <h2 class="hp-featured__title"><?php echo htmlspecialchars($featuredProject['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <?php if (!empty($featuredProject['description'])): ?>
          <p class="hp-featured__desc"><?php echo htmlspecialchars(hp_short((string) $featuredProject['description'], 220), ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <div class="hp-featured__actions">
          <?php if (!empty($featuredProject['url'])): ?>
            <a class="hp-cta hp-cta--primary" href="<?php echo htmlspecialchars($featuredProject['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
              Launch live <span class="hp-cta-arrow">→</span>
            </a>
          <?php endif; ?>
          <a class="hp-text-link" href="<?php echo appUrl('public/projects.php'); ?>">All projects <span>→</span></a>
        </div>
      </div>
      <div class="hp-featured__media">
        <?php if ($fpImg): ?>
          <div class="hp-featured__shot img-skeleton-wrap">
            <img
              src="<?php echo htmlspecialchars($fpImg, ENT_QUOTES, 'UTF-8'); ?>"
              alt="<?php echo htmlspecialchars($featuredProject['title'], ENT_QUOTES, 'UTF-8'); ?> preview"
              loading="lazy"
              decoding="async"
              onload="this.closest('.img-skeleton-wrap').classList.add('loaded')"
            >
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Skills -->
  <?php if ($skillPreview): ?>
  <section class="hp-skills">
    <div class="container hp-skills__inner">
      <div class="hp-skills__copy">
        <div class="hp-section-label">
          <span class="hp-section-label__text">Skills</span>
        </div>
        <h2 class="hp-section-title">A practical toolkit.</h2>
        <p class="hp-section-lede">Languages, data, cloud, and delivery tools I use when shipping systems and teaching others to do the same.</p>
        <a class="hp-text-link" href="<?php echo appUrl('public/skills.php'); ?>">See full skills list <span>→</span></a>
      </div>
      <ul class="hp-skills__cloud" aria-label="Highlighted skills">
        <?php foreach ($skillPreview as $skill): ?>
          <li>
            <span><?php echo htmlspecialchars((string) ($skill['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
            <?php if (!empty($skill['level'])): ?>
              <small><?php echo htmlspecialchars((string) $skill['level'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
  <?php endif; ?>

  <!-- Blog + About -->
  <section class="hp-signal">
    <div class="container hp-signal__inner">
      <?php if ($latestPosts): ?>
      <div class="hp-signal__col">
        <div class="hp-section-label">
          <span class="hp-section-label__text">From the blog</span>
        </div>
        <h2 class="hp-path__title">Recent writing</h2>
        <ul class="hp-blog-list">
          <?php foreach ($latestPosts as $post): ?>
            <li>
              <a href="<?php echo appUrl('post.php?slug=' . urlencode((string) $post['slug'])); ?>">
                <strong><?php echo htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <?php if (!empty($post['excerpt'])): ?>
                  <span><?php echo htmlspecialchars(hp_short((string) $post['excerpt'], 120), ENT_QUOTES, 'UTF-8'); ?></span>
                <?php elseif (!empty($post['published_at'])): ?>
                  <span><?php echo htmlspecialchars(date('M j, Y', strtotime((string) $post['published_at'])), ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <a class="hp-text-link" href="<?php echo appUrl('blog.php'); ?>">All posts <span>→</span></a>
      </div>
      <?php endif; ?>

      <div class="hp-signal__col">
        <div class="hp-section-label">
          <span class="hp-section-label__text">About</span>
        </div>
        <h2 class="hp-path__title">Who I am</h2>
        <p class="hp-about-snippet">
          I'm a Computer Scientist, Software Developer, Researcher, and Technology Innovator.
          This site gathers projects, research, academic work, and ideas into one place -
          where I've been, and where I'm headed.
        </p>
        <div class="hp-signal__actions">
          <a class="hp-text-link" href="<?php echo appUrl('public/about.php'); ?>">Read full bio <span>→</span></a>
          <a class="hp-text-link" href="<?php echo appUrl('public/Sila_Kipng\'etich_Tanui_CV.pdf'); ?>" target="_blank" rel="noopener noreferrer">Download CV <span>→</span></a>
        </div>
        <p class="hp-signal__stats">
          <span><strong><?php echo max($totalProjects, 1); ?>+</strong> projects</span>
          <span><strong><?php echo max($totalPublications, 1); ?>+</strong> publications</span>
          <span><strong><?php echo max(count($experience ?? []), 1); ?></strong> roles</span>
        </p>
      </div>
    </div>
  </section>

  <!-- Closing CTA -->
  <section class="hp-cta-strip">
    <div class="container hp-cta-strip__inner">
      <div class="hp-cta-strip__copy">
        <p class="hp-cta-strip__kicker">Next step</p>
        <h2 class="hp-cta-strip__heading">Let's build something purposeful together.</h2>
        <p class="hp-cta-strip__sub">Whether you are hiring, collaborating, or curious about the work, I would love to hear from you.</p>
        <div class="hp-cta-strip__meta">
          <a href="<?php echo appUrl('public/projects.php'); ?>">Projects</a>
          <a href="<?php echo appUrl('public/experience.php'); ?>">Experience</a>
          <a href="<?php echo appUrl('now.php'); ?>">Now</a>
          <a href="<?php echo appUrl('lab.php'); ?>">Lab</a>
        </div>
      </div>
      <div class="hp-cta-strip__actions">
        <a class="hp-cta hp-cta--inverted" href="<?php echo appUrl('public/contact.php'); ?>">
          Get in Touch <span class="hp-cta-arrow">→</span>
        </a>
        <a class="hp-cta hp-cta--ghost-light" href="<?php echo appUrl('public/Sila_Kipng\'etich_Tanui_CV.pdf'); ?>" target="_blank" rel="noopener noreferrer">
          <i class="fa-solid fa-file-pdf"></i> Download CV
        </a>
      </div>
    </div>
  </section>
</main>

<style>
/* ── HERO ───────────────────────────────────────────────── */
.hp-hero {
  position: relative;
  min-height: calc(100vh - 116px);
  display: flex;
  flex-direction: column;
  justify-content: center;
  overflow: hidden;
  border-bottom: 1px solid var(--light-gray);
  background:
    linear-gradient(135deg, rgba(255, 247, 237, 0.9) 0%, transparent 42%),
    linear-gradient(180deg, #ffffff 0%, #f8fafc 55%, #ffffff 100%);
}

.hp-hero__atmosphere {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}

.hp-hero__orb {
  position: absolute;
  border-radius: 50% !important;
  filter: blur(8px);
}

.hp-hero__orb--a {
  width: min(52vw, 560px);
  height: min(52vw, 560px);
  top: -18%;
  left: -8%;
  background: radial-gradient(circle, rgba(234, 88, 12, 0.18) 0%, transparent 68%);
  animation: hp-orb-drift 14s ease-in-out infinite alternate;
}

.hp-hero__orb--b {
  width: min(40vw, 420px);
  height: min(40vw, 420px);
  right: 8%;
  bottom: -12%;
  background: radial-gradient(circle, rgba(15, 23, 42, 0.08) 0%, transparent 70%);
  animation: hp-orb-drift 18s ease-in-out infinite alternate-reverse;
}

.hp-hero__grain {
  position: absolute;
  inset: 0;
  opacity: 0.35;
  background-image:
    linear-gradient(rgba(15, 23, 42, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15, 23, 42, 0.03) 1px, transparent 1px);
  background-size: 56px 56px;
  mask-image: radial-gradient(ellipse 70% 55% at 30% 40%, #000 15%, transparent 72%);
}

@keyframes hp-orb-drift {
  from { transform: translate3d(0, 0, 0) scale(1); }
  to { transform: translate3d(18px, 24px, 0) scale(1.06); }
}

.hp-hero__plane {
  position: relative;
  z-index: 1;
  padding: clamp(40px, 6vw, 72px) 0 clamp(36px, 5vw, 56px);
}

.hp-hero__inner {
  display: grid;
  grid-template-columns: 1.05fr 0.95fr;
  gap: clamp(28px, 4vw, 56px);
  align-items: stretch;
  min-height: min(66vh, 620px);
}

.hp-hero__copy {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 14px;
  max-width: 640px;
}

.hp-hero__role {
  margin: 0;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: var(--text-meta, 0.72rem);
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--accent);
}

.hp-hero__pulse {
  width: 8px;
  height: 8px;
  background: var(--accent);
  box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.45);
  animation: hp-pulse 2.2s ease-out infinite;
}

@keyframes hp-pulse {
  0% { box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.45); }
  70% { box-shadow: 0 0 0 10px rgba(234, 88, 12, 0); }
  100% { box-shadow: 0 0 0 0 rgba(234, 88, 12, 0); }
}

.hp-hero__brand {
  margin: 0;
  font-size: clamp(2.7rem, 6.8vw, 4.8rem);
  line-height: 0.92;
  letter-spacing: -0.055em;
  font-weight: 800;
  color: var(--text);
}

.hp-brand-line {
  display: block;
  font-family: var(--font-brand-name);
  opacity: 0;
  transform: translateY(22px);
  animation: hp-slide-up 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.05s forwards;
}

.hp-hero__tagline {
  margin: 4px 0 0;
  font-size: clamp(1.15rem, 2.2vw, 1.55rem);
  font-weight: 600;
  letter-spacing: -0.02em;
  line-height: 1.25;
  color: var(--accent);
  opacity: 0;
  transform: translateY(16px);
  animation: hp-slide-up 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.18s forwards;
}

@keyframes hp-slide-up {
  to { opacity: 1; transform: translateY(0); }
}

.hp-hero__sub {
  margin: 0;
  max-width: 34rem;
  color: var(--muted);
  font-size: clamp(1rem, 1.4vw, 1.08rem);
  line-height: 1.55;
  opacity: 0;
  animation: hp-slide-up 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.32s forwards;
}

.hp-hero__ctas {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
  margin-top: 8px;
  opacity: 0;
  animation: hp-slide-up 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.46s forwards;
}

.hp-cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 24px;
  font-size: 0.8rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  text-decoration: none;
  transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
  white-space: nowrap;
}

.hp-cta--primary {
  background: var(--text);
  color: #fff;
  border: 1px solid var(--text);
}

.hp-cta--primary:hover {
  background: var(--accent);
  border-color: var(--accent);
  opacity: 1;
  transform: translateY(-2px);
}

.hp-cta--secondary {
  background: rgba(255, 255, 255, 0.72);
  color: var(--text);
  border: 1px solid rgba(15, 23, 42, 0.16);
  backdrop-filter: blur(6px);
}

.hp-cta--secondary:hover {
  border-color: rgba(234, 88, 12, 0.45);
  color: var(--accent);
  opacity: 1;
}

.hp-cta--inverted {
  background: #fff;
  color: var(--text);
  border: 1px solid #fff;
}

.hp-cta--inverted:hover {
  background: var(--accent);
  border-color: var(--accent);
  color: #fff;
  opacity: 1;
}

.hp-cta--ghost-light {
  background: transparent;
  color: rgba(255, 255, 255, 0.82);
  border: 1px solid rgba(255, 255, 255, 0.28);
}

.hp-cta--ghost-light:hover { opacity: 0.75; }

.hp-cta-arrow {
  display: inline-block;
  transition: transform 0.2s ease;
}

.hp-cta:hover .hp-cta-arrow { transform: translateX(4px); }

.hp-hero__visual {
  display: flex;
  align-items: stretch;
  justify-content: flex-end;
  opacity: 0;
  animation: hp-fade-in 0.9s ease 0.28s forwards;
  min-width: 0;
}

@keyframes hp-fade-in {
  to { opacity: 1; }
}

.hp-photo-frame {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: min(66vh, 620px);
}

.hp-photo-accent {
  position: absolute;
  inset: 18px -14px -14px 18px;
  border: 1px solid rgba(234, 88, 12, 0.35);
  z-index: 0;
  pointer-events: none;
}

.hp-photo-inner {
  position: relative;
  z-index: 1;
  width: 100%;
  height: 100%;
  min-height: inherit;
  overflow: hidden;
  background: #e2e8f0;
  border: 1px solid rgba(15, 23, 42, 0.12);
  box-shadow: 0 28px 60px rgba(15, 23, 42, 0.12);
}

.hp-photo {
  width: 100%;
  height: 100%;
  min-height: inherit;
  object-fit: cover;
  object-position: top center;
  display: block;
  filter: saturate(0.92) contrast(1.02);
  transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1), filter 0.4s ease;
}

.hp-photo-frame:hover .hp-photo {
  transform: scale(1.03);
  filter: saturate(1) contrast(1.04);
}

.hp-photo-caption {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 2;
  margin: 0;
  display: flex;
  justify-content: space-between;
  padding: 12px 16px;
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #fff;
  background: linear-gradient(180deg, transparent, rgba(15, 23, 42, 0.72));
  pointer-events: none;
}

.hp-scroll-indicator {
  position: absolute;
  bottom: 22px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  font-size: 0.66rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--muted);
  opacity: 0;
  animation: hp-fade-in 1s ease 1.1s forwards;
  z-index: 2;
}

.hp-scroll-line {
  display: block;
  width: 1px;
  height: 36px;
  background: linear-gradient(to bottom, var(--accent), transparent);
  animation: hp-line-grow 1.5s ease-in-out infinite;
}

@keyframes hp-line-grow {
  0% { transform: scaleY(0); transform-origin: top; opacity: 0; }
  50% { transform: scaleY(1); transform-origin: top; opacity: 1; }
  100% { transform: scaleY(1); transform-origin: bottom; opacity: 0; }
}

/* Shared */
.hp-section-label {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.hp-section-label__text {
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--accent);
}

.hp-section-label::after {
  content: '';
  display: block;
  flex: 1;
  height: 1px;
  background: var(--light-gray);
  max-width: 72px;
}

.hp-section-title {
  margin: 0 0 10px;
  font-size: clamp(1.7rem, 3vw, 2.5rem);
  font-weight: 800;
  letter-spacing: -0.045em;
  line-height: 1.05;
  color: var(--text);
}

.hp-section-lede {
  margin: 0;
  max-width: 46ch;
  font-size: 1.02rem;
  line-height: 1.55;
  color: var(--muted);
}

.hp-text-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text);
  border-bottom: 2px solid var(--accent);
  padding-bottom: 2px;
  text-decoration: none;
  transition: color 0.2s ease;
}

.hp-text-link:hover {
  color: var(--accent);
  opacity: 1;
}

/* Explore map */
.hp-map {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
  background:
    radial-gradient(ellipse 40% 50% at 0% 0%, rgba(234, 88, 12, 0.05), transparent 60%),
    #fff;
}

.hp-map__head {
  margin-bottom: 28px;
  max-width: 40rem;
}

.hp-map__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1px;
  background: var(--light-gray);
  border: 1px solid var(--light-gray);
}

.hp-map__item {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 26px 24px;
  background: #fff;
  text-decoration: none;
  color: inherit;
  min-height: 180px;
  transition: background 0.25s ease, transform 0.25s ease;
}

.hp-map__item:hover {
  background: rgba(255, 247, 237, 0.85);
  opacity: 1;
}

.hp-map__item--accent {
  background: linear-gradient(160deg, #fff7ed 0%, #fff 55%);
}

.hp-map__kicker {
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--accent);
}

.hp-map__item strong {
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: var(--text);
}

.hp-map__item > span:not(.hp-map__kicker):not(.hp-map__go) {
  font-size: 0.92rem;
  line-height: 1.45;
  color: var(--muted);
  flex: 1;
}

.hp-map__go {
  margin-top: 8px;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--text);
  border-bottom: 1px solid rgba(234, 88, 12, 0.45);
  align-self: flex-start;
  padding-bottom: 2px;
  transition: color 0.2s ease, transform 0.2s ease;
}

.hp-map__item:hover .hp-map__go {
  color: var(--accent);
  transform: translateX(3px);
}

/* Path preview */
.hp-path {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
  background: #f8fafc;
}

.hp-path__inner {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: clamp(28px, 5vw, 56px);
}

.hp-path__title {
  margin: 0 0 22px;
  font-size: clamp(1.5rem, 2.4vw, 2rem);
  font-weight: 800;
  letter-spacing: -0.04em;
  color: var(--text);
}

.hp-path__list {
  list-style: none;
  margin: 0 0 22px;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0;
}

.hp-path__list li {
  padding: 16px 0;
  border-top: 1px solid var(--light-gray);
}

.hp-path__list li:last-child {
  border-bottom: 1px solid var(--light-gray);
}

.hp-path__when {
  margin: 0 0 6px;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--accent);
}

.hp-path__list h3 {
  margin: 0 0 4px;
  font-size: 1.05rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text);
}

.hp-path__list p {
  margin: 0;
  font-size: 0.92rem;
  color: var(--muted);
}

/* Work rail */
.hp-work {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
}

.hp-work__header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  margin-bottom: 28px;
  flex-wrap: wrap;
}

.hp-work__rail {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.hp-work__card {
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: inherit;
  min-width: 0;
  transition: transform 0.25s ease;
}

.hp-work__card:hover {
  transform: translateY(-4px);
  opacity: 1;
}

.hp-work__shot {
  aspect-ratio: 16 / 10;
  overflow: hidden;
  border: 1px solid rgba(15, 23, 42, 0.1);
  background: #0f172a;
  margin-bottom: 14px;
}

.hp-work__shot img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: top center;
  transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
}

.hp-work__card:hover .hp-work__shot img {
  transform: scale(1.04);
}

.hp-work__shot--empty {
  display: grid;
  place-items: center;
  background: var(--soft-bg);
}

.hp-work__meta {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.hp-work__meta strong {
  font-size: 1.02rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text);
}

.hp-work__meta span {
  font-size: 0.88rem;
  line-height: 1.4;
  color: var(--muted);
}

.hp-work__meta em {
  margin-top: 4px;
  font-style: normal;
  font-size: 0.74rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--accent);
}

/* Featured fallback */
.hp-featured {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
}

.hp-featured__inner {
  display: grid;
  grid-template-columns: 0.9fr 1.1fr;
  gap: clamp(28px, 4vw, 56px);
  align-items: center;
}

.hp-featured__title {
  margin: 0 0 14px;
  font-size: clamp(1.7rem, 3vw, 2.5rem);
  font-weight: 800;
  letter-spacing: -0.045em;
  line-height: 1.05;
  color: var(--text);
}

.hp-featured__desc {
  margin: 0 0 22px;
  max-width: 38ch;
  font-size: 1.02rem;
  line-height: 1.6;
  color: var(--muted);
}

.hp-featured__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 16px 20px;
}

.hp-featured__shot {
  overflow: hidden;
  border: 1px solid rgba(15, 23, 42, 0.1);
  background: #0f172a;
  aspect-ratio: 16 / 10;
}

.hp-featured__shot img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: top center;
}

/* Skills */
.hp-skills {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
  background:
    linear-gradient(180deg, #fff 0%, #fff7ed 100%);
}

.hp-skills__inner {
  display: grid;
  grid-template-columns: 0.9fr 1.1fr;
  gap: clamp(28px, 4vw, 48px);
  align-items: center;
}

.hp-skills__cloud {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.hp-skills__cloud li {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 12px 16px;
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.1);
  min-width: 118px;
}

.hp-skills__cloud span {
  font-size: 0.92rem;
  font-weight: 800;
  color: var(--text);
}

.hp-skills__cloud small {
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--muted);
}

/* Blog + about */
.hp-signal {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
}

.hp-signal__inner {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: clamp(28px, 5vw, 56px);
}

.hp-blog-list {
  list-style: none;
  margin: 0 0 22px;
  padding: 0;
}

.hp-blog-list li {
  border-top: 1px solid var(--light-gray);
}

.hp-blog-list li:last-child {
  border-bottom: 1px solid var(--light-gray);
}

.hp-blog-list a {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 16px 0;
  text-decoration: none;
  color: inherit;
  transition: color 0.2s ease;
}

.hp-blog-list a:hover {
  opacity: 1;
}

.hp-blog-list a:hover strong {
  color: var(--accent);
}

.hp-blog-list strong {
  font-size: 1.05rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text);
}

.hp-blog-list span {
  font-size: 0.9rem;
  line-height: 1.4;
  color: var(--muted);
}

.hp-about-snippet {
  margin: 0 0 20px;
  font-size: 1.02rem;
  line-height: 1.55;
  color: var(--muted);
  max-width: 42ch;
}

.hp-signal__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 14px 22px;
  margin-bottom: 22px;
}

.hp-signal__stats {
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 10px 18px;
  font-size: 0.82rem;
  color: var(--muted);
}

.hp-signal__stats strong {
  color: var(--text);
  font-weight: 800;
}

/* Closing CTA */
.hp-cta-strip {
  position: relative;
  overflow: hidden;
  background:
    radial-gradient(ellipse 50% 80% at 100% 0%, rgba(234, 88, 12, 0.28), transparent 55%),
    var(--text);
  padding: clamp(56px, 8vw, 96px) 0;
}

.hp-cta-strip__inner {
  display: grid;
  grid-template-columns: 1.2fr auto;
  gap: 40px;
  align-items: center;
  position: relative;
  z-index: 1;
}

.hp-cta-strip__kicker {
  margin: 0 0 10px;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #fdba74;
}

.hp-cta-strip__heading {
  margin: 0 0 12px;
  font-size: clamp(1.6rem, 2.8vw, 2.35rem);
  font-weight: 800;
  letter-spacing: -0.04em;
  color: #fff;
  line-height: 1.08;
}

.hp-cta-strip__sub {
  margin: 0;
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.65);
  line-height: 1.55;
  max-width: 42ch;
}

.hp-cta-strip__meta {
  margin-top: 22px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px 18px;
}

.hp-cta-strip__meta a {
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  color: rgba(255, 255, 255, 0.55);
  text-decoration: none;
  border-bottom: 1px solid transparent;
}

.hp-cta-strip__meta a:hover {
  color: #fdba74;
  border-bottom-color: rgba(253, 186, 116, 0.5);
  opacity: 1;
}

.hp-cta-strip__actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
  align-items: flex-end;
}

/* Responsive */
@media (max-width: 1100px) {
  .hp-map__grid { grid-template-columns: 1fr 1fr; }
  .hp-work__rail { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 900px) {
  .hp-hero { min-height: 0; }

  .hp-hero__inner {
    grid-template-columns: 1fr;
    gap: 28px;
    min-height: 0;
  }

  .hp-hero__visual { order: -1; justify-content: stretch; }

  .hp-photo-frame,
  .hp-photo-inner,
  .hp-photo {
    min-height: min(58vw, 380px);
    height: min(58vw, 380px);
  }

  .hp-photo-accent { display: none; }

  .hp-path__inner,
  .hp-skills__inner,
  .hp-signal__inner,
  .hp-featured__inner {
    grid-template-columns: 1fr;
  }

  .hp-cta-strip__inner {
    grid-template-columns: 1fr;
    gap: 28px;
  }

  .hp-cta-strip__actions {
    align-items: flex-start;
    flex-direction: row;
    flex-wrap: wrap;
  }

  .hp-scroll-indicator { display: none; }
}

@media (max-width: 640px) {
  .hp-map__grid,
  .hp-work__rail {
    grid-template-columns: 1fr;
  }

  .hp-cta { width: 100%; justify-content: center; }

  .hp-work__header .hp-cta { width: auto; }
}

@media (prefers-reduced-motion: reduce) {
  .hp-brand-line,
  .hp-hero__tagline,
  .hp-hero__sub,
  .hp-hero__ctas,
  .hp-hero__visual,
  .hp-scroll-indicator,
  .hp-hero__orb--a,
  .hp-hero__orb--b { animation: none; opacity: 1; transform: none; }

  .hp-scroll-line,
  .hp-hero__pulse { animation: none; }

  .hp-photo,
  .hp-work__shot img,
  .hp-work__card,
  .hp-cta { transition: none; }
}
</style>

<?php
require_once __DIR__ . '/footer.php';
?>
