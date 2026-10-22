<?php
$activePage = 'lab';
require_once __DIR__ . '/header.php';

$experiments = [
  ['title' => 'Byte Lab', 'href' => 'tools.php', 'desc' => 'Hex, Base64, and encoding toys for quick reverse-engineering triage.'],
  ['title' => 'SaaS Bench', 'href' => 'saas.php', 'desc' => 'Product experiments shipping as small, useful applications.'],
  ['title' => 'RE Notes', 'href' => 'reverse-engineering.php', 'desc' => 'Interest area: binaries, formats, protocols, and careful analysis.'],
  ['title' => 'Reading Shelf', 'href' => 'reading.php', 'desc' => 'Papers and books currently shaping how problems get framed.'],
];
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Lab</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>The Lab</h2>
        <p>Half-built tools, experiments, and interest areas — the workshop next to the polished portfolio.</p>
      </div>
      <div class="feature-grid">
        <?php foreach ($experiments as $item): ?>
          <article class="feature-tile reveal-on-enter">
            <div class="feature-tile__icon"><i class="fa-solid fa-flask"></i></div>
            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
            <p><?php echo htmlspecialchars($item['desc']); ?></p>
            <a class="tile-link" href="<?php echo appUrl($item['href']); ?>">Open →</a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
