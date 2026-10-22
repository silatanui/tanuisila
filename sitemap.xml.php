<?php
header('Content-Type: application/xml; charset=utf-8');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
$site = $scheme . '://' . $host . ($base === '' || $base === '/' ? '' : $base);

$pages = [
  'index.php', 'about.php', 'projects.php', 'blog.php', 'contact.php',
  'now.php', 'uses.php', 'lab.php', 'saas.php', 'tools.php',
  'reverse-engineering.php', 'reading.php', 'guestbook.php', 'faq.php',
  'changelog.php', 'colophon.php', 'resources.php', 'friends.php',
  'talks.php', 'open-source.php', 'sitemap.php',
];

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as $page): ?>
  <url>
    <loc><?php echo htmlspecialchars($site . '/' . $page); ?></loc>
    <changefreq>weekly</changefreq>
  </url>
<?php endforeach; ?>
</urlset>
