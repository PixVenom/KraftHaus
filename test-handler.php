<?php
// Minimal test handler to isolate the issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

echo "Test handler started...\n";

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Not a POST request']);
    exit;
}

echo "POST request received...\n";

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

echo "Input data: " . print_r($input, true) . "\n";

// Basic validation
$required_fields = ['name', 'email', 'phone', 'service', 'message'];
$errors = [];

foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        $errors[] = ucfirst($field) . ' is required';
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
    exit;
}

// Test database connection
try {
    require_once 'config/database.php';
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Check if table exists
    $result = $pdo->query("SHOW TABLES LIKE 'contact_submissions'");
    if ($result->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'Database table does not exist. Please run the SQL setup script.']);
        exit;
    }
    
    // Try to insert data
    $sql = "INSERT INTO contact_submissions (name, email, phone, service, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    
    $result = $stmt->execute([
        $input['name'],
        $input['email'],
        $input['phone'],
        $input['service'],
        $input['message']
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Test submission successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert data']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
