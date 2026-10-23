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

// Fetch quick stats for the hero
$totalProjects     = 0;
$totalPublications = 0;
$totalExperience   = 0;
try {
    $totalProjects     = (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
    $totalPublications = (int) $pdo->query('SELECT COUNT(*) FROM publications')->fetchColumn();
    $totalExperience   = (int) $pdo->query('SELECT COUNT(*) FROM experience')->fetchColumn();
} catch (Throwable $e) {}
?>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- HERO - BRAND-FIRST COMPOSITION                             -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section class="hp-hero">
  <div class="hp-hero__bg-grid" aria-hidden="true"></div>

  <div class="hp-hero__plane">
    <div class="container hp-hero__inner">

      <div class="hp-hero__copy">
        <p class="hp-hero__role reveal-on-enter">Computer Scientist</p>

        <h1 class="hp-hero__brand">
          <span class="hp-brand-line"><?php echo htmlspecialchars($profile['full_name']); ?></span>
        </h1>

        <p class="hp-hero__tagline">Building technology with purpose.</p>

        <p class="hp-hero__sub">
          <?php echo htmlspecialchars(
            $settings['hero_text']
            ?: $profile['summary']
            ?: 'Curious about AI, research, and the many ways technology can be put to good use.'
          ); ?>
        </p>

        <div class="hp-hero__ctas">
          <a class="hp-cta hp-cta--primary" href="<?php echo appUrl('public/projects.php'); ?>">
            View Projects <span class="hp-cta-arrow">→</span>
          </a>
          <a class="hp-cta hp-cta--secondary" href="<?php echo appUrl('public/contact.php'); ?>">
            Get in Touch
          </a>
        </div>
      </div>

      <div class="hp-hero__visual" aria-hidden="false">
        <div class="hp-photo-frame" id="hero-photo-wrap">
          <div class="hp-photo-inner img-skeleton-wrap" id="hero-photo-skeleton">
            <picture>
              <source srcset="<?php echo appUrl('public/profile.webp'); ?>?v=<?php echo @filemtime(__DIR__ . '/profile.webp'); ?>" type="image/webp">
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
            </picture>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="hp-scroll-indicator" aria-hidden="true">
    <span>Scroll</span>
    <span class="hp-scroll-line"></span>
  </div>
</section>

<!-- Focus strip: chips + stats below the first viewport -->
<section class="hp-focus-strip reveal-on-enter" aria-label="Focus areas and highlights">
  <div class="container hp-focus-strip__inner">
    <div class="hp-role-chips" role="list">
      <span class="hp-chip" role="listitem">Software Development</span>
      <span class="hp-chip" role="listitem">Artificial Intelligence</span>
      <span class="hp-chip" role="listitem">System Architecture</span>
      <span class="hp-chip" role="listitem">Technology Innovation</span>
    </div>
    <div class="hp-stats-row">
      <div class="hp-stat">
        <span class="hp-stat__num"><?php echo max($totalProjects, 1); ?>+</span>
        <span class="hp-stat__label">Projects</span>
      </div>
      <div class="hp-stat">
        <span class="hp-stat__num"><?php echo max($totalPublications, 3); ?>+</span>
        <span class="hp-stat__label">Publications</span>
      </div>
      <div class="hp-stat">
        <span class="hp-stat__num">5+</span>
        <span class="hp-stat__label">Yrs Experience</span>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- QUICK-NAV STRIP                                            -->
<!-- ═══════════════════════════════════════════════════════════ -->
<nav class="hp-quicknav" aria-label="Quick site navigation">
  <div class="container hp-quicknav__inner">
    <a class="hp-qn-link" href="<?php echo appUrl('public/about.php'); ?>">
      <i class="fa-solid fa-user-circle"></i> About
    </a>
    <a class="hp-qn-link" href="<?php echo appUrl('public/education.php'); ?>">
      <i class="fa-solid fa-graduation-cap"></i> Education
    </a>
    <a class="hp-qn-link" href="<?php echo appUrl('public/experience.php'); ?>">
      <i class="fa-solid fa-briefcase"></i> Experience
    </a>
    <a class="hp-qn-link" href="<?php echo appUrl('public/skills.php'); ?>">
      <i class="fa-solid fa-layer-group"></i> Skills
    </a>
    <a class="hp-qn-link" href="<?php echo appUrl('public/projects.php'); ?>">
      <i class="fa-solid fa-code"></i> Projects
    </a>
    <a class="hp-qn-link" href="<?php echo appUrl('public/blog.php'); ?>">
      <i class="fa-solid fa-pen-nib"></i> Blog
    </a>
    <a class="hp-qn-link hp-qn-link--cta" href="<?php echo appUrl('public/contact.php'); ?>">
      <i class="fa-solid fa-paper-plane"></i> Get in Touch
    </a>
  </div>
</nav>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- DISCOVER — Lab / SaaS / Interests                           -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section class="hp-about" style="padding-top:48px;padding-bottom:48px;border-bottom:1px solid var(--light-gray);">
  <div class="container">
    <div class="hp-section-label">
      <span class="hp-section-label__text">Discover</span>
    </div>
    <h2 class="hp-specialties__title" style="margin-bottom:24px;">Lab, products, and interests</h2>
    <div class="feature-grid">
      <article class="feature-tile reveal-on-enter">
        <div class="feature-tile__icon"><i class="fa-solid fa-flask"></i></div>
        <h3>The Lab</h3>
        <p>Experiments, Byte Lab tools, and workshop notes.</p>
        <a class="tile-link" href="<?php echo appUrl('lab.php'); ?>">Enter Lab →</a>
      </article>
      <article class="feature-tile reveal-on-enter">
        <div class="feature-tile__icon"><i class="fa-solid fa-rocket"></i></div>
        <h3>SaaS bench</h3>
        <p>CV Studio and other product experiments shipping in public.</p>
        <a class="tile-link" href="<?php echo appUrl('saas.php'); ?>">View products →</a>
      </article>
      <article class="feature-tile reveal-on-enter">
        <div class="feature-tile__icon"><i class="fa-solid fa-microchip"></i></div>
        <h3>Reverse engineering</h3>
        <p>Interest area: formats, binaries, and careful analysis.</p>
        <a class="tile-link" href="<?php echo appUrl('reverse-engineering.php'); ?>">Explore →</a>
      </article>
      <article class="feature-tile reveal-on-enter">
        <div class="feature-tile__icon"><i class="fa-solid fa-bolt"></i></div>
        <h3>Now</h3>
        <p>What is being built, learned, and read this month.</p>
        <a class="tile-link" href="<?php echo appUrl('now.php'); ?>">See now →</a>
      </article>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- ABOUT SNAPSHOT SECTION                                     -->
<!-- ═══════════════════════════════════════════════════════════ -->
<main id="main-content">
<section class="hp-about">
  <div class="container hp-about__inner">

    <div class="hp-section-label">
      <span class="hp-section-label__text">Who I Am</span>
    </div>

    <div class="hp-about__content">
      <h2 class="hp-about__heading">A place for the things I build, learn, and explore.</h2>
      <div class="hp-about__body">
        <p>I'm a Computer Scientist, Software Developer, Researcher, and Technology Innovator. I built this site to bring together software projects, research, academic work, and ideas - a single space that reflects where I've been and where I'm headed.</p>
        <p>I enjoy taking complex problems, breaking them apart, and turning them into working, purposeful technology. Whether it's a machine learning system, a full-stack web application, or a research visualization, my approach is always: <strong>understand the problem, explore the possibilities, build the solution.</strong></p>
      </div>
      <a class="hp-text-link" href="<?php echo appUrl('public/about.php'); ?>">
        Read Full Bio <span>→</span>
      </a>
    </div>

  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- SPECIALTIES FEATURE CARDS                                  -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section class="hp-specialties">
  <div class="container">

    <div class="hp-specialties__header">
      <div class="hp-section-label">
        <span class="hp-section-label__text">Core Specialties</span>
      </div>
      <h2 class="hp-specialties__title">What I do best</h2>
    </div>

    <div class="hp-cards-grid">

      <article class="hp-card">
        <div class="hp-card__icon-wrap" aria-hidden="true">
          <i class="fa-solid fa-code hp-card__icon-fa"></i>
        </div>
        <div class="hp-card__num">01</div>
        <h3 class="hp-card__title">Software Development</h3>
        <p class="hp-card__body">Full-stack web applications, APIs, database-driven systems, and scalable software solutions.</p>
        <ul class="hp-card__tags">
          <li><i class="fa-brands fa-python"></i> Python</li>
          <li><i class="fa-brands fa-php"></i> PHP</li>
          <li><i class="fa-brands fa-js"></i> JavaScript</li>
          <li><i class="fa-solid fa-database"></i> MySQL</li>
        </ul>
        <a class="hp-card__link" href="<?php echo appUrl('public/projects.php'); ?>">
          <i class="fa-solid fa-arrow-right"></i> See Projects
        </a>
      </article>

      <article class="hp-card">
        <div class="hp-card__icon-wrap" aria-hidden="true">
          <i class="fa-solid fa-robot hp-card__icon-fa"></i>
        </div>
        <div class="hp-card__num">02</div>
        <h3 class="hp-card__title">AI &amp; Machine Learning</h3>
        <p class="hp-card__body">Neural networks, regression models, clustering, SVMs, and scientific data analysis pipelines.</p>
        <ul class="hp-card__tags">
          <li><i class="fa-solid fa-brain"></i> TensorFlow</li>
          <li><i class="fa-solid fa-fire"></i> PyTorch</li>
          <li><i class="fa-solid fa-chart-line"></i> Scikit-learn</li>
          <li><i class="fa-solid fa-infinity"></i> NumPy</li>
        </ul>
        <a class="hp-card__link" href="<?php echo appUrl('public/skills.php'); ?>">
          <i class="fa-solid fa-arrow-right"></i> View Skills
        </a>
      </article>

      <article class="hp-card">
        <div class="hp-card__icon-wrap" aria-hidden="true">
          <i class="fa-solid fa-network-wired hp-card__icon-fa"></i>
        </div>
        <div class="hp-card__num">03</div>
        <h3 class="hp-card__title">Algorithms &amp; Systems</h3>
        <p class="hp-card__body">System architecture, algorithmic complexity, data structures, and high-performance computing.</p>
        <ul class="hp-card__tags">
          <li><i class="fa-solid fa-diagram-project"></i> Algorithms</li>
          <li><i class="fa-solid fa-server"></i> System Design</li>
          <li><i class="fa-solid fa-cubes"></i> Data Structures</li>
          <li><i class="fa-solid fa-cloud"></i> Cloud Systems</li>
        </ul>
        <a class="hp-card__link" href="<?php echo appUrl('public/projects.php'); ?>">
          <i class="fa-solid fa-arrow-right"></i> View Engineering
        </a>
      </article>

      <article class="hp-card">
        <div class="hp-card__icon-wrap" aria-hidden="true">
          <i class="fa-solid fa-chalkboard-user hp-card__icon-fa"></i>
        </div>
        <div class="hp-card__num">04</div>
        <h3 class="hp-card__title">Teaching &amp; Innovation</h3>
        <p class="hp-card__body">ICT &amp; Programming Lecturer committed to making complex computing accessible and industry-ready.</p>
        <ul class="hp-card__tags">
          <li><i class="fa-solid fa-graduation-cap"></i> Instruction</li>
          <li><i class="fa-solid fa-lightbulb"></i> Curriculum</li>
          <li><i class="fa-solid fa-users"></i> Mentoring</li>
          <li><i class="fa-solid fa-rocket"></i> Innovation</li>
        </ul>
        <a class="hp-card__link" href="<?php echo appUrl('public/experience.php'); ?>">
          <i class="fa-solid fa-arrow-right"></i> See Experience
        </a>
      </article>

    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- NEWSLETTER / UPDATES CTA                                   -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section class="hp-about" style="padding:48px 0;border-bottom:1px solid var(--light-gray);">
  <div class="container" style="display:grid;grid-template-columns:1.2fr 0.8fr;gap:28px;align-items:center;">
    <div>
      <div class="hp-section-label"><span class="hp-section-label__text">Updates</span></div>
      <h2 class="hp-about__heading" style="margin-bottom:10px;">Stay in the loop</h2>
      <p style="margin:0;color:var(--muted);max-width:520px;line-height:1.5;">Follow new essays via RSS, or jump to Contact for collaborations. No spam — just signal.</p>
    </div>
    <div class="tool-actions" style="justify-content:flex-end;">
      <a class="btn primary" href="<?php echo appUrl('feed.php'); ?>"><i class="fa-solid fa-rss"></i> RSS feed</a>
      <a class="btn" href="<?php echo appUrl('guestbook.php'); ?>">Guestbook</a>
      <a class="btn" href="<?php echo appUrl('changelog.php'); ?>">Changelog</a>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- CONTACT STRIP CTA                                          -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section class="hp-cta-strip">
  <div class="container hp-cta-strip__inner">
    <div class="hp-cta-strip__copy">
      <h2 class="hp-cta-strip__heading">Let's build something purposeful together.</h2>
      <p class="hp-cta-strip__sub">Whether you're a developer, researcher, entrepreneur, or someone curious about technology - I'd love to connect.</p>
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

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- HOMEPAGE-ONLY STYLES                                       -->
<!-- ═══════════════════════════════════════════════════════════ -->
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
}

