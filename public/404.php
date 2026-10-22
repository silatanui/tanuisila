<?php
http_response_code(404);
$activePage = '404';
require_once __DIR__ . '/header.php';
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">404</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Page not found</h2>
        <p>That route does not exist — try the sitemap, Lab, or jump home.</p>
      </div>
      <div class="tool-actions">
        <a class="btn primary" href="<?php echo appUrl('index.php'); ?>">Home</a>
        <a class="btn" href="<?php echo appUrl('sitemap.php'); ?>">Sitemap</a>
        <a class="btn" href="<?php echo appUrl('lab.php'); ?>">Lab</a>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
