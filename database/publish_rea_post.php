<?php
/**
 * Publish/update the REA (Reverse Engineer Anything) essay (local or production).
 *
 *   php database/publish_rea_post.php
 */
declare(strict_types=1);

require __DIR__ . '/../config/config.php';

$slug = 'rea-reverse-engineer-anything';
$title = 'REA: Reverse Engineer Anything.';
$excerpt = 'I pointed REA at the live Excalidraw whiteboard and built Quire, an original board you can open and draw on. Here is the prompt, what the inspection recovered, and the jobs I would not hand to REA.';
$articlePath = __DIR__ . '/posts/rea-reverse-engineer-anything-article.html';
$content = is_file($articlePath) ? file_get_contents($articlePath) : false;
if ($content === false || trim($content) === '') {
    fwrite(STDERR, "Missing {$articlePath}\n");
    exit(1);
}

$featured = 'assets/images/blog/rea/hero.png';
$now = date('Y-m-d H:i:s');

$exists = $pdo->prepare('SELECT id FROM blog_posts WHERE slug = ? LIMIT 1');
$exists->execute([$slug]);
$id = $exists->fetchColumn();

if ($id) {
    $stmt = $pdo->prepare("UPDATE blog_posts SET
        title = ?, excerpt = ?, content = ?, featured_image = ?, category = ?, tags = ?,
        status = 'published', reading_time = ?,
        seo_title = ?, seo_description = ?, allow_comments = 1
        WHERE id = ?");
    $stmt->execute([
        $title,
        $excerpt,
        $content,
        $featured,
        'Tools',
        'REA,Quire,Excalidraw,reverse-engineering,MCP,agents',
        18,
        'REA: Reverse Engineer Anything',
        $excerpt,
        $id,
    ]);
    echo "Updated post #{$id} ({$slug})\n";
} else {
    $stmt = $pdo->prepare("INSERT INTO blog_posts
        (title, slug, excerpt, content, featured_image, category, tags, author_name, status, reading_time, published_at, seo_title, seo_description, views, allow_comments)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published', ?, ?, ?, ?, 0, 1)");
    $stmt->execute([
        $title,
        $slug,
        $excerpt,
        $content,
        $featured,
        'Tools',
        'REA,Quire,Excalidraw,reverse-engineering,MCP,agents',
        "Tanui Kipng'etich Sila",
        18,
        $now,
        'REA: Reverse Engineer Anything',
        $excerpt,
    ]);
    echo 'Inserted post #' . $pdo->lastInsertId() . " ({$slug})\n";
}

echo "Images: assets/images/blog/rea/\n";
echo "Local:  http://localhost/tanuisila/public/post.php?slug={$slug}\n";
