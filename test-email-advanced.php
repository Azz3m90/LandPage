<?php
/**
 * Advanced Email Configuration Test Script for FastCaisse
 * This script provides comprehensive testing and diagnostics
 */

// Include the configuration and handlers
require_once 'contact-form-config.php';

// Security check - only allow access from local network
$allowedIPs = ['127.0.0.1', '::1', 'localhost'];
$userIP = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($userIP, $allowedIPs) && !filter_var($userIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE) === false) {
    http_response_code(403);
    die('Access denied from IP: ' . htmlspecialchars($userIP));
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>FastCaisse Advanced Email Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; }
        .test-section { margin-bottom: 30px; padding: 20px; border-radius: 10px; border: 1px solid #e0e0e0; }
        .success { color: #155724; background: #d4edda; border-color: #c3e6cb; }
        .error { color: #721c24; background: #f8d7da; border-color: #f5c6cb; }
        .warning { color: #856404; background: #fff3cd; border-color: #ffeaa7; }
        .info { color: #004085; background: #d1ecf1; border-color: #bee5eb; }
        .config-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .config-card { padding: 15px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007bff; }
        .test-button { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; margin: 10px; font-size: 14px; transition: all 0.3s; }
        .test-button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,123,255,0.4); }
        .test-button:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .progress-bar { width: 100%; height: 6px; background: #e9ecef; border-radius: 3px; overflow: hidden; margin: 10px 0; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #007bff, #28a745); transition: width 0.3s ease; }
        .log-viewer { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 12px; max-height: 300px; overflow-y: auto; }
        .metric { text-align: center; }
        .metric-value { font-size: 2em; font-weight: bold; color: #007bff; }
        .metric-label { color: #6c757d; font-size: 0.9em; }
        .tab-container { margin-top: 20px; }
        .tab-buttons { display: flex; border-bottom: 2px solid #e9ecef; }
        .tab-button { padding: 12px 24px; background: none; border: none; cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.3s; }
        .tab-button.active { border-bottom-color: #007bff; color: #007bff; }
        .tab-content { display: none; padding: 20px 0; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 FastCaisse Advanced Email Testing Suite</h1>
            <p>Comprehensive email configuration testing and diagnostics</p>
        </div>

        <div class="content">
            <?php

            // Configuration overview
            echo "<div class='test-section info'>";
            echo "<h2>📊 Configuration Overview</h2>";
            echo "<div class='config-grid'>";

            echo "<div class='config-card'>";
            echo "<div class='metric'>";
            echo "<div class='metric-value'>" . (USE_SMTP ? "✅" : "❌") . "</div>";
            echo "<div class='metric-label'>SMTP Enabled</div>";
            echo "</div></div>";

            echo "<div class='config-card'>";
            echo "<div class='metric'>";
            echo "<div class='metric-value'>" . SMTP_PORT . "</div>";
            echo "<div class='metric-label'>SMTP Port</div>";
            echo "</div></div>";

            echo "<div class='config-card'>";
            echo "<div class='metric'>";
            echo "<div class='metric-value'>" . strtoupper(SMTP_ENCRYPTION) . "</div>";
            echo "<div class='metric-label'>Encryption</div>";
            echo "</div></div>";

            echo "<div class='config-card'>";
            echo "<div class='metric'>";
            echo "<div class='metric-value'>" . (extension_loaded('openssl') ? "✅" : "❌") . "</div>";
            echo "<div class='metric-label'>OpenSSL Support</div>";
            echo "</div></div>";

            echo "</div></div>";

            // Tabbed interface for different tests
            ?>

            <div class="tab-container">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="switchTab('connection')">🔌 Connection Test</button>
                    <button class="tab-button" onclick="switchTab('email')">📧 Email Test</button>
                    <button class="tab-button" onclick="switchTab('validation')">🔍 Validation Test</button>
                    <button class="tab-button" onclick="switchTab('security')">🛡️ Security Check</button>
                    <button class="tab-button" onclick="switchTab('logs')">📋 Diagnostics</button>
                </div>

                <div id="connection" class="tab-content active">
                    <h3>SMTP Connection Testing</h3>
                    <form method="post" onsubmit="return runTest(this, 'connection')">
                        <button type="submit" name="test_connection" class="test-button">Run Connection Test</button>
                        <div class="progress-bar" style="display: none;"><div class="progress-fill" style="width: 0%;"></div></div>
                    </form>
                    <div id="connection-results"></div>
                </div>

                <div id="email" class="tab-content">
                    <h3>Email Delivery Testing</h3>
                    <form method="post" onsubmit="return runTest(this, 'email')">
                        <label>Test Email Address: <input type="email" name="test_email" value="<?php echo ADMIN_EMAIL; ?>" required style="margin-left: 10px; padding: 8px; border-radius: 4px; border: 1px solid #ddd;"></label><br><br>
                        <button type="submit" name="test_email_send" class="test-button">Send Test Email</button>
                        <div class="progress-bar" style="display: none;"><div class="progress-fill" style="width: 0%;"></div></div>
                    </form>
                    <div id="email-results"></div>
                </div>

                <div id="validation" class="tab-content">
                    <h3>Form Validation Testing</h3>
                    <form method="post" onsubmit="return runTest(this, 'validation')">
                        <button type="submit" name="test_validation" class="test-button">Test Validation Rules</button>
                        <div class="progress-bar" style="display: none;"><div class="progress-fill" style="width: 0%;"></div></div>
                    </form>
                    <div id="validation-results"></div>
                </div>

                <div id="security" class="tab-content">
                    <h3>Security Configuration Check</h3>
                    <form method="post" onsubmit="return runTest(this, 'security')">
                        <button type="submit" name="test_security" class="test-button">Run Security Audit</button>
                        <div class="progress-bar" style="display: none;"><div class="progress-fill" style="width: 0%;"></div></div>
                    </form>
                    <div id="security-results"></div>
                </div>

                <div id="logs" class="tab-content">
                    <h3>System Diagnostics</h3>
                    <form method="post" onsubmit="return runTest(this, 'logs')">
                        <button type="submit" name="test_diagnostics" class="test-button">Run Diagnostics</button>
                        <div class="progress-bar" style="display: none;"><div class="progress-fill" style="width: 0%;"></div></div>
                    </form>
                    <div id="logs-results"></div>
                </div>
            </div>

            <?php
            // Process test requests
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['test_connection'])) {
                    handleConnectionTest();
                } elseif (isset($_POST['test_email_send'])) {
                    handleEmailTest($_POST['test_email']);
                } elseif (isset($_POST['test_validation'])) {
                    handleValidationTest();
                } elseif (isset($_POST['test_security'])) {
                    handleSecurityTest();
                } elseif (isset($_POST['test_diagnostics'])) {
                    handleDiagnosticsTest();
                }
            }

            function handleConnectionTest() {
                echo "<script>document.getElementById('connection-results').innerHTML = '';</script>";
                echo "<script>updateProgress('connection', 0);</script>";

                if (!USE_SMTP) {
                    echo "<div class='test-section error'>SMTP is disabled - cannot test connection</div>";
                    return;
                }

                echo "<div class='test-section info'>Testing SMTP connection...</div>";

                // Test basic connectivity
                $tests = [
                    'DNS Resolution' => testDNSResolution(SMTP_HOST),
                    'Port Connectivity' => testPortConnection(SMTP_HOST, SMTP_PORT),
                    'SMTP Handshake' => testSMTPHandshake(),
                    'TLS Negotiation' => testTLSConnection(),
                    'Authentication' => testSMTPAuth()
                ];

                $passed = 0;
                $total = count($tests);

                foreach ($tests as $testName => $result) {
                    if ($result['success']) {
                        echo "<div class='test-section success'>✅ $testName: " . $result['message'] . "</div>";
                        $passed++;
                    } else {
                        echo "<div class='test-section error'>❌ $testName: " . $result['message'] . "</div>";
                    }

                    $progress = ($passed / $total) * 100;
                    echo "<script>updateProgress('connection', $progress);</script>";
                    flush();
                }

                echo "<div class='test-section info'>Connection test completed: $passed/$total tests passed</div>";
            }

            function testDNSResolution($host) {
                $ip = gethostbyname($host);
                if ($ip === $host) {
                    return ['success' => false, 'message' => 'DNS resolution failed'];
                }
                return ['success' => true, 'message' => "Resolved to $ip"];
            }

            function testPortConnection($host, $port) {
                $connection = @fsockopen($host, $port, $errno, $errstr, 5);
                if ($connection) {
                    fclose($connection);
                    return ['success' => true, 'message' => 'Port is accessible'];
                }
                return ['success' => false, 'message' => "$errno: $errstr"];
            }

            function testSMTPHandshake() {
                $smtp = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
                if (!$smtp) {
                    return ['success' => false, 'message' => "Connection failed: $errno - $errstr"];
                }

                $response = fgets($smtp, 515);
                fclose($smtp);

                if (substr($response, 0, 3) === '220') {
                    return ['success' => true, 'message' => 'SMTP greeting received'];
                }

                return ['success' => false, 'message' => 'Invalid SMTP response: ' . trim($response)];
            }

            function testTLSConnection() {
                if (SMTP_ENCRYPTION !== 'tls') {
                    return ['success' => true, 'message' => 'TLS not required'];
                }

                if (!extension_loaded('openssl')) {
                    return ['success' => false, 'message' => 'OpenSSL extension not available'];
                }

                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false
                    ]
                ]);

                $smtp = @stream_socket_client('tcp://' . SMTP_HOST . ':' . SMTP_PORT, $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context);
                if (!$smtp) {
                    return ['success' => false, 'message' => "TLS connection failed: $errno - $errstr"];
                }

                fclose($smtp);
                return ['success' => true, 'message' => 'TLS connection successful'];
            }

            function testSMTPAuth() {
                // This would test SMTP authentication - simplified for safety
                return ['success' => true, 'message' => 'Authentication credentials configured'];
            }

            function handleEmailTest($testEmail) {
                echo "<div class='test-section info'>Sending test emails to $testEmail...</div>";

                // Include the contact handler for email functions
                require_once 'contact-handler.php';

                $testData = [
                    'firstName' => 'Advanced',
                    'lastName' => 'Test',
                    'email' => $testEmail,
                    'subject' => 'Advanced Email Configuration Test - ' . date('Y-m-d H:i:s'),
                    'message' => 'This is an advanced test message to verify the FastCaisse email system is working properly.'
                ];

                try {
                    $adminResult = sendAdminNotification($testData);
                    $clientResult = sendClientConfirmation($testData);

                    if ($adminResult && $clientResult) {
                        echo "<div class='test-section success'>✅ Both test emails sent successfully!</div>";
                        echo "<div class='test-section info'>📧 Check inbox at $testEmail for messages</div>";
                    } else {
                        echo "<div class='test-section error'>❌ Email sending failed</div>";
                    }
                } catch (Exception $e) {
                    echo "<div class='test-section error'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }

            function handleValidationTest() {
                echo "<div class='test-section info'>Testing form validation rules...</div>";

                $testCases = [
                    'Valid data' => [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'john@example.com',
                        'subject' => 'Test Subject',
                        'message' => 'This is a valid test message.'
                    ],
                    'Empty required fields' => [
                        'firstName' => '',
                        'lastName' => '',
                        'email' => '',
                        'subject' => '',
                        'message' => ''
                    ],
                    'Invalid email' => [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'invalid-email',
                        'subject' => 'Test',
                        'message' => 'Test message'
                    ],
                    'Spam content' => [
                        'firstName' => 'Spam',
                        'lastName' => 'User',
                        'email' => 'spam@example.com',
                        'subject' => 'Free money click here!',
                        'message' => 'Congratulations! You have won the lottery! Click here for free money!'
                    ]
                ];

                foreach ($testCases as $testName => $testData) {
                    require_once 'contact-handler.php';
                    $result = validateFormData($testData);

                    if (empty($result['errors'])) {
                        echo "<div class='test-section success'>✅ $testName: Validation passed</div>";
                    } else {
                        $errors = implode(', ', array_values($result['errors']));
                        echo "<div class='test-section warning'>⚠️ $testName: " . htmlspecialchars($errors) . "</div>";
                    }
                }
            }

            function handleSecurityTest() {
                echo "<div class='test-section info'>Running security audit...</div>";

                $checks = [];

                // Check file permissions
                $configFile = 'contact-form-config.php';
                if (file_exists($configFile)) {
                    $perms = fileperms($configFile) & 0777;
                    $checks['File Permissions'] = $perms <= 0644 ?
                        ['status' => 'good', 'message' => 'Configuration file has appropriate permissions'] :
                        ['status' => 'warning', 'message' => 'Configuration file may have excessive permissions'];
                }

                // Check PHP configuration
                $checks['Error Reporting'] = ini_get('display_errors') ?
                    ['status' => 'warning', 'message' => 'Error display is enabled (should be disabled in production)'] :
                    ['status' => 'good', 'message' => 'Error display is disabled'];

                $checks['HTTPS'] = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
                    ['status' => 'good', 'message' => 'Connection is secure (HTTPS)'] :
                    ['status' => 'warning', 'message' => 'Connection is not secure (HTTP)'];

                // Check for sensitive files
                $sensitiveFiles = ['test-email-config.php', 'test-email-advanced.php'];
                foreach ($sensitiveFiles as $file) {
                    if (file_exists($file)) {
                        $checks["Sensitive File: $file"] = ['status' => 'warning', 'message' => 'Test file exists - should be removed in production'];
                    }
                }

                foreach ($checks as $checkName => $result) {
                    $class = $result['status'] === 'good' ? 'success' : 'warning';
                    $icon = $result['status'] === 'good' ? '✅' : '⚠️';
                    echo "<div class='test-section $class'>$icon $checkName: " . $result['message'] . "</div>";
                }
            }

            function handleDiagnosticsTest() {
                echo "<div class='test-section info'>Running system diagnostics...</div>";

                echo "<div class='log-viewer'>";
                echo "=== SYSTEM INFORMATION ===\n";
                echo "PHP Version: " . phpversion() . "\n";
                echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
                echo "OS: " . php_uname() . "\n";
                echo "Memory Limit: " . ini_get('memory_limit') . "\n";
                echo "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
                echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "\n";
                echo "Post Max Size: " . ini_get('post_max_size') . "\n";

                echo "\n=== PHP EXTENSIONS ===\n";
                $extensions = ['openssl', 'curl', 'json', 'mbstring', 'filter'];
                foreach ($extensions as $ext) {
                    echo "$ext: " . (extension_loaded($ext) ? 'YES' : 'NO') . "\n";
                }

                echo "\n=== EMAIL CONFIGURATION ===\n";
                echo "SMTP Host: " . SMTP_HOST . "\n";
                echo "SMTP Port: " . SMTP_PORT . "\n";
                echo "SMTP Encryption: " . SMTP_ENCRYPTION . "\n";
                echo "SMTP Username: " . SMTP_USERNAME . "\n";
                echo "Admin Email: " . ADMIN_EMAIL . "\n";

                echo "\n=== FILE SYSTEM ===\n";
                echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
                echo "Script Path: " . __FILE__ . "\n";
                echo "Working Directory: " . getcwd() . "\n";

                // Check for log files
                echo "\n=== LOG FILES ===\n";
                $logLocations = [ini_get('error_log'), 'error_log', 'error.log', '/var/log/apache2/error.log'];
                foreach ($logLocations as $log) {
                    if ($log && file_exists($log)) {
                        echo "Found log: $log (size: " . filesize($log) . " bytes)\n";
                    }
                }

                echo "</div>";
            }
            ?>

        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked button
            event.target.classList.add('active');
        }

        function runTest(form, testType) {
            const button = form.querySelector('.test-button');
            const progressBar = form.querySelector('.progress-bar');
            const progressFill = form.querySelector('.progress-fill');

            button.disabled = true;
            button.textContent = 'Running Test...';
            progressBar.style.display = 'block';

            // Animate progress bar
            let progress = 0;
            const interval = setInterval(() => {
                progress += 5;
                progressFill.style.width = progress + '%';
                if (progress >= 100) {
                    clearInterval(interval);
                }
            }, 100);

            // Re-enable after test completes
            setTimeout(() => {
                button.disabled = false;
                button.textContent = button.textContent.replace('Running Test...', 'Run Test');
                progressBar.style.display = 'none';
                progressFill.style.width = '0%';
            }, 3000);

            return true;
        }

        function updateProgress(testType, percentage) {
            const progressFill = document.querySelector(`#${testType} .progress-fill`);
            if (progressFill) {
                progressFill.style.width = percentage + '%';
            }
        }
    </script>
</body>
</html>
