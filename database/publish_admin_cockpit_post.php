<?php
/**
 * Publish/update the Admin Cockpit blog post (local or production).
 *
 * Usage (cPanel Terminal or local):
 *   php database/publish_admin_cockpit_post.php
 *
 * Images ship with the repo under:
 *   assets/images/blog/admin-cockpit/
 */
declare(strict_types=1);

require __DIR__ . '/../config/config.php';

$title = 'Admin Cockpit';
$slug = 'admin-cockpit-how-i-manage-my-portfolio-dashboard';
$excerpt = 'How I track visitors, posts, and inbox noise from one admin dashboard — with maps, adaptive legends, and charts that match how I edit.';
$articlePath = __DIR__ . '/posts/admin-cockpit-article.html';
$content = is_file($articlePath) ? file_get_contents($articlePath) : false;
if ($content === false || trim($content) === '') {
    fwrite(STDERR, "Missing article at database/posts/admin-cockpit-article.html\n");
    exit(1);
}

// Path works with portfolioAssetUrl() on both /tanuisila (XAMPP) and domain root (cPanel).
$featured = 'assets/images/blog/admin-cockpit/dashboard-overview.png';
$now = date('Y-m-d H:i:s');

$exists = $pdo->prepare('SELECT id FROM blog_posts WHERE slug = ? LIMIT 1');
$exists->execute([$slug]);
$id = $exists->fetchColumn();

if ($id) {
    $stmt = $pdo->prepare("UPDATE blog_posts SET
        title = ?, excerpt = ?, content = ?, featured_image = ?, category = ?, tags = ?,
        status = 'published', reading_time = ?, published_at = COALESCE(published_at, ?),
        seo_title = ?, seo_description = ?, allow_comments = 1
        WHERE id = ?");
    $stmt->execute([
        $title,
        $excerpt,
        $content,
        $featured,
        'Engineering',
        'admin,analytics,portfolio,dashboard,charts',
        12,
        $now,
        'Admin Cockpit — How I Manage My Portfolio Dashboard',
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
        'Engineering',
        'admin,analytics,portfolio,dashboard,charts',
        "Tanui Kipng'etich Sila",
        12,
        $now,
        'Admin Cockpit — How I Manage My Portfolio Dashboard',
        $excerpt,
    ]);
    echo 'Inserted post #' . $pdo->lastInsertId() . " ({$slug})\n";
}

echo "Images: assets/images/blog/admin-cockpit/\n";
echo "Slug:   {$slug}\n";
