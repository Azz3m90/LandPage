<?php
/**
 * Google Analytics Implementation Verification Script
 * This script verifies that GA4 tracking code is properly installed on all pages
 */

$ga_tracking_id = 'G-KLYR85GNLM';
$pages_to_check = [
    'index.html' => 'Homepage (French)',
    'index-en.html' => 'Homepage (English)',
    'index-nl.html' => 'Homepage (Dutch)',
    'fastcaisse-restaurant-pos.html' => 'Restaurant POS (French)',
    'fastcaisse-restaurant-pos-en.html' => 'Restaurant POS (English)',
    'fastcaisse-restaurant-pos-nl.html' => 'Restaurant POS (Dutch)',
    'fastcaisse-retail-pos.html' => 'Retail POS (French)',
    'fastcaisse-retail-pos-en.html' => 'Retail POS (English)',
    'fastcaisse-retail-pos-nl.html' => 'Retail POS (Dutch)',
    'fastcaisse-crm-business-management.html' => 'CRM Business Management (French)',
    'fastcaisse-crm-business-management-en.html' => 'CRM Business Management (English)',
    'fastcaisse-crm-business-management-nl.html' => 'CRM Business Management (Dutch)',
    'general-conditions-fr.html' => 'General Conditions (French)',
    '404.html' => '404 Error Page'
];

$results = [];
$all_passed = true;

foreach ($pages_to_check as $file => $description) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $has_gtag = strpos($content, 'gtag.js?id=' . $ga_tracking_id) !== false;
        $has_config = strpos($content, "gtag('config', '" . $ga_tracking_id . "')") !== false;
        $is_after_head = false;

        // Check if GA tag is right after <head> tag
        if (preg_match('/<head>\s*<!--\s*Google tag/', $content)) {
            $is_after_head = true;
        }

        $results[] = [
            'file' => $file,
            'description' => $description,
            'has_gtag' => $has_gtag,
            'has_config' => $has_config,
            'is_after_head' => $is_after_head,
            'passed' => $has_gtag && $has_config && $is_after_head
        ];

        if (!($has_gtag && $has_config && $is_after_head)) {
            $all_passed = false;
        }
    } else {
        $results[] = [
            'file' => $file,
            'description' => $description,
            'has_gtag' => false,
            'has_config' => false,
            'is_after_head' => false,
            'passed' => false,
            'error' => 'File not found'
        ];
        $all_passed = false;
    }
}

