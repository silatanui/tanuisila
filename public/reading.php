<?php
$activePage = 'reading';
require_once __DIR__ . '/header.php';

$shelf = [
  ['title' => 'Designing Data-Intensive Applications', 'note' => 'Reliability, scaling, and storage mental models.'],
  ['title' => 'The True Believer (Hoffer)', 'note' => 'Mass movements and identity — feeds essay thinking.'],
  ['title' => 'Papers on multi-agent sync', 'note' => 'Coordination, phase locking, and emergent timing.'],
  ['title' => 'Clean Architecture notes', 'note' => 'Boundaries that keep PHP apps maintainable.'],
  ['title' => 'East Africa payment rails', 'note' => 'M-PESA, cards, and product implications.'],
  ['title' => 'Reverse engineering primers', 'note' => 'Formats, tooling maps, and ethical practice.'],
];
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Reading</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Reading shelf</h2>
        <p>Books, papers, and threads that are actively influencing how problems get framed.</p>
      </div>
      <div class="status-list reveal-on-enter">
        <?php foreach ($shelf as $row): ?>
          <div class="status-row">
            <div class="status-row__label">Title</div>
            <div class="status-row__value">
              <strong style="color:var(--text);"><?php echo htmlspecialchars($row['title']); ?></strong><br>
              <span style="color:var(--muted);"><?php echo htmlspecialchars($row['note']); ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
