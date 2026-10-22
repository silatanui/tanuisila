<?php
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/rss+xml; charset=utf-8');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$site = $scheme . '://' . $host . ($base === '' || $base === '/' ? '' : $base);

$posts = [];
try {
  $posts = $pdo->query("SELECT title, slug, excerpt, published_at FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $posts = [];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0">
  <channel>
    <title>Tanui Kipng&apos;etich Sila — Blog</title>
    <link><?php echo htmlspecialchars($site); ?>/</link>
    <description>Writing and insights from Tanui Kipng&apos;etich Sila</description>
    <language>en</language>
<?php foreach ($posts as $post): ?>
    <item>
      <title><?php echo htmlspecialchars($post['title'], ENT_XML1); ?></title>
      <link><?php echo htmlspecialchars($site . '/post.php?slug=' . rawurlencode($post['slug'])); ?></link>
      <guid><?php echo htmlspecialchars($site . '/post.php?slug=' . rawurlencode($post['slug'])); ?></guid>
      <pubDate><?php echo !empty($post['published_at']) ? date(DATE_RSS, strtotime($post['published_at'])) : date(DATE_RSS); ?></pubDate>
      <description><![CDATA[<?php echo htmlspecialchars($post['excerpt'] ?? ''); ?>]]></description>
    </item>
<?php endforeach; ?>
  </channel>
</rss>
