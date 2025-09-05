<?php
/**
 * Test PHPMailer Configuration
 * This script tests if PHPMailer is properly configured and can send emails
 */

// Include PHPMailer autoloader
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include configuration
if (file_exists('contact-form-config.php')) {
    require_once 'contact-form-config.php';
} else {
    // Fallback configuration
    define('SMTP_HOST', 'mail.infomaniak.com');
    define('SMTP_PORT', 587);
    define('SMTP_USERNAME', 'contact@fastcaisse.be');
    define('SMTP_PASSWORD', '2VeryFast4u&me');
    define('SMTP_ENCRYPTION', 'tls');
}

// Test email address (change this to your email for testing)
$testEmail = 'test@example.com';

echo "<!DOCTYPE html>
<html>
<head>
    <title>PHPMailer Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>PHPMailer Configuration Test</h1>";

echo "<h2>Configuration Details:</h2>";
echo "<pre>";
echo "SMTP Host: " . SMTP_HOST . "\n";
echo "SMTP Port: " . SMTP_PORT . "\n";
echo "SMTP Username: " . SMTP_USERNAME . "\n";
echo "SMTP Encryption: " . SMTP_ENCRYPTION . "\n";
echo "PHPMailer Version: " . PHPMailer::VERSION . "\n";
echo "</pre>";

if (isset($_GET['send'])) {
    echo "<h2>Sending Test Email...</h2>";

    try {
        $mail = new PHPMailer(true);

        // Enable verbose debug output
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function($str, $level) {
            echo "<pre class='info'>Debug: $str</pre>";
        };

        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = (SMTP_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom('noreply@fastcaisse.be', 'FastCaisse Test');
        $mail->addAddress($testEmail);
        $mail->addReplyTo('contact@fastcaisse.be', 'FastCaisse Contact');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'PHPMailer Test Email';
        $mail->Body    = '<h1>Test Email</h1><p>This is a test email sent using PHPMailer with SMTP.</p><p>If you receive this email, your configuration is working correctly!</p>';
        $mail->AltBody = 'This is a test email sent using PHPMailer with SMTP. If you receive this email, your configuration is working correctly!';

        $mail->send();
        echo "<p class='success'><strong>✓ Email sent successfully!</strong></p>";
        echo "<p>Check the inbox of: <strong>$testEmail</strong></p>";

    } catch (Exception $e) {
        echo "<p class='error'><strong>✗ Email could not be sent.</strong></p>";
        echo "<p class='error'>Error: {$mail->ErrorInfo}</p>";
        echo "<p class='error'>Exception: {$e->getMessage()}</p>";
    }
} else {
    echo "<h2>Ready to Send Test Email</h2>";
    echo "<p>Test email will be sent to: <strong>$testEmail</strong></p>";
    echo "<p><a href='?send=1' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Send Test Email</a></p>";
}

echo "<hr>";
echo "<h3>Troubleshooting Tips:</h3>";
echo "<ul>
    <li>Make sure your SMTP credentials are correct</li>
    <li>Check if your firewall allows outbound connections on port " . SMTP_PORT . "</li>
    <li>Verify that PHP's OpenSSL extension is enabled for TLS/SSL</li>
    <li>Some SMTP servers require app-specific passwords instead of regular passwords</li>
    <li>Check your SMTP server's documentation for specific requirements</li>
</ul>";

echo "</body></html>";
?>
