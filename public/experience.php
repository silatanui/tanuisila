<?php
$activePage = 'experience';
require_once __DIR__ . '/header.php';
?>

<main class="container">
  <section id="experience" class="section-row" style="border-bottom:none;">
    <div class="section-label">Experience</div>
    <div class="section-content">
      <h2>Professional Background</h2>
      <div class="timeline-rows">
        <?php foreach ($experience as $item): ?>
          <?php 
            // Select matching company logo for Sila's experience entries
            $companyLogo = '';
            $company = strtolower($item['company']);
            if (strpos($company, 'transcosmos') !== false) {
                $companyLogo = 'transcosmos.png';
            } elseif (strpos($company, 'litmed') !== false) {
                $companyLogo = 'litmed_college_kerugoya.png';
            } elseif (strpos($company, 'infolink') !== false) {
                $companyLogo = 'infolink_college_of_technology.png';
            }
          ?>
          <div class="timeline-row">
            <div class="time-meta"><?php echo htmlspecialchars($item['start_date']); ?> — <?php echo htmlspecialchars($item['end_date'] ?: 'Present'); ?></div>
            <div class="timeline-desc" style="display: flex; gap: 20px; align-items: flex-start;">
              <?php if (!empty($companyLogo)): ?>
                <div style="flex-shrink: 0; width: 64px; height: 64px; padding: 4px; background: var(--soft-bg); border: 1px solid var(--light-gray); display: flex; align-items: center; justify-content: center;">
                  <img src="<?php echo htmlspecialchars($companyLogo); ?>?v=<?php echo @filemtime(__DIR__ . '/' . $companyLogo); ?>" alt="<?php echo htmlspecialchars($item['company']); ?> Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
              <?php endif; ?>
              <div style="flex-grow: 1;">
                <h4 style="margin: 0 0 4px;"><?php echo htmlspecialchars($item['role_name']); ?></h4>
                <div class="timeline-institution" style="margin-bottom: 8px;"><?php echo htmlspecialchars($item['company']); ?> · <span style="font-weight: 400; color: var(--accent-muted);"><?php echo htmlspecialchars($item['location']); ?></span></div>
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
