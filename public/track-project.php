<?php
/**
 * Beacon endpoint for project card views and outbound launch clicks.
 * POST JSON: { "project_id": 1, "event": "view"|"click" }
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/analytics.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '[]', true);
if (!is_array($data)) {
    $data = $_POST;
}

$projectId = (int) ($data['project_id'] ?? 0);
$event = strtolower(trim((string) ($data['event'] ?? 'view')));
if ($event !== 'click') {
    $event = 'view';
}

if ($projectId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'project_id']);
    exit;
}

try {
    $exists = $pdo->prepare('SELECT id FROM projects WHERE id = ? LIMIT 1');
    $exists->execute([$projectId]);
    if (!$exists->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'not_found']);
        exit;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'db']);
    exit;
}

$counted = trackProjectEvent($pdo, $projectId, $event);
echo json_encode(['ok' => true, 'counted' => $counted]);
