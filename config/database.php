<?php
// Database configuration for KraftHaus Contact Form
// Update these values with your Hostinger database credentials

// Database configuration
$host = 'coral-manatee-691994.hostinger.com'; // Your Hostinger database host
$dbname = 'u425323865_contact_us'; // Replace with your actual database name
$username = 'u425323865_contacts'; // Replace with your database username
$password = 'Krafthaus@123'; // Replace with your database password

// PDO options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

// Create DSN
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

// Test database connection
try {
    $pdo = new PDO($dsn, $username, $password, $options);
    // Connection successful - you can remove this in production
    // error_log("Database connection successful");
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}

// Email configuration
define('SMTP_HOST', 'smtp.hostinger.com'); // Hostinger SMTP server
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@krafthaus.in'); // Your email
define('SMTP_PASSWORD', 'your_email_password'); // Your email password
define('FROM_EMAIL', 'noreply@krafthaus.in');
define('FROM_NAME', 'KraftHaus Contact Form');
/define('ADMIN_EMAIL', 'hello@krafthaus.in');  //Where to send notifications


// Security settings
define('MAX_SUBMISSIONS_PER_HOUR', 5); // Rate limiting
define('ENABLE_EMAIL_NOTIFICATIONS', true);
define('ENABLE_AUTO_REPLY', true);

// Timezone
date_default_timezone_set('Asia/Kolkata'); // Set to your timezone
?>
