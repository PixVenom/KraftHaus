<?php
// Test script to diagnose contact form issues
// Access this file directly in your browser to check for problems

echo "<h2>KraftHaus Contact Form - Diagnostic Test</h2>";

// Test 1: Check if database config file exists
echo "<h3>1. Database Configuration Test</h3>";
if (file_exists('config/database.php')) {
    echo "✅ config/database.php exists<br>";
    
    // Test database connection
    try {
        require_once 'config/database.php';
        echo "✅ Database configuration loaded successfully<br>";
        
        // Test connection
        $pdo = new PDO($dsn, $username, $password, $options);
        echo "✅ Database connection successful<br>";
        
        // Test if tables exist
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "📋 Available tables: " . implode(', ', $tables) . "<br>";
        
        if (in_array('contact_submissions', $tables)) {
            echo "✅ contact_submissions table exists<br>";
            
            // Check table structure
            $columns = $pdo->query("DESCRIBE contact_submissions")->fetchAll(PDO::FETCH_COLUMN);
            echo "📋 Table columns: " . implode(', ', $columns) . "<br>";
        } else {
            echo "❌ contact_submissions table does NOT exist<br>";
            echo "🔧 <strong>Solution:</strong> Run the database-setup.sql script in phpMyAdmin<br>";
        }
        
    } catch (PDOException $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
        echo "🔧 <strong>Solution:</strong> Check your database credentials in config/database.php<br>";
    }
} else {
    echo "❌ config/database.php does NOT exist<br>";
    echo "🔧 <strong>Solution:</strong> Make sure the config folder and database.php file are uploaded<br>";
}

// Test 2: Check if form handler exists
echo "<h3>2. Form Handler Test</h3>";
if (file_exists('contact-form-handler.php')) {
    echo "✅ contact-form-handler.php exists<br>";
} else {
    echo "❌ contact-form-handler.php does NOT exist<br>";
    echo "🔧 <strong>Solution:</strong> Upload the contact-form-handler.php file<br>";
}

// Test 3: Check PHP version and extensions
echo "<h3>3. PHP Environment Test</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO Available: " . (extension_loaded('pdo') ? "✅ Yes" : "❌ No") . "<br>";
echo "PDO MySQL Available: " . (extension_loaded('pdo_mysql') ? "✅ Yes" : "❌ No") . "<br>";

// Test 4: Check file permissions
echo "<h3>4. File Permissions Test</h3>";
$files_to_check = [
    'config/database.php',
    'contact-form-handler.php',
    'contact.html'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $perms = fileperms($file);
        $readable = is_readable($file) ? "✅" : "❌";
        echo "$file: $readable (permissions: " . substr(sprintf('%o', $perms), -4) . ")<br>";
    }
}

// Test 5: Test form submission (simulate)
echo "<h3>5. Form Submission Test</h3>";
if (isset($_GET['test_form'])) {
    echo "Testing form submission...<br>";
    
    // Simulate form data
    $test_data = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+91-1234567890',
        'service' => 'Web Development',
        'message' => 'This is a test message to check if the form works correctly.'
    ];
    
    // Test JSON encoding
    $json_data = json_encode($test_data);
    echo "JSON data: " . htmlspecialchars($json_data) . "<br>";
    
    // Test if we can make a request to the handler
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/contact-form-handler.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ cURL Error: " . $error . "<br>";
    } else {
        echo "✅ HTTP Response Code: $http_code<br>";
        echo "Response: " . htmlspecialchars($response) . "<br>";
    }
} else {
    echo "<a href='?test_form=1'>🧪 Test Form Submission</a><br>";
}

// Test 6: Check JavaScript console errors
echo "<h3>6. Browser Console Check</h3>";
echo "🔍 <strong>Manual Check Required:</strong><br>";
echo "1. Open your contact page<br>";
echo "2. Press F12 to open Developer Tools<br>";
echo "3. Go to the Console tab<br>";
echo "4. Try submitting the form<br>";
echo "5. Look for any red error messages<br>";

echo "<hr>";
echo "<h3>🔧 Quick Fixes</h3>";
echo "<ol>";
echo "<li><strong>If database tables don't exist:</strong> Run database-setup.sql in phpMyAdmin</li>";
echo "<li><strong>If connection fails:</strong> Check database credentials in config/database.php</li>";
echo "<li><strong>If files don't exist:</strong> Upload all files to your Hostinger account</li>";
echo "<li><strong>If JavaScript errors:</strong> Check browser console for specific errors</li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
?>
