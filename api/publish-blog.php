<?php
/**
 * Blog Publishing API
 * Handles blog post creation and updates
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get the request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON data');
    }
    
    $filename = $input['filename'] ?? '';
    $content = $input['content'] ?? '';
    $postData = $input['postData'] ?? [];
    
    if (empty($filename) || empty($content)) {
        throw new Exception('Missing required data');
    }
    
    // Create blogs directory if it doesn't exist
    $blogsDir = '../blogs/';
    if (!is_dir($blogsDir)) {
        mkdir($blogsDir, 0755, true);
    }
    
    // Save the blog post file
    $filePath = $blogsDir . $filename;
    if (file_put_contents($filePath, $content) === false) {
        throw new Exception('Failed to save blog post file');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Blog post created successfully',
        'filename' => $filename,
        'path' => $filePath
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
