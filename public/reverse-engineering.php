<?php
$activePage = 're';
require_once __DIR__ . '/header.php';

$tracks = [
  ['File triage', 'Identify formats, headers, and entropy before deeper analysis.'],
  ['Static reading', 'Disassembly, strings, imports, and control-flow sketches.'],
  ['Dynamic checks', 'Debugging and tracing when the binary only talks at runtime.'],
  ['Formats & protocols', 'Undocumented layouts, parsers, and careful reconstruction.'],
  ['Learning loop', 'Compiler Explorer, CTF-style puzzles, and annotated write-ups.'],
  ['Ethics first', 'Only systems you own or have explicit permission to examine.'],
];
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Interests</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Reverse engineering</h2>
        <p>A personal interest area: understanding how software is assembled, packed, and communicated — with a strong bias toward learning and responsible practice.</p>
      </div>
      <div class="feature-grid">
        <?php foreach ($tracks as [$title, $copy]): ?>
          <article class="feature-tile reveal-on-enter">
            <div class="feature-tile__icon"><i class="fa-solid fa-microchip"></i></div>
            <h3><?php echo htmlspecialchars($title); ?></h3>
            <p><?php echo htmlspecialchars($copy); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
      <p style="margin-top:24px;">
        <a class="btn" href="<?php echo appUrl('tools.php'); ?>">Try Byte Lab tools →</a>
      </p>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
