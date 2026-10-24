<?php
$activePage = 'about';
require_once __DIR__ . '/header.php';

$portraitFile = __DIR__ . '/about-portrait.jpg';
if (!is_file($portraitFile)) {
  $portraitFile = __DIR__ . '/profile.jpg';
}
$profileImg = appUrl('public/' . basename($portraitFile)) . '?v=' . (@filemtime($portraitFile) ?: time());
$displayName = $settings['site_title'] ?? $profile['full_name'] ?? "Tanui Kipng'etich Sila";
?>

<main class="ab-page">
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
        <p class="ab-hero__role">Computer Scientist · Software Developer · Researcher</p>
        <p class="ab-hero__lede">
          I turn ideas into working technology — software, research systems, and digital products built with clarity and purpose.
        </p>
        <div class="ab-hero__actions">
          <a class="ab-btn ab-btn--primary" href="<?php echo appUrl('public/contact.php'); ?>">Get in Touch</a>
          <a class="ab-btn ab-btn--ghost" href="<?php echo appUrl('public/projects.php'); ?>">View Projects</a>
        </div>
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
          I’m <strong><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></strong>, a Computer Scientist, Software Developer, Researcher, and Technology Innovator. My work spans software development, web engineering, databases, artificial intelligence, machine learning, networking, and security research.
        </p>
        <p>
          My interest in technology has always been tied to <strong>building things</strong>. I enjoy taking a complex problem, breaking it into clear units, and turning ideas into systems people can use. Programming, for me, is a rigorous way to create practical, maintainable solutions — not just writing code.
        </p>
        <p>
          I began with a <strong>BSc in Computer Science at Kirinyaga University</strong> (First Class Honours), then completed an <strong>MSc in Computer Science at the University of Debrecen</strong> as a <strong>Stipendium Hungaricum</strong> scholar, focusing on data science, AI workflows, geometric modeling, and research methods.
        </p>
      </div>
    </div>
  </section>

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
              Full-stack applications and database architectures with Python, Go, PHP, JavaScript, C#, Blazor, React, Laravel, and SQL/NoSQL — built as integrated, scalable systems.
            </p>
          </div>
        </li>
        <li class="ab-focus__item">
          <span class="ab-focus__num" aria-hidden="true">02</span>
          <div>
            <h3 class="ab-focus__item-title">Research &amp; algorithms</h3>
            <p class="ab-focus__item-body">
              From neural nets and clustering to Bézier curves and cryptography — I like turning abstract models into interactive, testable programs.
            </p>
          </div>
        </li>
        <li class="ab-focus__item">
          <span class="ab-focus__num" aria-hidden="true">03</span>
          <div>
            <h3 class="ab-focus__item-title">Teaching &amp; clarity</h3>
            <p class="ab-focus__item-body">
              As an ICT and Programming Lecturer, I learned to explain complex systems clearly. Technical knowledge matters most when it can be taught and applied.
            </p>
          </div>
        </li>
        <li class="ab-focus__item">
          <span class="ab-focus__num" aria-hidden="true">04</span>
          <div>
            <h3 class="ab-focus__item-title">Innovation through Sterrali</h3>
            <p class="ab-focus__item-body">
              Through <strong>Sterrali Technologies</strong>, I channel the same engineering mindset into digital products, automation, and technology entrepreneurship.
            </p>
          </div>
        </li>
      </ol>
    </div>
  </section>

  <section class="ab-close">
    <div class="container ab-close__inner">
      <blockquote class="ab-close__quote">
        Understand the problem. Explore the possibilities. Build the solution. Test it. Improve it.
      </blockquote>
      <p class="ab-close__signoff">
        Computer science isn’t only what I studied — it’s how I approach problems.
      </p>
      <div class="ab-close__actions">
        <a class="ab-btn ab-btn--primary" href="<?php echo appUrl('public/contact.php'); ?>">Start a conversation</a>
        <a class="ab-btn ab-btn--ghost" href="<?php echo appUrl('public/blog.php'); ?>">Read the blog</a>
      </div>
    </div>
  </section>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>
