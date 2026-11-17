<?php
/**
 * Recover views/clicks for a re-created project after delete.
 *
 * Deleting a project removes the projects row (and its views/clicks columns)
 * but leaves project_events rows keyed by the old id. This script:
 *   1) Lists orphan event totals (old project ids with no projects row)
 *   2) Remaps those events onto the new project (by title or --new-id)
 *   3) Recomputes projects.views / projects.clicks from project_events
 *
 * Usage (dry-run first):
 *   php database/recover_project_counts.php --title="PlantGrid"
 *   php database/recover_project_counts.php --title="PlantGrid" --old-id=642 --apply
 *   php database/recover_project_counts.php --new-id=650 --old-id=642 --apply
 */
declare(strict_types=1);

require __DIR__ . '/../config/config.php';

$args = [
    'title' => null,
    'old-id' => null,
    'new-id' => null,
    'apply' => false,
];
foreach ($argv as $i => $arg) {
    if ($i === 0) {
        continue;
    }
    if ($arg === '--apply') {
        $args['apply'] = true;
        continue;
    }
    if (preg_match('/^--title=(.+)$/', $arg, $m)) {
        $args['title'] = trim($m[1], "\"'");
        continue;
    }
    if (preg_match('/^--old-id=(\d+)$/', $arg, $m)) {
        $args['old-id'] = (int) $m[1];
        continue;
    }
    if (preg_match('/^--new-id=(\d+)$/', $arg, $m)) {
        $args['new-id'] = (int) $m[1];
        continue;
    }
}

echo "=== Orphan project_events (deleted project ids) ===\n";
$orphans = $pdo->query("
    SELECT e.project_id AS old_id,
           SUM(e.event_type = 'view') AS views,
           SUM(e.event_type = 'click') AS clicks,
           MIN(e.created_at) AS first_at,
           MAX(e.created_at) AS last_at
    FROM project_events e
    LEFT JOIN projects p ON p.id = e.project_id
    WHERE p.id IS NULL
    GROUP BY e.project_id
    ORDER BY e.project_id
")->fetchAll(PDO::FETCH_ASSOC);

if (!$orphans) {
    echo "(none — either events were deleted too, or nothing was orphaned)\n";
} else {
    foreach ($orphans as $o) {
        echo "old_id={$o['old_id']}  views={$o['views']}  clicks={$o['clicks']}  first={$o['first_at']}  last={$o['last_at']}\n";
    }
}

$newId = $args['new-id'];
if ($newId === null && $args['title']) {
    $stmt = $pdo->prepare('SELECT id, title, url, views, clicks FROM projects WHERE title = ? OR url LIKE ? ORDER BY id DESC LIMIT 5');
    $stmt->execute([$args['title'], '%' . $args['title'] . '%']);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\n=== Projects matching title/url ===\n";
    foreach ($matches as $m) {
        echo "id={$m['id']} title={$m['title']} views={$m['views']} clicks={$m['clicks']} url={$m['url']}\n";
    }
    if (count($matches) === 1) {
        $newId = (int) $matches[0]['id'];
    } elseif (count($matches) > 1) {
        echo "Multiple matches — pass --new-id=N explicitly.\n";
    } else {
        echo "No project found for title. Re-add PlantGrid in admin first, then re-run.\n";
    }
}

$oldId = $args['old-id'];
if ($oldId === null && count($orphans) === 1) {
    $oldId = (int) $orphans[0]['old_id'];
}

echo "\n=== Planned remap ===\n";
echo 'old-id: ' . ($oldId ?? '(pass --old-id=N)') . "\n";
echo 'new-id: ' . ($newId ?? '(pass --new-id=N or --title=PlantGrid)') . "\n";
echo 'mode:   ' . ($args['apply'] ? 'APPLY' : 'dry-run') . "\n";

if ($oldId === null || $newId === null) {
    echo "\nPick the orphan old_id that was PlantGrid (often the newest orphan),\n";
    echo "confirm the new PlantGrid id from admin/projects, then run with --apply.\n";
    echo "Example:\n";
    echo "  php database/recover_project_counts.php --title=\"PlantGrid\" --old-id=642 --apply\n";
    exit(0);
}

$checkOld = $pdo->prepare("SELECT
    SUM(event_type='view') views,
    SUM(event_type='click') clicks
    FROM project_events WHERE project_id = ?");
$checkOld->execute([$oldId]);
$oldCounts = $checkOld->fetch(PDO::FETCH_ASSOC) ?: ['views' => 0, 'clicks' => 0];

$proj = $pdo->prepare('SELECT id, title, views, clicks FROM projects WHERE id = ?');
$proj->execute([$newId]);
$newProj = $proj->fetch(PDO::FETCH_ASSOC);
if (!$newProj) {
    fwrite(STDERR, "New project id {$newId} not found.\n");
    exit(1);
}

echo "Will move events from #{$oldId} (views={$oldCounts['views']}, clicks={$oldCounts['clicks']})\n";
echo "onto project #{$newId} \"{$newProj['title']}\" (current views={$newProj['views']}, clicks={$newProj['clicks']})\n";

if (!$args['apply']) {
    echo "\nDry-run only. Add --apply to commit.\n";
    exit(0);
}

$pdo->beginTransaction();
try {
    $pdo->prepare('UPDATE project_events SET project_id = ? WHERE project_id = ?')
        ->execute([$newId, $oldId]);

    $pdo->prepare("
        UPDATE projects p
        SET
          views = (SELECT COUNT(*) FROM project_events e WHERE e.project_id = p.id AND e.event_type = 'view'),
          clicks = (SELECT COUNT(*) FROM project_events e WHERE e.project_id = p.id AND e.event_type = 'click')
        WHERE p.id = ?
    ")->execute([$newId]);

    $proj->execute([$newId]);
    $after = $proj->fetch(PDO::FETCH_ASSOC);
    $pdo->commit();
    echo "\nDone. #{$newId} \"{$after['title']}\" now views={$after['views']} clicks={$after['clicks']}\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Failed: ' . $e->getMessage() . "\n");
    exit(1);
}
