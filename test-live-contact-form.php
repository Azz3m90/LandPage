<?php
/**
 * Test Live Contact Form System
 * This simulates a real form submission to test the complete system
 */

// Simulate a form submission
$testData = json_encode([
    'firstName' => 'Test',
    'lastName' => 'User',
    'email' => 'test@example.com',
    'phone' => '+32 123 456 789',
    'subject' => 'System Integration Test',
    'message' => 'This is a test message to verify that the SMTP integration is working correctly in the live system.',
    'language' => 'en',
    'cf-turnstile-response' => 'test-token-bypass'  // We'll handle this in testing
]);

// Set up the request environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_SERVER['HTTP_REFERER'] = 'https://fastcaisse.be/index-en.html';

// Capture the output
ob_start();

// Include and execute the contact handler
include 'contact-handler.php';

$output = ob_get_clean();

echo "<h2>Live Contact Form Test Results</h2>";
echo "<hr>";
echo "<h3>System Response:</h3>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";
echo "<hr>";
echo "<h3>Configuration Check:</h3>";
echo "<pre>";
echo "SMTP Host: " . (defined('SMTP_HOST') ? SMTP_HOST : 'NOT DEFINED') . "\n";
echo "SMTP Port: " . (defined('SMTP_PORT') ? SMTP_PORT : 'NOT DEFINED') . "\n";
echo "SMTP Username: " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'NOT DEFINED') . "\n";
echo "Admin Email: " . (defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'NOT DEFINED') . "\n";
echo "USE_SMTP: " . (defined('USE_SMTP') ? (USE_SMTP ? 'TRUE' : 'FALSE') : 'NOT DEFINED') . "\n";
echo "</pre>";
?>
