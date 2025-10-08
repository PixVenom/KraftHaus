<?php
// Simple test to check basic functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Simple Contact Form Test</h2>";

// Test 1: Check if we can include the database config
echo "<h3>1. Database Config Test</h3>";
try {
    require_once 'config/database.php';
    echo "✅ Database config loaded<br>";
    
    // Test connection
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "✅ Database connected<br>";
    
    // Check if table exists
    $result = $pdo->query("SHOW TABLES LIKE 'contact_submissions'");
    if ($result->rowCount() > 0) {
        echo "✅ contact_submissions table exists<br>";
    } else {
        echo "❌ contact_submissions table does NOT exist<br>";
        echo "<strong>SOLUTION:</strong> You need to run the database-setup.sql script in phpMyAdmin<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 2: Check if form handler exists and is accessible
echo "<h3>2. Form Handler Test</h3>";
if (file_exists('contact-form-handler.php')) {
    echo "✅ contact-form-handler.php exists<br>";
} else {
    echo "❌ contact-form-handler.php missing<br>";
}

// Test 3: Test a simple form submission
echo "<h3>3. Test Form Submission</h3>";
if ($_POST) {
    echo "<strong>POST data received:</strong><br>";
    print_r($_POST);
    echo "<br><br>";
    
    // Try to process the form
    try {
        require_once 'contact-form-handler.php';
    } catch (Exception $e) {
        echo "❌ Form handler error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "<form method='POST'>";
    echo "<input type='text' name='name' value='Test User' required><br><br>";
    echo "<input type='email' name='email' value='test@example.com' required><br><br>";
    echo "<input type='tel' name='phone' value='+91-1234567890' required><br><br>";
    echo "<select name='service' required>";
    echo "<option value='Web Development'>Web Development</option>";
    echo "</select><br><br>";
    echo "<textarea name='message' required>Test message</textarea><br><br>";
    echo "<button type='submit'>Test Submit</button>";
    echo "</form>";
}

echo "<hr>";
echo "<p><strong>Current directory:</strong> " . getcwd() . "</p>";
echo "<p><strong>Files in directory:</strong></p>";
echo "<ul>";
foreach (scandir('.') as $file) {
    if ($file != '.' && $file != '..') {
        echo "<li>$file</li>";
    }
}
echo "</ul>";
?>
