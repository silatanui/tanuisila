<?php
$activePage = 'faq';
require_once __DIR__ . '/header.php';

$faqs = [
  ['Are you available for work?', 'Yes — thoughtful collaborations, research chats, and high-impact engineering. Use the contact page.'],
  ['Do you build SaaS products?', 'Yes. CV Studio and other experiments ship with a product mindset: clear problem, usable UI, iterate fast.'],
  ['What is Byte Lab?', 'A small in-browser toolkit for Base64/hex conversion useful during reverse-engineering triage.'],
  ['Can I reuse code from this site?', 'The portfolio source is on GitHub. Respect licenses on dependencies and do not copy private credentials.'],
  ['How do I read the blog offline?', 'Use the RSS feed at feed.php with your favorite reader.'],
];
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">FAQ</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Frequently asked</h2>
        <p>Short answers about availability, products, and how this site works.</p>
      </div>
      <div class="status-list">
        <?php foreach ($faqs as $i => $faq): ?>
          <div class="status-row reveal-on-enter">
            <div class="status-row__label">Q<?php echo $i + 1; ?></div>
            <div class="status-row__value">
              <strong style="color:var(--text);"><?php echo htmlspecialchars($faq[0]); ?></strong><br>
              <?php echo htmlspecialchars($faq[1]); ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