.hp-hero__bg-grid {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(234,88,12,0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(234,88,12,0.05) 1px, transparent 1px);
  background-size: 80px 80px;
  pointer-events: none;
  opacity: 0.6;
}

.hp-hero__plane {
  position: relative;
  z-index: 1;
  padding: 56px 0 48px;
}

.hp-hero__inner {
  display: grid;
  grid-template-columns: 1.05fr 0.95fr;
  gap: 48px;
  align-items: stretch;
  min-height: min(68vh, 640px);
}

.hp-hero__copy {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 16px;
  max-width: 640px;
}

.hp-hero__role {
  margin: 0;
  font-size: var(--text-meta, 0.72rem);
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--accent-muted);
}

.hp-hero__brand {
  margin: 0;
  font-size: clamp(2.6rem, 6.5vw, 4.6rem);
  line-height: 0.94;
  letter-spacing: -0.055em;
  font-weight: 800;
  color: var(--text);
}

.hp-brand-line {
  display: block;
  font-family: var(--font-brand-name);
  opacity: 0;
  transform: translateY(20px);
  animation: hp-slide-up 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.05s forwards;
}

.hp-hero__tagline {
  margin: 0;
  font-size: clamp(1.15rem, 2.2vw, 1.55rem);
  font-weight: 600;
  letter-spacing: -0.02em;
  line-height: 1.25;
  color: var(--accent);
  opacity: 0;
  transform: translateY(16px);
  animation: hp-slide-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s forwards;
}

