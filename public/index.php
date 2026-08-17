<?php
$activePage = 'home';
require_once __DIR__ . '/header.php';

// Safe self-healing localized copy mechanism to handle server asset permissions
$sourceImg = __DIR__ . '/../IMG_20260625_115331.jpg';
$destImg = __DIR__ . '/profile.jpg';
if (file_exists($sourceImg)) {
    if (!file_exists($destImg) || filesize($sourceImg) !== filesize($destImg)) {
        copy($sourceImg, $destImg);
        // Also copy to root index path context as fall-back
        @copy($sourceImg, __DIR__ . '/../profile.jpg');
    }
}
?>

<section class="hero" style="border-bottom:none;">
  <div class="container hero-inner">
    <div class="hero-copy">
      <p class="eyebrow"><?php echo htmlspecialchars($profile['full_name']); ?> · Computer Scientist · Software Developer · Researcher</p>
      <h2 class="hero-title">Building technology<br>with purpose.</h2>
      <p class="hero-sub"><?php echo htmlspecialchars($settings['hero_text'] ?: $profile['summary'] ?: 'Computer Scientist and developer, curious about AI, research, and the many ways technology can be put to good use.'); ?></p>
      <div class="hero-ctas">
        <a class="btn primary" href="projects.php">View projects</a>
        <a class="btn secondary" href="research.php">Explore research</a>
      </div>
      <div class="sso-row">
        <?php if (!empty($profile['github'])): ?><a class="sso github" href="<?php echo htmlspecialchars($profile['github']); ?>" target="_blank">GitHub</a><?php endif; ?>
        <?php if (!empty($profile['linkedin'])): ?><a class="sso email" href="<?php echo htmlspecialchars($profile['linkedin']); ?>" target="_blank">LinkedIn</a><?php endif; ?>
      </div>
    </div>
    <div class="hero-visual">
      <img src="profile.jpg?v=<?php echo @filemtime(__DIR__ . '/profile.jpg'); ?>" alt="Tanui Kipng'etich Sila" class="hero-logo" style="width: 100%; height: auto; object-fit: cover;">
    </div>
  </div>
</section>

<main class="container">
  <section id="about-summary" class="section-row" style="border-bottom:none; border-top:1px solid var(--light-gray);">
    <div class="section-label">Home</div>
    <div class="section-content">
      <h2><strong>A place for the things I build, learn, and explore.</strong></h2>
      <div class="content">
        <p>I created this website as a place to bring together the work, ideas, experiences, and interests that have shaped my journey in technology. Over the years, Computer Science has taken me in many different directions, from software development and artificial intelligence to research, teaching, data, and exploring how different technologies can be used to solve practical problems. Rather than keeping all of that work scattered across different projects and platforms, I wanted to create a space where I could share it, look back at how I have grown, and document some of the things I am still learning along the way.</p>
        <p>I have always enjoyed understanding how things work and then trying to build something myself. Sometimes that starts with a problem that needs a solution, sometimes with a technology I have become curious about, and sometimes with a simple question that keeps bothering me until I decide to explore it. Not every idea turns into a finished project, and that is part of the process I enjoy. There is something valuable about experimenting, making mistakes, trying again, and eventually arriving at something that works. A lot of what I know today has come from that process of building, testing, breaking things, and learning from them.</p>
        <p>This website is therefore more than a collection of projects or a place to list technologies I know. It is a reflection of the work I have done and the areas I continue to explore. Here, you will find some of my software projects, research interests, technical experiments, academic work, and thoughts on technologies that I find interesting. I also want this to be a place where I can share what I learn along the way, because documenting a journey can be just as valuable as reaching the destination.</p>
        <p>I am also interested in meeting people who are curious about technology and enjoy learning, building, researching, and exchanging ideas. Whether you are a developer, researcher, student, educator, entrepreneur, or simply someone interested in what technology can do, I believe there is always something to learn from a good conversation. If you happen to find something here that interests you, or you are working on something you think I might find interesting, <strong>I would be happy to connect.</strong></p>
        <p><strong>Computer Science · Software Development · Artificial Intelligence · Research · Technology Innovation</strong></p>
      </div>
      <div style="margin-top:24px;">
        <a href="projects.php" style="font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; border-bottom: 2px solid var(--text); padding-bottom: 2px; font-size: 0.8rem; color: var(--text);">EXPLORE MY WORK →</a>
      </div>
    </div>
  </section>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>

