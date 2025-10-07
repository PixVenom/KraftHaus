<?php
/**
 * Blog Index Update API
 * Updates the blog index with new posts
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
    
    if (!$input || !isset($input['postData'])) {
        throw new Exception('Invalid JSON data');
    }
    
    $postData = $input['postData'];
    
    // Validate required fields
    $requiredFields = ['title', 'slug', 'excerpt', 'content'];
    foreach ($requiredFields as $field) {
        if (empty($postData[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Create blogs directory if it doesn't exist
    $blogsDir = '../blogs/';
    if (!is_dir($blogsDir)) {
        mkdir($blogsDir, 0755, true);
    }
    
    $indexFile = $blogsDir . 'index.json';
    
    // Load existing posts
    $existingPosts = [];
    if (file_exists($indexFile)) {
        $existingContent = file_get_contents($indexFile);
        if ($existingContent) {
            $existingPosts = json_decode($existingContent, true) ?: [];
        }
    }
    
    // Create new post entry
    $newPost = [
        'id' => (string)(time() . rand(1000, 9999)),
        'title' => $postData['title'],
        'slug' => $postData['slug'],
        'date' => date('c'), // ISO 8601 format
        'imageUrl' => $postData['imageUrl'] ?? null,
        'excerpt' => $postData['excerpt'],
        'category' => $postData['category'] ?? 'Photography / Module Design',
        'author' => $postData['author'] ?? 'KraftHaus Team'
    ];
    
    // Add new post to the beginning of the array
    array_unshift($existingPosts, $newPost);
    
    // Limit to 50 most recent posts
    $existingPosts = array_slice($existingPosts, 0, 50);
    
    // Save updated index
    $indexContent = json_encode($existingPosts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (file_put_contents($indexFile, $indexContent) === false) {
        throw new Exception('Failed to update blog index');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Blog index updated successfully',
        'postCount' => count($existingPosts),
        'newPost' => $newPost
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
