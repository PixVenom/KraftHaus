<?php
// GitHub Webhook for Auto-Deployment
// Place this file in your Hostinger public_html directory

$secret = 'your-webhook-secret-key'; // Change this to a secure random string
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

// Verify the webhook signature
$expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(401);
    die('Unauthorized');
}

// Parse the payload
$data = json_decode($payload, true);

// Check if it's a push to main branch
if ($data['ref'] === 'refs/heads/main') {
    // Pull the latest changes
    $output = shell_exec('cd /home/your-username/public_html && git pull origin main 2>&1');
    
    // Log the deployment
    file_put_contents('deployment.log', date('Y-m-d H:i:s') . " - Deployment successful\n" . $output . "\n", FILE_APPEND);
    
    echo "Deployment successful";
} else {
    echo "Not a push to main branch";
}
?>
