<?php
// Contact Form Handler for KraftHaus Website
// This script handles contact form submissions and stores them in the database

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Include database configuration
require_once 'config/database.php';

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate phone number (basic validation)
function validatePhone($phone) {
    // Remove all non-digit characters
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    // Check if phone has at least 10 digits
    return strlen($phone) >= 10;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If JSON input is not available, try POST data
    if (!$input) {
        $input = $_POST;
    }
    
    // Validate required fields
    $required_fields = ['name', 'email', 'phone', 'service', 'message'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            $errors[] = ucfirst($field) . ' is required';
        }
    }
    
    // Validate email format
    if (!empty($input['email']) && !validateEmail($input['email'])) {
        $errors[] = 'Please enter a valid email address';
    }
    
    // Validate phone number
    if (!empty($input['phone']) && !validatePhone($input['phone'])) {
        $errors[] = 'Please enter a valid phone number';
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ]);
        exit;
    }
    
    // Sanitize input data
    $name = sanitizeInput($input['name']);
    $email = sanitizeInput($input['email']);
    $phone = sanitizeInput($input['phone']);
    $service = sanitizeInput($input['service']);
    $message = sanitizeInput($input['message']);
    
    // Get client IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    // Get user agent
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    // Create database connection
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Prepare SQL statement
    $sql = "INSERT INTO contact_submissions (name, email, phone, service, message, ip_address, user_agent, created_at) 
            VALUES (:name, :email, :phone, :service, :message, :ip_address, :user_agent, NOW())";
    
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':service', $service);
    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':ip_address', $ip_address);
    $stmt->bindParam(':user_agent', $user_agent);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Get the last inserted ID
        $submission_id = $pdo->lastInsertId();
        
        // Send email notification (optional)
        $email_sent = sendEmailNotification($name, $email, $phone, $service, $message, $submission_id);
        
        // Log successful submission
        error_log("Contact form submission successful. ID: $submission_id, Email: $email");
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your message! We will get back to you soon.',
            'submission_id' => $submission_id,
            'email_sent' => $email_sent
        ]);
    } else {
        throw new Exception('Failed to insert data into database');
    }
    
} catch (PDOException $e) {
    // Database error
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again later.'
    ]);
} catch (Exception $e) {
    // General error
    error_log("General error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later.'
    ]);
}

// Function to send email notification
function sendEmailNotification($name, $email, $phone, $service, $message, $submission_id) {
    try {
        // Email configuration
        $to = 'hello@krafthaus.in'; // Change this to your email
        $subject = 'New Contact Form Submission - KraftHaus';
        
        $email_message = "
        <html>
        <head>
            <title>New Contact Form Submission</title>
        </head>
        <body>
            <h2>New Contact Form Submission</h2>
            <p><strong>Submission ID:</strong> $submission_id</p>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Service Interest:</strong> $service</p>
            <p><strong>Message:</strong></p>
            <p>$message</p>
            <hr>
            <p><em>This message was sent from the KraftHaus contact form.</em></p>
        </body>
        </html>
        ";
        
        // Email headers
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: KraftHaus Contact Form <noreply@krafthaus.in>',
            'Reply-To: ' . $email,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Send email
        return mail($to, $subject, $email_message, implode("\r\n", $headers));
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}
?>
