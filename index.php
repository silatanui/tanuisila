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
    header("Location: public/index.php");
    exit;
}
?>
