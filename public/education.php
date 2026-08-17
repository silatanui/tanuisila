<?php
$activePage = 'education';
require_once __DIR__ . '/header.php';
?>

<main class="container">
  <section id="education" class="section-row" style="border-bottom:none;">
    <div class="section-label">Education</div>
    <div class="section-content">
      <h2>Completed Academic Journey</h2>
      <div class="timeline-rows">
        <?php foreach ($education as $item): ?>
          <?php 
            // Select matching University Logo asset for Sila's education cards
            $uniLogo = '';
            $institution = strtolower($item['institution']);
            if (strpos($institution, 'debrecen') !== false) {
                $uniLogo = 'DE-cimer.jpg';
            } elseif (strpos($institution, 'kirinyaga') !== false) {
                $uniLogo = 'kyu_logo.png';
            }
          ?>
          <div class="timeline-row">
            <div class="time-meta"><?php echo htmlspecialchars($item['start_year']); ?> — <?php echo htmlspecialchars($item['end_year']); ?></div>
            <div class="timeline-desc" style="display: flex; gap: 20px; align-items: flex-start;">
              <?php if (!empty($uniLogo)): ?>
                <div style="flex-shrink: 0; width: 64px; height: 64px; padding: 4px; background: var(--soft-bg); border: 1px solid var(--light-gray); display: flex; align-items: center; justify-content: center;">
                  <img src="<?php echo $uniLogo; ?>?v=<?php echo @filemtime(__DIR__ . '/' . $uniLogo); ?>" alt="<?php echo htmlspecialchars($item['institution']); ?> Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
              <?php endif; ?>
              <div style="flex-grow: 1;">
                <h4 style="margin: 0 0 4px;"><?php echo htmlspecialchars($item['degree'] ?: 'Education'); ?></h4>
                <div class="timeline-institution" style="margin-bottom: 8px;"><?php echo htmlspecialchars($item['institution']); ?> · <span style="font-weight: 400; color: var(--accent-muted);"><?php echo htmlspecialchars($item['field_name']); ?></span></div>
                <?php if (!empty($item['description'])): ?>
                  <div class="timeline-body" style="margin-top: 8px;"><?php echo nl2br(htmlspecialchars($item['description'])); ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>
