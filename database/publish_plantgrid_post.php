<?php
/**
 * Publish/update the PlantGrid operations desk essay (local or production).
 *
 *   php database/publish_plantgrid_post.php
 */
declare(strict_types=1);

require __DIR__ . '/../config/config.php';

$slug = 'plantgrid-operations-desk';
$title = 'PlantGrid.';
$excerpt = 'A portfolio demo of an ICT operations console for plant work: cases, assets, maps, messages, and the modules around them.';
$articlePath = __DIR__ . '/posts/plantgrid-operations-desk-article.html';
$content = is_file($articlePath) ? file_get_contents($articlePath) : false;
if ($content === false || trim($content) === '') {
    fwrite(STDERR, "Missing {$articlePath}\n");
    exit(1);
}

// Strip copy markers if present when pasting from plantgrid/article.html
$content = preg_replace('/<!--\s*[👇👆].*?-->\s*/u', '', $content) ?? $content;

$featured = 'assets/images/blog/plantgrid/dashboard-overview.png';
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
        'Projects',
        'PlantGrid,ICT,operations,manufacturing,cases,portfolio',
        18,
        'PlantGrid — ICT operations desk for a fictional plant',
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
        'Projects',
        'PlantGrid,ICT,operations,manufacturing,cases,portfolio',
        "Tanui Kipng'etich Sila",
        18,
        $now,
        'PlantGrid — ICT operations desk for a fictional plant',
        $excerpt,
    ]);
    echo 'Inserted post #' . $pdo->lastInsertId() . " ({$slug})\n";
}

echo "Images: assets/images/blog/plantgrid/\n";
echo "Copy-paste source: plantgrid/article.html\n";
echo "Local:  http://localhost/tanuisila/public/post.php?slug={$slug}\n";
echo "Demo:   https://plantgrid.tanuisila.dev/dashboard\n";
