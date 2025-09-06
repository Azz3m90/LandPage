<?php
/**
 * Reset Rate Limit Script
 * This script clears the rate limit for testing purposes
 *
 * Usage: Access this file directly in the browser to clear rate limits
 */

header('Content-Type: application/json');

// Remove rate limit files
$rateDir = __DIR__ . '/';
$cleared = 0;

// Pattern to match rate limit files
$pattern = $rateDir . 'rate_limit_*.tmp';
$files = glob($pattern);

foreach ($files as $file) {
    if (unlink($file)) {
        $cleared++;
    }
}

// Response
$response = [
    'success' => true,
    'message' => "Rate limit cleared. Removed $cleared temporary file(s).",
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response, JSON_PRETTY_PRINT);

// Also provide a simple HTML interface if accessed directly
if (!isset($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Rate Limit Reset</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background-color: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #333;
                text-align: center;
            }
            .success {
                color: #28a745;
                text-align: center;
                font-size: 18px;
                margin: 20px 0;
            }
            .info {
                background: #e3f2fd;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .back-link {
                text-align: center;
                margin-top: 30px;
            }
            .back-link a {
                color: #007bff;
                text-decoration: none;
                font-size: 16px;
            }
            .back-link a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔄 Rate Limit Reset Tool</h1>
            <div class="success">
                ✅ <?php echo $response['message']; ?>
            </div>
            <div class="info">
                <strong>Note:</strong> This tool removes server-side rate limit files.
                For client-side rate limits stored in localStorage, use the verification dashboard.
            </div>
            <div class="back-link">
                <a href="verify-form.html">← Back to Verification Dashboard</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
