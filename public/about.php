<?php
$activePage = 'about';
require_once __DIR__ . '/header.php';

$portraitFile = __DIR__ . '/about-portrait.jpg';
if (!is_file($portraitFile)) {
  $portraitFile = __DIR__ . '/profile.jpg';
}
$profileImg = appUrl('public/' . basename($portraitFile)) . '?v=' . (@filemtime($portraitFile) ?: time());
$displayName = $settings['site_title'] ?? $profile['full_name'] ?? "Tanui Kipng'etich Sila";
$location = trim((string) ($profile['location'] ?? ''));
$headline = trim((string) ($profile['headline'] ?? 'Computer Scientist · Software Developer · Researcher'));

$eduCount = is_array($education ?? null) ? count($education) : 0;
$expCount = is_array($experience ?? null) ? count($experience) : 0;
$projCount = !empty($navProjects) ? count($navProjects) : (is_array($projects ?? null) ? count($projects) : 0);
$skillCount = is_array($skills ?? null) ? count($skills) : 0;

$latestRole = is_array($experience ?? null) && !empty($experience) ? $experience[0] : null;
$latestEdu = is_array($education ?? null) && !empty($education) ? $education[0] : null;
?>

<main id="main-content" class="ab-page">
  <section class="ab-hero" aria-label="About introduction">
    <div class="ab-hero__bg" aria-hidden="true"></div>
    <div class="container ab-hero__inner">
      <div class="ab-hero__media">
        <div class="ab-hero__photo-wrap img-skeleton-wrap">
          <img
            class="ab-hero__photo"
            src="<?php echo htmlspecialchars($profileImg, ENT_QUOTES, 'UTF-8'); ?>"
            alt="<?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>"
            width="480"
            height="480"
            decoding="async"
            fetchpriority="high"
            onload="this.closest('.img-skeleton-wrap') && this.closest('.img-skeleton-wrap').classList.add('loaded')"
          >
        </div>
      </div>

      <div class="ab-hero__copy">
        <p class="ab-hero__eyebrow">About</p>
        <h1 class="ab-hero__name brand-name"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="ab-hero__role"><?php echo htmlspecialchars($headline, ENT_QUOTES, 'UTF-8'); ?></p>
        <p class="ab-hero__lede">
          I build practical technology - software, research systems, and digital products -
          with a clear through-line from understanding the problem to shipping something people can use.
        </p>
        <?php if ($location !== ''): ?>
          <p class="ab-hero__place"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <div class="ab-hero__actions">
          <a class="ab-btn ab-btn--primary" href="<?php echo appUrl('public/contact.php'); ?>">Get in Touch</a>
          <a class="ab-btn ab-btn--ghost" href="<?php echo appUrl('public/projects.php'); ?>">View Projects</a>
          <a class="ab-btn ab-btn--ghost" href="<?php echo appUrl('public/Sila_Kipng\'etich_Tanui_CV.pdf'); ?>" target="_blank" rel="noopener noreferrer">Download CV</a>
        </div>
      </div>
    </div>
  </section>

  <section class="ab-pulse" aria-label="At a glance">
    <div class="container ab-pulse__inner">
      <div class="ab-pulse__stat">
        <strong><?php echo max($eduCount, 1); ?></strong>
        <span>Education credentials</span>
      </div>
      <div class="ab-pulse__stat">
        <strong><?php echo max($expCount, 1); ?></strong>
        <span>Professional roles</span>
      </div>
      <div class="ab-pulse__stat">
        <strong><?php echo max($projCount, 1); ?>+</strong>
        <span>Live projects</span>
      </div>
      <div class="ab-pulse__stat">
        <strong><?php echo max($skillCount, 1); ?></strong>
        <span>Skills tracked</span>
      </div>
    </div>
  </section>

  <section class="ab-story">
    <div class="container ab-story__inner">
      <div class="ab-story__label">
        <span>The story</span>
      </div>
      <div class="ab-story__body">
        <p>
          I'm <strong><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></strong>,
          a Computer Scientist, Software Developer, Researcher, and Technology Innovator.
          My work spans software development, web engineering, databases, artificial intelligence,
          machine learning, networking, and security research.
        </p>
        <p>
          My interest in technology has always been tied to <strong>building things</strong>.
          I enjoy taking a complex problem, breaking it into clear units, and turning ideas into
          systems people can use. Programming, for me, is a rigorous way to create practical,
          maintainable solutions - not just writing code for its own sake.
        </p>
        <p>
          I earned a <strong>BSc in Computer Science at Kirinyaga University</strong> with First Class Honours,
          and I am pursuing an <strong>MSc in Computer Science at the University of Debrecen</strong>
          as a <strong>Stipendium Hungaricum</strong> scholar, with focus areas in data science,
          AI workflows, geometric modeling, and research methods.
        </p>
        <p>
          Along the way I have taught programming, supported campus IT operations, and worked in
          international customer success on enterprise commerce stacks. That mix - instruction,
          systems reliability, and live product support - still shapes how I design and ship software today.
        </p>
      </div>
    </div>
  </section>

  <?php if ($latestEdu || $latestRole): ?>
  <section class="ab-path">
    <div class="container">
      <div class="ab-path__head">
        <p class="ab-focus__eyebrow">Path</p>
        <h2 class="ab-focus__title">Where the work comes from</h2>
        <p class="ab-focus__lede">Snapshots from education and experience - open either timeline for the full story.</p>
      </div>

      <div class="ab-path__grid">
        <?php if ($latestEdu): ?>
          <a class="ab-path__card" href="<?php echo appUrl('public/education.php'); ?>">
            <span class="ab-path__kicker">Education</span>
            <strong><?php echo htmlspecialchars((string) ($latestEdu['degree'] ?? 'Degree'), ENT_QUOTES, 'UTF-8'); ?></strong>
            <span class="ab-path__meta">
              <?php echo htmlspecialchars((string) ($latestEdu['institution'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
              <?php
                $eduWhen = trim(($latestEdu['start_year'] ?? '') . ' - ' . ($latestEdu['end_year'] ?? ''), ' -');
                if ($eduWhen !== ''):
              ?>
                <em><?php echo htmlspecialchars($eduWhen, ENT_QUOTES, 'UTF-8'); ?></em>
              <?php endif; ?>
            </span>
            <span class="ab-path__go">Full education timeline →</span>
          </a>
        <?php endif; ?>

        <?php if ($latestRole): ?>
          <a class="ab-path__card" href="<?php echo appUrl('public/experience.php'); ?>">
            <span class="ab-path__kicker">Experience</span>
            <strong><?php echo htmlspecialchars((string) ($latestRole['role_name'] ?? 'Role'), ENT_QUOTES, 'UTF-8'); ?></strong>
            <span class="ab-path__meta">
              <?php echo htmlspecialchars((string) ($latestRole['company'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
              <?php
                $expStart = trim((string) ($latestRole['start_date'] ?? ''));
                $expEnd = trim((string) ($latestRole['end_date'] ?? ''));
                $expCurrent = $expEnd === '' || strcasecmp($expEnd, 'present') === 0;
                $expWhen = trim($expStart . ' - ' . ($expCurrent ? 'Present' : $expEnd), ' -');
                if ($expWhen !== ''):
              ?>
                <em><?php echo htmlspecialchars($expWhen, ENT_QUOTES, 'UTF-8'); ?></em>
              <?php endif; ?>
            </span>
            <span class="ab-path__go">Full experience timeline →</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="ab-focus">
    <div class="container">
      <div class="ab-focus__head">
        <p class="ab-focus__eyebrow">Focus</p>
        <h2 class="ab-focus__title">How I spend my craft</h2>
        <p class="ab-focus__lede">Four threads that show up again and again in the work I ship.</p>
      </div>

      <ol class="ab-focus__list">
        <li class="ab-focus__item">
          <span class="ab-focus__num" aria-hidden="true">01</span>
          <div>
            <h3 class="ab-focus__item-title">Applied engineering</h3>
            <p class="ab-focus__item-body">
              Full-stack applications and database architectures with Python, Go, PHP, JavaScript, C#,
              Blazor, React, Laravel, and SQL/NoSQL - built as integrated, scalable systems rather than isolated demos.
            </p>
          </div>
        </li>
        <li class="ab-focus__item">
          <span class="ab-focus__num" aria-hidden="true">02</span>
          <div>
            <h3 class="ab-focus__item-title">Research &amp; algorithms</h3>
            <p class="ab-focus__item-body">
              From neural nets and clustering to Bézier curves and cryptography - I like turning abstract
              models into interactive, testable programs that make the idea easier to explore.
            </p>
          </div>
        </li>
        <li class="ab-focus__item">
          <span class="ab-focus__num" aria-hidden="true">03</span>
          <div>
            <h3 class="ab-focus__item-title">Teaching &amp; clarity</h3>
            <p class="ab-focus__item-body">
              As an ICT and Programming Lecturer, I learned to explain complex systems clearly.
              Technical knowledge matters most when it can be taught, reused, and applied under real constraints.
            </p>
          </div>
        </li>
        <li class="ab-focus__item">
          <span class="ab-focus__num" aria-hidden="true">04</span>
          <div>
            <h3 class="ab-focus__item-title">Innovation through Sterrali</h3>
            <p class="ab-focus__item-body">
              Through <strong>Sterrali Technologies</strong>, I channel the same engineering mindset into
              digital products, automation, and technology entrepreneurship - shipping in public whenever I can.
            </p>
          </div>
        </li>
      </ol>
    </div>
  </section>

  <section class="ab-explore">
    <div class="container">
      <div class="ab-focus__head">
        <p class="ab-focus__eyebrow">Explore</p>
        <h2 class="ab-focus__title">Keep going from here</h2>
        <p class="ab-focus__lede">The rest of the portfolio is one click away if you want proof, not just a bio.</p>
      </div>
      <div class="ab-explore__grid">
        <a class="ab-explore__item" href="<?php echo appUrl('public/projects.php'); ?>">
          <strong>Projects</strong>
          <span>Live builds with screenshots, stack notes, and launch links.</span>
        </a>
        <a class="ab-explore__item" href="<?php echo appUrl('public/skills.php'); ?>">
          <strong>Skills</strong>
          <span>Languages, data, cloud, and delivery tools I use in practice.</span>
        </a>
        <a class="ab-explore__item" href="<?php echo appUrl('blog.php'); ?>">
          <strong>Blog</strong>
          <span>Notes on reverse engineering, tooling, and how this site is run.</span>
        </a>
        <a class="ab-explore__item" href="<?php echo appUrl('public/contact.php'); ?>">
          <strong>Contact</strong>
          <span>Roles, collaborations, and questions are welcome.</span>
        </a>
      </div>
    </div>
  </section>

  <section class="ab-close">
    <div class="container ab-close__inner">
      <blockquote class="ab-close__quote">
        Understand the problem. Explore the possibilities. Build the solution. Test it. Improve it.
      </blockquote>
      <p class="ab-close__signoff">
        Computer science isn't only what I studied - it's how I approach problems.
      </p>
      <div class="ab-close__actions">
        <a class="ab-btn ab-btn--primary" href="<?php echo appUrl('public/contact.php'); ?>">Start a conversation</a>
        <a class="ab-btn ab-btn--ghost" href="<?php echo appUrl('public/experience.php'); ?>">See experience</a>
      </div>
    </div>
  </section>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>
