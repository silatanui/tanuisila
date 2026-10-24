<?php
/**
 * Publish/update the Reciprocal Coordination essay (local or production).
 *
 *   php database/publish_reciprocal_post.php
 */
declare(strict_types=1);

require __DIR__ . '/../config/config.php';

$slug = 'reciprocal-coordination-and-spontaneous-phase-synchronization-in-multi-agent-ai-systems';
$title = 'Reciprocal Coordination and Spontaneous Phase Synchronization in Multi-Agent AI Systems.';
$excerpt = 'Two flawless AI agents can still fail together. This essay unpacks reciprocal coordination and spontaneous phase synchronization, from singing AIs to drones, traffic, and collaborative tools, with new figures.';
$articlePath = __DIR__ . '/posts/reciprocal-coordination-article.html';
$content = is_file($articlePath) ? file_get_contents($articlePath) : false;
if ($content === false || trim($content) === '') {
    fwrite(STDERR, "Missing {$articlePath}\n");
    exit(1);
}

$featured = 'assets/images/blog/reciprocal/hero.jpg';
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
        'AI Systems',
        'multi-agent,coordination,synchronization,AI,systems',
        14,
        'Reciprocal Coordination in Multi-Agent AI Systems',
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
        'AI Systems',
        'multi-agent,coordination,synchronization,AI,systems',
        "Tanui Kipng'etich Sila",
        14,
        $now,
        'Reciprocal Coordination in Multi-Agent AI Systems',
        $excerpt,
    ]);
    echo 'Inserted post #' . $pdo->lastInsertId() . " ({$slug})\n";
}

echo "Images: assets/images/blog/reciprocal/\n";
echo "Local:  http://localhost/tanuisila/public/post.php?slug={$slug}\n";
