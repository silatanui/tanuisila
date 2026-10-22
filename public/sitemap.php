<?php
$activePage = 'sitemap';
require_once __DIR__ . '/header.php';

$groups = [
  'Main' => [
    ['Home', 'index.php'],
    ['About', 'about.php'],
    ['Projects', 'projects.php'],
    ['Blog', 'blog.php'],
    ['Contact', 'contact.php'],
  ],
  'Personal' => [
    ['Now', 'now.php'],
    ['Uses', 'uses.php'],
    ['Reading', 'reading.php'],
    ['Guestbook', 'guestbook.php'],
    ['FAQ', 'faq.php'],
  ],
  'Lab & products' => [
    ['Lab', 'lab.php'],
    ['SaaS', 'saas.php'],
    ['Tools', 'tools.php'],
    ['Reverse Engineering', 'reverse-engineering.php'],
    ['Open Source', 'open-source.php'],
  ],
  'Meta' => [
    ['Changelog', 'changelog.php'],
    ['Colophon', 'colophon.php'],
    ['Resources', 'resources.php'],
    ['Friends', 'friends.php'],
    ['Talks', 'talks.php'],
  ],
];
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Sitemap</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Everything in one map</h2>
        <p>HTML sitemap for humans — also see <a href="<?php echo appUrl('sitemap.xml'); ?>">sitemap.xml</a> for crawlers.</p>
      </div>
      <?php foreach ($groups as $title => $links): ?>
        <h3 style="margin:28px 0 12px;font-size:0.78rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--accent-muted);"><?php echo htmlspecialchars($title); ?></h3>
        <div class="feature-grid" style="margin-bottom:8px;">
          <?php foreach ($links as [$label, $href]): ?>
            <a class="feature-tile reveal-on-enter" href="<?php echo appUrl($href); ?>" style="min-height:auto;text-decoration:none;">
              <h3 style="margin:0;"><?php echo htmlspecialchars($label); ?></h3>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
