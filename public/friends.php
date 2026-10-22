<?php
$activePage = 'friends';
require_once __DIR__ . '/header.php';

$friends = [
  ['label' => 'GitHub', 'url' => 'https://github.com/silatanui', 'note' => 'Code and experiments.'],
  ['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/tanui-kipng-etich-sila/', 'note' => 'Professional updates.'],
  ['label' => 'IndieWeb', 'url' => 'https://indieweb.org/', 'note' => 'Own-your-content community.'],
  ['label' => 'Awesome RE', 'url' => 'https://github.com/morluto/awesome-reverse-engineering', 'note' => 'RE tooling map.'],
];
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Friends</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Friends of the site</h2>
        <p>A tiny webring-style shelf — places I send people often.</p>
      </div>
      <div class="feature-grid">
        <?php foreach ($friends as $f): ?>
          <article class="feature-tile reveal-on-enter">
            <div class="feature-tile__icon"><i class="fa-solid fa-link"></i></div>
            <h3><?php echo htmlspecialchars($f['label']); ?></h3>
            <p><?php echo htmlspecialchars($f['note']); ?></p>
            <a class="tile-link" href="<?php echo htmlspecialchars($f['url']); ?>" target="_blank" rel="noopener">Visit →</a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
