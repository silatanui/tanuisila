<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    json_response(['error' => 'Unauthorized']);
    exit;
}

function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Invalid request method'], 405);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    json_response(['error' => 'No file uploaded or upload error'], 400);
    exit;
}

$uploadDir = __DIR__ . '/../assets/images/blog/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        json_response(['error' => 'Failed to create upload directory'], 500);
        exit;
    }
}

$file = $_FILES['file'];
$mimeType = $file['type'];
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

if (!in_array($mimeType, $allowedMimes)) {
    json_response(['error' => 'Invalid file type. Only images are allowed.'], 400);
    exit;
}

$fileSize = $file['size'];
$maxSize = 5 * 1024 * 1024; // 5MB

if ($fileSize > $maxSize) {
    json_response(['error' => 'File too large. Maximum 5MB allowed.'], 400);
    exit;
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'blog-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
$filePath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $filePath)) {
    // Return the URL for the editor
    $imageUrl = '/tanuisila/assets/images/blog/' . $filename;
    json_response([
        'location' => $imageUrl,
        'success' => true
    ]);
} else {
    json_response(['error' => 'Failed to save file'], 500);
}
?>
