<?php
$activePage = 'oss';
require_once __DIR__ . '/header.php';

$repos = [
  ['name' => 'tanuisila', 'desc' => 'This portfolio — PHP CMS, blog, admin, and personal lab surfaces.', 'url' => 'https://github.com/silatanui/tanuisila'],
  ['name' => 'Graph-Coloring-Machine', 'desc' => 'Interactive graph coloring / algorithms exploration.', 'url' => 'https://github.com/silatanui/Graph-Coloring-Machine'],
  ['name' => 'Enumerator', 'desc' => 'Enumeration tooling for theoretical CS experiments.', 'url' => 'https://github.com/silatanui/Enumerator'],
  ['name' => 'Entscheidungsproblem', 'desc' => 'Computability-flavored interactive exploration.', 'url' => 'https://github.com/silatanui/Entscheidungsproblem'],
];
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Open Source</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Open source &amp; public work</h2>
        <p>Repositories worth opening — code as portfolio, the Anthony Fu style.</p>
      </div>
      <div class="feature-grid">
        <?php foreach ($repos as $repo): ?>
          <article class="feature-tile reveal-on-enter">
            <div class="feature-tile__icon"><i class="fa-brands fa-github"></i></div>
            <h3><?php echo htmlspecialchars($repo['name']); ?></h3>
            <p><?php echo htmlspecialchars($repo['desc']); ?></p>
            <a class="tile-link" href="<?php echo htmlspecialchars($repo['url']); ?>" target="_blank" rel="noopener">View on GitHub →</a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
