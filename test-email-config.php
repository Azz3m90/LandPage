<?php
/**
 * Email Configuration Test Script
 *
 * This script tests the SMTP email configuration for FastCaisse contact form
 */

// Include the configuration
require_once 'contact-form-config.php';
require_once 'contact-handler.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>FastCaisse Email Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #007bff; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .config-item { margin: 10px 0; padding: 10px; background: #f8f9fa; border-left: 3px solid #007bff; }
        .config-label { font-weight: bold; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 10px 5px; }
        button:hover { background: #0056b3; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔧 FastCaisse Email Configuration Test</h1>";

echo "<h2>📋 Current Configuration</h2>";

// Display current configuration
echo "<div class='config-item'>
    <div class='config-label'>Admin Email:</div>
    " . ADMIN_EMAIL . "
</div>";

echo "<div class='config-item'>
    <div class='config-label'>SMTP Enabled:</div>
    " . (USE_SMTP ? '✅ Yes' : '❌ No') . "
</div>";

if (USE_SMTP) {
    echo "<div class='config-item'>
        <div class='config-label'>SMTP Host:</div>
        " . SMTP_HOST . "
    </div>";

    echo "<div class='config-item'>
        <div class='config-label'>SMTP Port:</div>
        " . SMTP_PORT . "
    </div>";

    echo "<div class='config-item'>
        <div class='config-label'>SMTP Username:</div>
        " . SMTP_USERNAME . "
    </div>";

    echo "<div class='config-item'>
        <div class='config-label'>SMTP Encryption:</div>
        " . SMTP_ENCRYPTION . "
    </div>";
}

// Test SMTP connection if requested
if (isset($_POST['test_smtp'])) {
    echo "<h2>🧪 SMTP Connection Test</h2>";

    if (!USE_SMTP) {
        echo "<div class='error'>❌ SMTP is disabled. Please enable SMTP in configuration to test.</div>";
    } else {
        echo "<div class='info'>Testing SMTP connection to " . SMTP_HOST . ":" . SMTP_PORT . "...</div>";

        // Test SMTP connection
        $smtp = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
        if ($smtp) {
            $response = fgets($smtp, 515);
            if (substr($response, 0, 3) === '220') {
                echo "<div class='success'>✅ SMTP server connection successful!</div>";
                echo "<div class='info'>Server response: " . htmlspecialchars(trim($response)) . "</div>";
                fclose($smtp);
            } else {
                echo "<div class='error'>❌ SMTP server responded with error: " . htmlspecialchars(trim($response)) . "</div>";
                fclose($smtp);
            }
        } else {
            echo "<div class='error'>❌ Could not connect to SMTP server: $errno - $errstr</div>";
        }
    }
}

// Send test email if requested
if (isset($_POST['send_test'])) {
    echo "<h2>📧 Test Email Send</h2>";

    $testData = [
        'firstName' => 'Test',
        'lastName' => 'User',
        'email' => ADMIN_EMAIL, // Send test to admin email
        'subject' => 'Email Configuration Test',
        'message' => 'This is a test message to verify that the FastCaisse contact form email configuration is working properly. Sent at ' . date('Y-m-d H:i:s') . '.'
    ];

    echo "<div class='info'>Sending test email to " . ADMIN_EMAIL . "...</div>";

    try {
        $adminResult = sendAdminNotification($testData);
        $clientResult = sendClientConfirmation($testData);

        if ($adminResult) {
            echo "<div class='success'>✅ Admin notification email sent successfully!</div>";
        } else {
            echo "<div class='error'>❌ Failed to send admin notification email.</div>";
        }

        if ($clientResult) {
            echo "<div class='success'>✅ Client confirmation email sent successfully!</div>";
        } else {
            echo "<div class='error'>❌ Failed to send client confirmation email.</div>";
        }

        if ($adminResult && $clientResult) {
            echo "<div class='success'>🎉 All tests passed! Email configuration is working correctly.</div>";
            echo "<div class='info'>Please check your email inbox at " . ADMIN_EMAIL . " for the test messages.</div>";
        }

    } catch (Exception $e) {
        echo "<div class='error'>❌ Exception occurred: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Display test buttons
echo "<h2>🚀 Run Tests</h2>";

if (USE_SMTP) {
    echo "<div class='info'>SMTP is enabled. You can test both SMTP connection and email sending.</div>";
} else {
    echo "<div class='warning'>⚠️ SMTP is disabled. Email will be sent using PHP's mail() function.</div>";
}

echo "<form method='post' onsubmit='return confirmTest(event)'>
    <button type='submit' name='test_smtp'>🔌 Test SMTP Connection</button>
    <button type='submit' name='send_test'>📧 Send Test Email</button>
    <button type='submit' name='validate_config'>🔍 Validate Configuration</button>
    <button type='submit' name='check_logs'>📋 Check Error Logs</button>
</form>

<script>
function confirmTest(event) {
    const button = event.submitter;
    if (button.name === 'send_test') {
        return confirm('This will send test emails to " . ADMIN_EMAIL . ". Continue?');
    }
    return true;
}
</script>";

// Display usage instructions
echo "<h2>📖 Usage Instructions</h2>
<div class='info'>
    <p><strong>1. Test SMTP Connection:</strong> This will test if your server can connect to the SMTP server.</p>
    <p><strong>2. Send Test Email:</strong> This will send actual test emails to verify the complete email flow.</p>
    <p><strong>3. Check Email:</strong> Look for test emails in " . ADMIN_EMAIL . " inbox (including spam folder).</p>
</div>";

// Handle additional test requests
if (isset($_POST['validate_config'])) {
    echo "<h2>🔍 Configuration Validation Details</h2>";

    // Check file permissions
    $configFile = 'contact-form-config.php';
    if (file_exists($configFile)) {
        $perms = fileperms($configFile);
        echo "<div class='info'>📁 File Permissions: " . sprintf('%o', $perms & 0777) . "</div>";
        if (($perms & 0777) > 0644) {
            echo "<div class='warning'>⚠️ Configuration file permissions may be too permissive</div>";
        }
    }

    // Check PHP configuration
    echo "<div class='info'>🔧 PHP Configuration:</div>";
    echo "<div class='config-item'>PHP Version: " . phpversion() . "</div>";
    echo "<div class='config-item'>OpenSSL Extension: " . (extension_loaded('openssl') ? '✅ Loaded' : '❌ Not loaded') . "</div>";
    echo "<div class='config-item'>Mail Function: " . (function_exists('mail') ? '✅ Available' : '❌ Not available') . "</div>";
    echo "<div class='config-item'>fsockopen Function: " . (function_exists('fsockopen') ? '✅ Available' : '❌ Not available') . "</div>";

    // Check server environment
    echo "<div class='info'>🌐 Server Environment:</div>";
    echo "<div class='config-item'>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</div>";
    echo "<div class='config-item'>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</div>";
    echo "<div class='config-item'>Script Path: " . __FILE__ . "</div>";
}

if (isset($_POST['check_logs'])) {
    echo "<h2>📋 Error Log Check</h2>";

    $logFiles = [
        ini_get('error_log'),
        '/var/log/apache2/error.log',
        '/var/log/nginx/error.log',
        'error.log',
        'php_error.log'
    ];

    $found = false;
    foreach ($logFiles as $logFile) {
        if ($logFile && file_exists($logFile) && is_readable($logFile)) {
            $found = true;
            echo "<div class='info'>📁 Reading log file: " . htmlspecialchars($logFile) . "</div>";

            $lines = file($logFile);
            $recentLines = array_slice($lines, -50); // Last 50 lines
            $smtpErrors = array_filter($recentLines, function($line) {
                return stripos($line, 'smtp') !== false ||
                       stripos($line, 'mail') !== false ||
                       stripos($line, 'email') !== false;
            });

            if (!empty($smtpErrors)) {
                echo "<div class='info'>🔍 Recent SMTP/Email related log entries:</div>";
                foreach (array_slice($smtpErrors, -10) as $line) { // Last 10 email-related lines
                    echo "<div class='config-item'>" . htmlspecialchars(trim($line)) . "</div>";
                }
            } else {
                echo "<div class='success'>✅ No recent SMTP/Email errors found in this log</div>";
            }
            break;
        }
    }

    if (!$found) {
        echo "<div class='warning'>⚠️ Could not locate or read error log files</div>";
        echo "<div class='info'>💡 Common log locations:</div>";
        foreach ($logFiles as $logFile) {
            if ($logFile) {
                echo "<div class='config-item'>" . htmlspecialchars($logFile) . "</div>";
            }
        }
    }
}

echo "<div class='warning'>
    <p><strong>⚠️ Security Warning:</strong></p>
    <ul>
        <li>This test file displays sensitive SMTP configuration details</li>
        <li>Delete this file after testing: <code>test-email-config.php</code></li>
        <li>Restrict access to this file if keeping it for future use</li>
        <li>Never leave this file accessible in production</li>
    </ul>
</div>";

echo "<div class='info'>
    <p><strong>🛡️ Security Recommendations:</strong></p>
    <ul>
        <li>Use environment variables for sensitive credentials</li>
        <li>Implement rate limiting for contact forms</li>
        <li>Add CSRF protection tokens</li>
        <li>Regular security updates for server and PHP</li>
        <li>Monitor email bounce rates and delivery issues</li>
    </ul>
</div>";

echo "</div>
</body>
</html>";
?>