@keyframes hp-slide-up {
  to { opacity: 1; transform: translateY(0); }
}

.hp-hero__sub {
  margin: 0;
  max-width: 520px;
  color: var(--muted);
  font-size: var(--text-body, 1.05rem);
  line-height: 1.55;
  opacity: 0;
  animation: hp-slide-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.35s forwards;
}

.hp-role-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.hp-chip {
  display: inline-block;
  padding: 5px 12px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--accent-muted);
  border: 1px solid rgba(234,88,12,0.28);
  background: rgba(234,88,12,0.05);
}

.hp-hero__ctas {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
  margin-top: 6px;
  opacity: 0;
  animation: hp-slide-up 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.5s forwards;
}

.hp-focus-strip {
  border-bottom: 1px solid var(--light-gray);
  background: var(--soft-bg);
  padding: 22px 0;
}

.hp-focus-strip__inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
}

.hp-cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 13px 24px;
  font-size: 0.82rem;
  font-weight: 700;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  text-decoration: none;
  transition: all 0.2s ease;
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
}

.hp-cta--secondary {
  background: transparent;
  color: var(--text);
  border: 1px solid rgba(15,23,42,0.2);
}

.hp-cta--secondary:hover {
  background: rgba(15,23,42,0.06);
  opacity: 1;
}

