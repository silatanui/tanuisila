<?php
// Simple DB verification helper. Place in the site root and open in a browser.
require_once __DIR__ . '/config.php';

echo "<h1>DB Check</h1>";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!$tables) {
        echo "<p>No tables found in database <strong>" . htmlspecialchars(DB_NAME) . "</strong>.</p>";
        exit;
    }
    echo "<p>Connected to <strong>" . htmlspecialchars(DB_NAME) . "</strong>. Found " . count($tables) . " tables.</p>";
    echo "<ul>";
    foreach ($tables as $t) {
        $c = $pdo->query("SELECT COUNT(*) FROM `" . $t . "`")->fetchColumn();
        echo "<li>" . htmlspecialchars($t) . " — " . intval($c) . " rows</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p>Error querying database: " . htmlspecialchars($e->getMessage()) . "</p>";
}
