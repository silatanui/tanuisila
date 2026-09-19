<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/BlogAIHelper.php';

// Check authorization
if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if (empty($action)) {
        throw new Exception('No action specified.');
    }

    if (empty($content)) {
        throw new Exception('Please enter or paste your blog content first into the editor.');
    }

    $aiHelper = new BlogAIHelper(OPENAI_API_KEY);
    $response = [];

    switch ($action) {
        case 'auto_generate_all':
        case 'generate_all_from_content':
            $response = $aiHelper->generateAllFromContent($content, $title, $category);
            break;

        case 'generate_title':
            $generatedTitle = $aiHelper->generateTitle($content);
            $generatedSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $generatedTitle));
            $generatedSlug = trim($generatedSlug, '-');
            $response['title'] = $generatedTitle;
            $response['slug'] = $generatedSlug;
            break;

        case 'generate_category':
            $response['category'] = $aiHelper->generateCategory($content, $title);
            break;

        case 'generate_excerpt':
            $response['excerpt'] = $aiHelper->generateExcerpt($title, $content);
            break;

        case 'generate_seo_title':
            $response['seo_title'] = $aiHelper->generateSEOTitle($title, $content);
            break;

        case 'generate_seo_description':
            $response['seo_description'] = $aiHelper->generateSEODescription($title, $content);
            break;

        case 'generate_tags':
            $response['tags'] = $aiHelper->generateTags($title, $content, $category);
            break;

        case 'analyze_content':
            $response = $aiHelper->analyzeContent($content);
            break;

        case 'full_optimization':
            if (empty($title)) {
                $response = $aiHelper->generateAllFromContent($content, '', $category);
            } else {
                $response['excerpt'] = $aiHelper->generateExcerpt($title, $content);
                $response['seo_title'] = $aiHelper->generateSEOTitle($title, $content);
                $response['seo_description'] = $aiHelper->generateSEODescription($title, $content);
                $response['tags'] = $aiHelper->generateTags($title, $content, $category);
                $analysis = $aiHelper->analyzeContent($content);
                $response['analysis'] = $analysis;
                $response['reading_time'] = $analysis['reading_time'];
            }
            break;

        default:
            throw new Exception('Unknown AI action: ' . $action);
    }

    echo json_encode(['success' => true, 'data' => $response]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

