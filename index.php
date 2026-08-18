<?php
/**
 * Entry point for the portfolio application
 * Routes to the appropriate section
 */

$page = $_GET['page'] ?? '';

if ($page === 'admin') {
    header("Location: admin/index.php");
    exit;
} else {
    require __DIR__ . '/public/index.php';
}
?>
