<?php
$activePage = 'resources';
require_once __DIR__ . '/header.php';

$bookmarks = [
  ['Compiler Explorer', 'https://godbolt.org/', 'See how source becomes assembly — essential RE learning.'],
  ['MDN Web Docs', 'https://developer.mozilla.org/', 'Authoritative web platform reference.'],
  ['PHP The Right Way', 'https://phptherightway.com/', 'Modern PHP practices and patterns.'],
  ['Awesome Reverse Engineering', 'https://github.com/morluto/awesome-reverse-engineering', 'Curated map of RE tools by task.'],
  ['Owain Evans blog', 'https://www.owainevans.com/', 'Long-form thinking on AI and systems (example of strong personal writing).'],
  ['IndieWeb', 'https://indieweb.org/', 'Own your content and personal site practices.'],
];
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Resources</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Bookmarks worth keeping</h2>
        <p>Hand-picked links for systems, reverse engineering, PHP craft, and personal web culture.</p>
      </div>
      <div class="feature-grid">
        <?php foreach ($bookmarks as [$title, $url, $note]): ?>
          <article class="feature-tile reveal-on-enter">
            <div class="feature-tile__icon"><i class="fa-solid fa-bookmark"></i></div>
            <h3><?php echo htmlspecialchars($title); ?></h3>
            <p><?php echo htmlspecialchars($note); ?></p>
            <a class="tile-link" href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener">Open →</a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