.hp-cta--ghost {
  background: transparent;
  color: var(--muted);
  border: 1px solid var(--light-gray);
  padding: 12px 16px;
}

.hp-cta--ghost:hover { opacity: 0.7; }

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
  color: rgba(255,255,255,0.8);
  border: 1px solid rgba(255,255,255,0.3);
}

.hp-cta--ghost-light:hover { opacity: 0.7; }

.hp-cta-arrow {
  display: inline-block;
  transition: transform 0.2s ease;
}

.hp-cta:hover .hp-cta-arrow { transform: translateX(4px); }

/* ── PHOTO PLANE ───────────────────────────────────────── */
.hp-hero__visual {
  display: flex;
  align-items: stretch;
  justify-content: flex-end;
  opacity: 0;
  animation: hp-fade-in 0.85s ease 0.35s forwards;
  min-width: 0;
}

@keyframes hp-fade-in {
  to { opacity: 1; }
}

.hp-photo-frame {
  position: relative;
  width: 100%;
  max-width: none;
  height: 100%;
  min-height: min(68vh, 640px);
  overflow: hidden;
}

.hp-photo-inner {
  width: 100%;
  height: 100%;
  min-height: inherit;
  border: 1px solid var(--light-gray);
  overflow: hidden;
  position: relative;
  background-color: #e2e8f0;
}

