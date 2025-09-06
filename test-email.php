<?php
/**
 * Email Configuration Test Script
 *
 * This script tests the email sending functionality
 * to ensure SMTP is properly configured.
 */

// Include PHPMailer autoloader
require_once 'vendor/autoload.php';
require_once 'contact-form-config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h2>FastCaisse Email Configuration Test</h2>\n";
echo "<hr>\n";

// Display current configuration
echo "<h3>Current Configuration:</h3>\n";
echo "<pre>";
echo "SMTP Host: " . SMTP_HOST . "\n";
echo "SMTP Port: " . SMTP_PORT . "\n";
echo "SMTP Username: " . SMTP_USERNAME . "\n";
echo "SMTP Encryption: " . SMTP_ENCRYPTION . "\n";
echo "Admin Email: " . ADMIN_EMAIL . "\n";
echo "</pre>";
echo "<hr>\n";

// Test SMTP connection
echo "<h3>Testing SMTP Connection...</h3>\n";

try {
    $mail = new PHPMailer(true);

    // Enable verbose debug output
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Debugoutput = function($str, $level) {
        echo "Debug level $level: $str<br>\n";
    };

    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = (SMTP_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->Timeout    = 30;

    // Test connection
    echo "<p>Attempting to connect to SMTP server...</p>\n";

    if ($mail->smtpConnect()) {
        echo "<p style='color: green;'><strong>✅ SMTP Connection Successful!</strong></p>\n";
        $mail->smtpClose();

        // Now try to send a test email
        echo "<hr>\n";
        echo "<h3>Sending Test Email...</h3>\n";

        $testMail = new PHPMailer(true);
        $testMail->isSMTP();
        $testMail->Host       = SMTP_HOST;
        $testMail->SMTPAuth   = true;
        $testMail->Username   = SMTP_USERNAME;
        $testMail->Password   = SMTP_PASSWORD;
        $testMail->SMTPSecure = (SMTP_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $testMail->Port       = SMTP_PORT;

        // Recipients
        $testMail->setFrom(SMTP_USERNAME, 'FastCaisse Test');
        $testMail->addAddress(ADMIN_EMAIL, 'Admin');
        $testMail->addReplyTo(SMTP_USERNAME, 'FastCaisse');

        // Content
        $testMail->isHTML(true);
        $testMail->CharSet = 'UTF-8';
        $testMail->Subject = 'Test Email - FastCaisse Contact Form';
        $testMail->Body    = '
        <html>
        <head>
            <title>Test Email</title>
        </head>
        <body>
            <h2>Test Email from FastCaisse Contact Form</h2>
            <p>This is a test email to verify that the SMTP configuration is working correctly.</p>
            <p><strong>Configuration Details:</strong></p>
            <ul>
                <li>SMTP Host: ' . SMTP_HOST . '</li>
                <li>SMTP Port: ' . SMTP_PORT . '</li>
                <li>SMTP Username: ' . SMTP_USERNAME . '</li>
                <li>Encryption: ' . SMTP_ENCRYPTION . '</li>
                <li>Admin Email: ' . ADMIN_EMAIL . '</li>
            </ul>
            <p>If you receive this email, your contact form is properly configured!</p>
            <p>Timestamp: ' . date('Y-m-d H:i:s') . '</p>
        </body>
        </html>';

        $testMail->AltBody = 'This is a test email from FastCaisse. Your SMTP configuration is working!';

        if ($testMail->send()) {
            echo "<p style='color: green;'><strong>✅ Test email sent successfully to " . ADMIN_EMAIL . "!</strong></p>\n";
            echo "<p>Please check your inbox to confirm receipt.</p>\n";
        } else {
            echo "<p style='color: red;'><strong>❌ Failed to send test email</strong></p>\n";
        }

    } else {
        echo "<p style='color: red;'><strong>❌ SMTP Connection Failed!</strong></p>\n";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error: " . $e->getMessage() . "</strong></p>\n";
    echo "<p>Error details: " . $e->getTraceAsString() . "</p>\n";
}

echo "<hr>\n";
echo "<h3>Troubleshooting Tips:</h3>\n";
echo "<ul>";
echo "<li>Make sure SMTP credentials are correct</li>";
echo "<li>Verify that port " . SMTP_PORT . " is not blocked by firewall</li>";
echo "<li>Check if your hosting provider allows external SMTP connections</li>";
echo "<li>Try using port 587 with TLS or port 465 with SSL</li>";
echo "<li>Ensure PHP OpenSSL extension is enabled</li>";
echo "</ul>";
?>
