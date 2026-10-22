<?php
$activePage = 'colophon';
require_once __DIR__ . '/header.php';
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Colophon</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>How this site is built</h2>
        <p>A short technical credit roll — stack, fonts, and deployment — for curious visitors.</p>
      </div>
      <div class="status-list reveal-on-enter">
        <div class="status-row">
          <div class="status-row__label">Stack</div>
          <div class="status-row__value">PHP + MySQL (PDO), vanilla CSS/JS, Apache on XAMPP / LiteSpeed on production.</div>
        </div>
        <div class="status-row">
          <div class="status-row__label">Type</div>
          <div class="status-row__value">Outfit via Google Fonts; sharp corners and an architectural grid.</div>
        </div>
        <div class="status-row">
          <div class="status-row__label">Icons</div>
          <div class="status-row__value">Font Awesome 6 for navigation and status cues.</div>
        </div>
        <div class="status-row">
          <div class="status-row__label">Source</div>
          <div class="status-row__value"><a href="https://github.com/silatanui/tanuisila" target="_blank" rel="noopener">github.com/silatanui/tanuisila</a></div>
        </div>
        <div class="status-row">
          <div class="status-row__label">Deploy</div>
          <div class="status-row__value">GitHub → cPanel Git Version Control with <code>.cpanel.yml</code>.</div>
        </div>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
