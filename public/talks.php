<?php
$activePage = 'talks';
require_once __DIR__ . '/header.php';

$talks = [
  ['title' => 'Building purposeful software', 'meta' => 'Internal / workshop style', 'note' => 'On framing problems before picking frameworks.'],
  ['title' => 'AI tools without the hype', 'meta' => 'Dev circle', 'note' => 'Practical API workflows and evaluation loops.'],
  ['title' => 'Reading systems, not just code', 'meta' => 'Interest talk', 'note' => 'Gentle intro to reverse-engineering habits for developers.'],
];
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Talks</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Talks &amp; sessions</h2>
        <p>Topics I enjoy presenting — invite me if your meetup needs a clear, practical session.</p>
      </div>
      <div class="status-list">
        <?php foreach ($talks as $t): ?>
          <div class="status-row reveal-on-enter">
            <div class="status-row__label"><?php echo htmlspecialchars($t['meta']); ?></div>
            <div class="status-row__value">
              <strong style="color:var(--text);"><?php echo htmlspecialchars($t['title']); ?></strong><br>
              <?php echo htmlspecialchars($t['note']); ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <p style="margin-top:22px;"><a class="btn primary" href="<?php echo appUrl('contact.php'); ?>">Invite me to speak →</a></p>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