.hp-photo-inner picture {
  display: block;
  width: 100%;
  height: 100%;
}

.hp-photo {
  width: 100%;
  height: 100%;
  min-height: inherit;
  object-fit: cover;
  object-position: top center;
  display: block;
  filter: grayscale(8%);
  transition: opacity 0.45s ease-out, filter 0.4s ease;
}

.hp-photo-frame:hover .hp-photo {
  filter: grayscale(0%);
}

/* ── STATS ROW ─────────────────────────────────────────── */
.hp-stats-row {
  display: flex;
  gap: 0;
  min-width: min(100%, 320px);
  border: 1px solid var(--light-gray);
  background: #fff;
}

.hp-stat {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 16px 12px;
  gap: 4px;
  border-right: 1px solid var(--light-gray);
}

.hp-stat:last-child { border-right: none; }

.hp-stat__num {
  font-size: 1.6rem;
  font-weight: 800;
  letter-spacing: -0.05em;
  color: var(--text);
  line-height: 1;
}

.hp-stat__label {
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--muted);
}

/* ── SCROLL INDICATOR ──────────────────────────────────── */
.hp-scroll-indicator {
  position: absolute;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--muted);
  opacity: 0;
  animation: hp-fade-in 1s ease 1.2s forwards;
}

.hp-scroll-line {
  display: block;
  width: 1px;
  height: 40px;
  background: linear-gradient(to bottom, var(--accent), transparent);
  animation: hp-line-grow 1.5s ease-in-out infinite;
}

@keyframes hp-line-grow {
  0% { transform: scaleY(0); transform-origin: top; opacity: 0; }
  50% { transform: scaleY(1); transform-origin: top; opacity: 1; }
  100% { transform: scaleY(1); transform-origin: bottom; opacity: 0; }
}

/* ── QUICK NAV STRIP ───────────────────────────────────── */
.hp-quicknav {
  background: var(--text);
  border-bottom: 1px solid rgba(255,255,255,0.06);
  overflow-x: auto;
  scrollbar-width: none;
}

.hp-quicknav::-webkit-scrollbar { display: none; }

