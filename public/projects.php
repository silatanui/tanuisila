<?php
$activePage = 'projects';
require_once __DIR__ . '/header.php';
?>

<main class="container">
  <section id="projects" class="section-row" style="border-bottom:none;">
    <div class="section-label">Projects</div>
    <div class="section-content">
      <h2>Selected Engineering Work</h2>
      <div class="editorial-grid">
        <?php foreach ($projects as $p): ?>
          <article class="project-item">
            <div>
              <div class="project-meta">Project</div>
              <h3 style="margin-top: 4px;"><?php echo htmlspecialchars($p['title']); ?></h3>
              <p style="margin-top: 10px;"><?php echo htmlspecialchars($p['description']); ?></p>
            </div>
            <?php if (!empty($p['url'])): ?>
              <a class="button" href="<?php echo htmlspecialchars($p['url']); ?>" target="_blank">Live Demo →</a>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>
