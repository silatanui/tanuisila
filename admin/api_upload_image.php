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

$type = preg_replace('/[^a-z0-9_-]/i', '', $_POST['type'] ?? 'blog');
if ($type === 'projects' || $type === 'project') {
    $folder = 'projects';
    $prefix = 'project-';
} else {
    $folder = 'blog';
    $prefix = 'blog-';
}

$uploadDir = __DIR__ . '/../assets/images/' . $folder . '/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        json_response(['error' => 'Failed to create upload directory'], 500);
        exit;
    }
}

$file = $_FILES['file'];
// Verify real MIME type from file contents (not the browser-supplied value)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

if (!in_array($mimeType, $allowedMimes)) {
    json_response(['error' => 'Invalid file type. Only JPG, PNG, GIF, WebP, SVG images are allowed.'], 400);
    exit;
}

$fileSize = $file['size'];
$maxSize = 10 * 1024 * 1024; // 10MB

if ($fileSize > $maxSize) {
    json_response(['error' => 'File too large. Maximum 10MB allowed.'], 400);
    exit;
}

// Generate clean unique filename
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext === 'jpeg') { $ext = 'jpg'; }
$filename = $prefix . time() . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
$filePath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $filePath)) {
    // Also copy to public directory for easy fallback
    if ($folder === 'projects') {
        @copy($filePath, __DIR__ . '/../public/' . $filename);
    }

    // Dynamic URL resolution based on script path
    $scriptDir = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/admin/api_upload_image.php'));
    $basePath = $scriptDir === '/' || $scriptDir === '\\' ? '' : rtrim(str_replace('\\', '/', $scriptDir), '/');
    $imageUrl = $basePath . '/assets/images/' . $folder . '/' . $filename;
    
    json_response([
        'location' => $imageUrl,
        'filename' => $filename,
        'url' => $imageUrl,
        'success' => true
    ]);
} else {
    json_response(['error' => 'Failed to save file to server storage'], 500);
}
?>
