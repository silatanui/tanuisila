<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/BlogAIHelper.php';

// Check authorization
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $category = $_POST['category'] ?? '';

    if (empty($action)) {
        throw new Exception('No action specified');
    }

    $aiHelper = new BlogAIHelper(OPENAI_API_KEY);

    $response = [];

    switch ($action) {
        case 'analyze_content':
            if (empty($content)) {
                throw new Exception('Content is required');
            }
            $response = $aiHelper->analyzeContent($content);
            break;

        case 'generate_excerpt':
            if (empty($title) || empty($content)) {
                throw new Exception('Title and content are required');
            }
            $response['excerpt'] = $aiHelper->generateExcerpt($title, $content);
            break;

        case 'generate_seo_title':
            if (empty($title) || empty($content)) {
                throw new Exception('Title and content are required');
            }
            $response['seo_title'] = $aiHelper->generateSEOTitle($title, $content);
            break;

        case 'generate_seo_description':
            if (empty($title) || empty($content)) {
                throw new Exception('Title and content are required');
            }
            $response['seo_description'] = $aiHelper->generateSEODescription($title, $content);
            break;

        case 'generate_tags':
            if (empty($title) || empty($content)) {
                throw new Exception('Title and content are required');
            }
            $response['tags'] = $aiHelper->generateTags($title, $content, $category);
            break;

        case 'full_optimization':
            if (empty($title) || empty($content)) {
                throw new Exception('Title and content are required');
            }
            // Run all AI operations
            $response['excerpt'] = $aiHelper->generateExcerpt($title, $content);
            $response['seo_title'] = $aiHelper->generateSEOTitle($title, $content);
            $response['seo_description'] = $aiHelper->generateSEODescription($title, $content);
            $response['tags'] = $aiHelper->generateTags($title, $content, $category);
            $response['analysis'] = $aiHelper->analyzeContent($content);
            break;

        default:
            throw new Exception('Unknown action: ' . $action);
    }

    echo json_encode(['success' => true, 'data' => $response]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
