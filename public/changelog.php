<?php
$activePage = 'changelog';
require_once __DIR__ . '/header.php';

$entries = [
  ['date' => '2026-10-22', 'title' => 'Design polish', 'body' => 'Brand-first hero, quieter footer, featured blog treatment, scroll header.'],
  ['date' => '2026-10-22', 'title' => 'Personal surfaces', 'body' => 'Now, Uses, Lab, SaaS, Reading, and reverse-engineering interest pages.'],
  ['date' => '2026-10-19', 'title' => 'Responsive pass', 'body' => 'Fixed horizontal overflow on blog cards, topbar, and tablet layouts.'],
  ['date' => '2026-10', 'title' => 'Blog reading layout', 'body' => 'Focused article column with sticky related sidebar and comments.'],
];
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Changelog</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Site changelog</h2>
        <p>What changed on this website, newest first — like a product release log for a personal site.</p>
      </div>
      <div class="status-list reveal-on-enter">
        <?php foreach ($entries as $e): ?>
          <div class="status-row">
            <div class="status-row__label"><?php echo htmlspecialchars($e['date']); ?></div>
            <div class="status-row__value">
              <strong style="color:var(--text);"><?php echo htmlspecialchars($e['title']); ?></strong><br>
              <?php echo htmlspecialchars($e['body']); ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
