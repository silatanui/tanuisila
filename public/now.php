<?php
$activePage = 'now';
require_once __DIR__ . '/header.php';
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Now</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>What I'm focused on</h2>
        <p>A living snapshot of current work, study, and side quests — inspired by the classic /now page format.</p>
      </div>
      <div class="status-list reveal-on-enter">
        <div class="status-row">
          <div class="status-row__label">Building</div>
          <div class="status-row__value">Shipping portfolio tooling, SaaS experiments (CV Studio), and production-ready PHP systems.</div>
        </div>
        <div class="status-row">
          <div class="status-row__label">Learning</div>
          <div class="status-row__value">Deeper systems thinking — reverse engineering workflows, agent tooling, and applied ML evaluation.</div>
        </div>
        <div class="status-row">
          <div class="status-row__label">Reading</div>
          <div class="status-row__value">Papers and notes on multi-agent coordination, payment rails in East Africa, and clean software architecture.</div>
        </div>
        <div class="status-row">
          <div class="status-row__label">Available</div>
          <div class="status-row__value">Open to thoughtful collaborations, research chats, and high-impact engineering work.</div>
        </div>
        <div class="status-row">
          <div class="status-row__label">Updated</div>
          <div class="status-row__value"><?php echo date('F Y'); ?></div>
        </div>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