// Generate report
$total_pages = count($pages_to_check);
$passed_pages = count(array_filter($results, function($r) { return $r['passed']; }));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Analytics Verification - FastCaisse</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .summary {
            background: <?php echo $all_passed ? '#d4edda' : '#fff3cd'; ?>;
            border: 1px solid <?php echo $all_passed ? '#c3e6cb' : '#ffeeba'; ?>;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .summary h2 {
            color: <?php echo $all_passed ? '#155724' : '#856404'; ?>;
            margin-bottom: 10px;
            font-size: 20px;
        }
        .summary .stats {
            display: flex;
            gap: 30px;
            margin-top: 15px;
        }
        .stat {
            display: flex;
            flex-direction: column;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .status.pass {
            background: #d4edda;
            color: #155724;
        }
        .status.fail {
            background: #f8d7da;
            color: #721c24;
        }
        .status.warning {
            background: #fff3cd;
            color: #856404;
        }
        .check-icon {
            width: 16px;
            height: 16px;
        }
        .tracking-id {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 13px;
        }
        .next-steps {
            background: #e3f2fd;
            border: 1px solid #90caf9;
            border-radius: 5px;
            padding: 20px;
            margin-top: 30px;
        }
        .next-steps h3 {
            color: #1565c0;
            margin-bottom: 15px;
        }
        .next-steps ul {
            margin-left: 20px;
            color: #333;
        }
        .next-steps li {
            margin-bottom: 8px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge.success {
            background: #28a745;
            color: white;
        }
        .badge.error {
            background: #dc3545;
            color: white;
        }
        .badge.warning {
            background: #ffc107;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            📊 Google Analytics Implementation Verification
        </h1>
        <div class="subtitle">
            Tracking ID: <span class="tracking-id"><?php echo $ga_tracking_id; ?></span>
        </div>

        <div class="summary">
            <h2><?php echo $all_passed ? '✅ All Pages Have Google Analytics Installed!' : '⚠️ Some Issues Found'; ?></h2>
            <div class="stats">
                <div class="stat">
                    <div class="stat-value"><?php echo $passed_pages; ?>/<?php echo $total_pages; ?></div>
                    <div class="stat-label">Pages Verified</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?php echo round(($passed_pages / $total_pages) * 100); ?>%</div>
                    <div class="stat-label">Completion Rate</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?php echo date('Y-m-d H:i'); ?></div>
                    <div class="stat-label">Last Checked</div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Page</th>
                    <th>File</th>
                    <th>GA Script</th>
                    <th>GA Config</th>
                    <th>Position</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                <tr>
                    <td><?php echo htmlspecialchars($result['description']); ?></td>
                    <td><code><?php echo htmlspecialchars($result['file']); ?></code></td>
                    <td>
                        <?php if (isset($result['error'])): ?>
                            <span class="badge error">Error</span>
                        <?php else: ?>
                            <span class="badge <?php echo $result['has_gtag'] ? 'success' : 'error'; ?>">
                                <?php echo $result['has_gtag'] ? '✓ Found' : '✗ Missing'; ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (isset($result['error'])): ?>
                            <span class="badge error">Error</span>
                        <?php else: ?>
                            <span class="badge <?php echo $result['has_config'] ? 'success' : 'error'; ?>">
                                <?php echo $result['has_config'] ? '✓ Found' : '✗ Missing'; ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (isset($result['error'])): ?>
                            <span class="badge error">Error</span>
                        <?php else: ?>
                            <span class="badge <?php echo $result['is_after_head'] ? 'success' : 'warning'; ?>">
                                <?php echo $result['is_after_head'] ? '✓ Correct' : '⚠ Check'; ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (isset($result['error'])): ?>
                            <span class="status fail">
                                ✗ <?php echo htmlspecialchars($result['error']); ?>
                            </span>
                        <?php elseif ($result['passed']): ?>
                            <span class="status pass">
                                ✓ Verified
                            </span>
                        <?php else: ?>
                            <span class="status fail">
                                ✗ Issues Found
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="next-steps">
            <h3>📋 Next Steps</h3>
            <ul>
                <?php if ($all_passed): ?>
                    <li>✅ <strong>Google Analytics is properly installed on all pages!</strong></li>
                    <li>🔍 Go to <a href="https://analytics.google.com" target="_blank">Google Analytics</a> to verify real-time data</li>
                    <li>📊 Check the Real-Time reports to confirm tracking is working</li>
                    <li>🎯 Set up Goals and Conversions in GA4</li>
                    <li>📱 Test tracking on both desktop and mobile devices</li>
                    <li>🔗 Link Google Analytics with Google Search Console</li>
                <?php else: ?>
                    <li>⚠️ <strong>Fix the issues identified above</strong></li>
                    <li>🔧 Ensure GA tracking code is added immediately after the &lt;head&gt; tag</li>
                    <li>📝 Verify the tracking ID matches: <code><?php echo $ga_tracking_id; ?></code></li>
                    <li>🔄 Run this verification again after making changes</li>
                <?php endif; ?>
            </ul>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #666;">
            <p>
                <strong>Google Analytics Resources:</strong><br>
                <a href="https://analytics.google.com" target="_blank">Google Analytics Dashboard</a> |
                <a href="https://support.google.com/analytics" target="_blank">Help Center</a> |
                <a href="https://developers.google.com/analytics/devguides/collection/ga4" target="_blank">Developer Guide</a>
            </p>
        </div>
    </div>
</body>
</html>
