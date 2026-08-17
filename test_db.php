<?php
require_once 'config/config.php';

echo "Experience entries:\n";
$exp = $pdo->query('SELECT id, company FROM experience ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
echo "Count: " . count($exp) . "\n";
foreach ($exp as $e) {
    echo "  ID: " . $e['id'] . ", Company: " . $e['company'] . "\n";
}

echo "\nEducation entries:\n";
$edu = $pdo->query('SELECT id, institution FROM education ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
echo "Count: " . count($edu) . "\n";
foreach ($edu as $e) {
    echo "  ID: " . $e['id'] . ", Institution: " . $e['institution'] . "\n";
}