.hp-quicknav__inner {
  display: flex;
  align-items: center;
  gap: 0;
  min-height: 52px;
  padding-top: 0;
  padding-bottom: 0;
}

.hp-qn-link {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 0 20px;
  height: 52px;
  font-size: 0.76rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(255,255,255,0.6);
  text-decoration: none;
  border-right: 1px solid rgba(255,255,255,0.08);
  white-space: nowrap;
  transition: color 0.2s ease, background 0.2s ease;
}

.hp-qn-link i { font-size: 0.8rem; }

.hp-qn-link:hover {
  color: #fff;
  background: rgba(255,255,255,0.07);
  opacity: 1;
}

.hp-qn-link--cta {
  margin-left: auto;
  color: var(--accent);
  border-right: none;
}

.hp-qn-link--cta:hover {
  color: #fff;
  background: var(--accent);
}

/* ── SECTION LABEL ─────────────────────────────────────── */
.hp-section-label {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.hp-section-label__text {
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--accent-muted);
}

.hp-section-label::after {
  content: '';
  display: block;
  flex: 1;
  height: 1px;
  background: var(--light-gray);
  max-width: 60px;
}

/* ── ABOUT SECTION ─────────────────────────────────────── */
.hp-about {
  padding: 80px 0;
  border-bottom: 1px solid var(--light-gray);
}

.hp-about__inner {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 60px;
  align-items: start;
}

.hp-about__heading {
  margin: 0 0 20px;
  font-size: clamp(1.6rem, 2.5vw, 2.2rem);
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -0.04em;
  color: var(--text);
}

.hp-about__body {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 24px;
}

.hp-about__body p {
  margin: 0;
  font-size: 1rem;
  line-height: 1.6;
  color: var(--muted);
}

.hp-text-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8rem;
  font-weight: 800;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--text);
  border-bottom: 2px solid var(--text);
  padding-bottom: 2px;
  text-decoration: none;
  transition: color 0.2s ease, border-color 0.2s ease;
}

.hp-text-link:hover {
  color: var(--accent);
  border-color: var(--accent);
  opacity: 1;
}

/* ── SPECIALTIES ───────────────────────────────────────── */
.hp-specialties {
  padding: 80px 0;
  border-bottom: 1px solid var(--light-gray);
}

.hp-specialties__header {
  margin-bottom: 40px;
}

.hp-specialties__title {
  margin: 0;
  font-size: clamp(1.8rem, 3vw, 2.6rem);
  font-weight: 800;
  letter-spacing: -0.05em;
  color: var(--text);
  line-height: 1;
}

.hp-cards-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0;
  border: 1px solid var(--light-gray);
}

.hp-card {
  padding: 32px 28px;
  background: var(--soft-bg);
  border-right: 1px solid var(--light-gray);
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: background 0.25s ease;
  position: relative;
  overflow: hidden;
}

.hp-card:last-child { border-right: none; }

.hp-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: var(--accent);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.hp-card:hover::before { transform: scaleX(1); }

.hp-card:hover {
  background: rgba(234,88,12,0.03);
}

