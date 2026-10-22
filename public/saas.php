<?php
$activePage = 'saas';
require_once __DIR__ . '/header.php';

$products = [
  [
    'name' => 'CV Studio',
    'tag' => 'Live',
    'url' => appUrl('cv_studio/'),
    'desc' => 'AI-assisted resume builder with ATS-aware layouts and realtime section editing.',
  ],
  [
    'name' => 'Portfolio OS',
    'tag' => 'This site',
    'url' => appUrl('index.php'),
    'desc' => 'CMS-backed personal site with admin, blog AI helpers, and analytics hooks.',
  ],
  [
    'name' => 'LyricSync',
    'tag' => 'Experiment',
    'url' => appUrl('LyricSync/'),
    'desc' => 'Media-aligned lyric tooling — timing, sync, and listening workflows.',
  ],
  [
    'name' => 'Flyleaf',
    'tag' => 'Lab',
    'url' => appUrl('Flyleaf/'),
    'desc' => 'Reading and document intelligence experiments under active development.',
  ],
];
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">SaaS</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Products &amp; experiments</h2>
        <p>Small software products with a SaaS mindset: clear problem, usable interface, ship iteratively.</p>
      </div>
      <div class="feature-grid">
        <?php foreach ($products as $p): ?>
          <article class="feature-tile reveal-on-enter">
            <div class="feature-tile__icon"><i class="fa-solid fa-rocket"></i></div>
            <h3><?php echo htmlspecialchars($p['name']); ?> <span style="font-size:0.7rem;color:var(--accent);letter-spacing:0.08em;text-transform:uppercase;"><?php echo htmlspecialchars($p['tag']); ?></span></h3>
            <p><?php echo htmlspecialchars($p['desc']); ?></p>
            <a class="tile-link" href="<?php echo htmlspecialchars($p['url']); ?>" <?php echo str_starts_with($p['url'], 'http') ? 'target="_blank" rel="noopener"' : ''; ?>>Launch →</a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