.hp-card__icon-wrap {
  position: relative;
  width: 68px;
  height: 68px;
  background: rgba(234,88,12,0.08);
  border: 1px solid rgba(234,88,12,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: background 0.25s ease, border-color 0.25s ease;
}

.hp-card:hover .hp-card__icon-wrap {
  background: rgba(234,88,12,0.15);
  border-color: rgba(234,88,12,0.4);
}

.hp-card__icon-fa {
  font-size: 1.9rem;
  color: var(--accent);
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.hp-card:hover .hp-card__icon-fa {
  transform: scale(1.12) rotate(-4deg);
}

.hp-card__num {
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  color: var(--accent);
  opacity: 0.6;
  font-family: monospace;
}

.hp-card__title {
  margin: 0;
  font-size: 1.08rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text);
  line-height: 1.2;
}

.hp-card__body {
  margin: 0;
  font-size: 0.88rem;
  line-height: 1.5;
  color: var(--muted);
  flex: 1;
}

/* Skill tag list with inline icons */
.hp-card__tags {
  list-style: none;
  padding: 0;
  margin: 4px 0 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.hp-card__tags li {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--text);
  padding: 5px 10px;
  background: rgba(15,23,42,0.03);
  border: 1px solid var(--light-gray);
  transition: background 0.15s ease, border-color 0.15s ease;
}

.hp-card:hover .hp-card__tags li {
  background: rgba(234,88,12,0.05);
  border-color: rgba(234,88,12,0.15);
}

.hp-card__tags li i {
  font-size: 0.82rem;
  color: var(--accent);
  width: 14px;
  text-align: center;
  flex-shrink: 0;
}

.hp-card__link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  align-self: flex-start;
  margin-top: 4px;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.09em;
  text-transform: uppercase;
  color: var(--accent-muted);
  text-decoration: none;
  border-bottom: 1px solid rgba(194,65,12,0.3);
  padding-bottom: 1px;
  margin-top: 4px;
  transition: color 0.2s, border-color 0.2s;
}

.hp-card__link:hover {
  color: var(--accent);
  border-color: var(--accent);
  opacity: 1;
}

/* ── CTA STRIP ─────────────────────────────────────────── */
.hp-cta-strip {
  background: var(--text);
  padding: 72px 0;
}

.hp-cta-strip__inner {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 40px;
  align-items: center;
}

.hp-cta-strip__heading {
  margin: 0 0 10px;
  font-size: clamp(1.5rem, 2.5vw, 2.2rem);
  font-weight: 800;
  letter-spacing: -0.04em;
  color: #fff;
  line-height: 1.1;
}

.hp-cta-strip__sub {
  margin: 0;
  font-size: 1rem;
  color: rgba(255,255,255,0.6);
  line-height: 1.5;
  max-width: 560px;
}

.hp-cta-strip__actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
  align-items: flex-end;
}

/* ── RESPONSIVE ────────────────────────────────────────── */
@media (max-width: 1100px) {
  .hp-cards-grid {
    grid-template-columns: 1fr 1fr;
  }

  .hp-card:nth-child(2) { border-right: none; }
  .hp-card:nth-child(1),
  .hp-card:nth-child(2) {
    border-bottom: 1px solid var(--light-gray);
  }
}

@media (max-width: 860px) {
  .hp-hero {
    min-height: 0;
  }

  .hp-hero__plane {
    padding: 36px 0 28px;
  }

  .hp-hero__inner {
    grid-template-columns: 1fr;
    gap: 28px;
    min-height: 0;
  }

  .hp-hero__visual {
    order: -1;
    justify-content: stretch;
  }

  .hp-photo-frame,
  .hp-photo-inner,
  .hp-photo {
    min-height: min(52vw, 360px);
    height: min(52vw, 360px);
  }

  .hp-hero__copy {
    align-items: flex-start;
    text-align: left;
    max-width: none;
  }

  .hp-hero__sub {
    max-width: none;
  }

  .hp-focus-strip__inner {
    flex-direction: column;
    align-items: stretch;
  }

  .hp-stats-row {
    width: 100%;
  }

  .hp-about__inner {
    grid-template-columns: 1fr;
    gap: 16px;
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
  .hp-cards-grid {
    grid-template-columns: 1fr;
  }

  .hp-card { border-right: none; border-bottom: 1px solid var(--light-gray); }
  .hp-card:last-child { border-bottom: none; }

  .hp-qn-link--cta { margin-left: 0; }
}

@media (prefers-reduced-motion: reduce) {
  .hp-brand-line,
  .hp-hero__tagline,
  .hp-hero__sub,
  .hp-hero__ctas,
  .hp-hero__visual,
  .hp-scroll-indicator { animation: none; opacity: 1; transform: none; }

  .hp-scroll-line { animation: none; }
}
</style>

<?php
require_once __DIR__ . '/footer.php';
?>
