<?php
/**
 * Dynamic Sitemap Generator for FastCaisse
 * This script generates an XML sitemap automatically
 *
 * Usage: Run this script to update sitemap.xml
 * Can be set up as a cron job for automatic updates
 */

// Configuration
$domain = 'https://fastcaisse.be';
$lastmod = date('Y-m-d'); // Today's date

// Define all pages with their properties
$pages = [
    // Homepage variants
    [
        'loc' => '/',
        'priority' => '1.0',
        'changefreq' => 'weekly',
        'hreflang' => [
            'fr' => '/',
            'en' => '/index-en.html',
            'nl' => '/index-nl.html'
        ]
    ],
    [
        'loc' => '/index-en.html',
        'priority' => '1.0',
        'changefreq' => 'weekly',
        'hreflang' => [
            'fr' => '/',
            'en' => '/index-en.html',
            'nl' => '/index-nl.html'
        ]
    ],
    [
        'loc' => '/index-nl.html',
        'priority' => '1.0',
        'changefreq' => 'weekly',
        'hreflang' => [
            'fr' => '/',
            'en' => '/index-en.html',
            'nl' => '/index-nl.html'
        ]
    ],

    // Restaurant POS pages
    [
        'loc' => '/fastcaisse-restaurant-pos.html',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'hreflang' => [
            'fr' => '/fastcaisse-restaurant-pos.html',
            'en' => '/fastcaisse-restaurant-pos-en.html',
            'nl' => '/fastcaisse-restaurant-pos-nl.html'
        ]
    ],
    [
        'loc' => '/fastcaisse-restaurant-pos-en.html',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'hreflang' => [
            'fr' => '/fastcaisse-restaurant-pos.html',
            'en' => '/fastcaisse-restaurant-pos-en.html',
            'nl' => '/fastcaisse-restaurant-pos-nl.html'
        ]
    ],
    [
        'loc' => '/fastcaisse-restaurant-pos-nl.html',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'hreflang' => [
            'fr' => '/fastcaisse-restaurant-pos.html',
            'en' => '/fastcaisse-restaurant-pos-en.html',
            'nl' => '/fastcaisse-restaurant-pos-nl.html'
        ]
    ],

    // Retail POS pages
    [
        'loc' => '/fastcaisse-retail-pos.html',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'hreflang' => [
            'fr' => '/fastcaisse-retail-pos.html',
            'en' => '/fastcaisse-retail-pos-en.html',
            'nl' => '/fastcaisse-retail-pos-nl.html'
        ]
    ],
    [
        'loc' => '/fastcaisse-retail-pos-en.html',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'hreflang' => [
            'fr' => '/fastcaisse-retail-pos.html',
            'en' => '/fastcaisse-retail-pos-en.html',
            'nl' => '/fastcaisse-retail-pos-nl.html'
        ]
    ],
    [
        'loc' => '/fastcaisse-retail-pos-nl.html',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'hreflang' => [
            'fr' => '/fastcaisse-retail-pos.html',
            'en' => '/fastcaisse-retail-pos-en.html',
            'nl' => '/fastcaisse-retail-pos-nl.html'
        ]
    ],

    // CRM Business Management pages
    [
        'loc' => '/fastcaisse-crm-business-management.html',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'hreflang' => [
            'fr' => '/fastcaisse-crm-business-management.html',
            'en' => '/fastcaisse-crm-business-management-en.html',
            'nl' => '/fastcaisse-crm-business-management-nl.html'
        ]
    ],
    [
        'loc' => '/fastcaisse-crm-business-management-en.html',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'hreflang' => [
            'fr' => '/fastcaisse-crm-business-management.html',
            'en' => '/fastcaisse-crm-business-management-en.html',
            'nl' => '/fastcaisse-crm-business-management-nl.html'
        ]
    ],
    [
        'loc' => '/fastcaisse-crm-business-management-nl.html',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'hreflang' => [
            'fr' => '/fastcaisse-crm-business-management.html',
            'en' => '/fastcaisse-crm-business-management-en.html',
            'nl' => '/fastcaisse-crm-business-management-nl.html'
        ]
    ],

    // Legal pages
    [
        'loc' => '/general-conditions-fr.html',
        'priority' => '0.3',
        'changefreq' => 'yearly',
        'hreflang' => null
    ]
];

// Start XML generation
$xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL;

// Generate XML for each page
foreach ($pages as $page) {
    $xml .= '  <url>' . PHP_EOL;
    $xml .= '    <loc>' . $domain . $page['loc'] . '</loc>' . PHP_EOL;
    $xml .= '    <lastmod>' . $lastmod . '</lastmod>' . PHP_EOL;
    $xml .= '    <changefreq>' . $page['changefreq'] . '</changefreq>' . PHP_EOL;
    $xml .= '    <priority>' . $page['priority'] . '</priority>' . PHP_EOL;

    // Add hreflang tags if available
    if ($page['hreflang'] !== null) {
        foreach ($page['hreflang'] as $lang => $url) {
            $xml .= '    <xhtml:link rel="alternate" hreflang="' . $lang . '" href="' . $domain . $url . '"/>' . PHP_EOL;
        }
        // Add x-default for homepage
        if ($page['loc'] === '/') {
            $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . $domain . '/"/>' . PHP_EOL;
        }
    }

    $xml .= '  </url>' . PHP_EOL . PHP_EOL;
}

$xml .= '</urlset>';

// Save sitemap
$result = file_put_contents('sitemap.xml', $xml);

// Output result
if ($result !== false) {
    echo "✅ Sitemap generated successfully!" . PHP_EOL;
    echo "📄 File: sitemap.xml" . PHP_EOL;
    echo "📊 Total URLs: " . count($pages) . PHP_EOL;
    echo "📅 Last modified: " . $lastmod . PHP_EOL;
    echo "🔗 URL: " . $domain . "/sitemap.xml" . PHP_EOL;

    // Ping search engines (optional - uncomment to use)
    // pingSearchEngines($domain . '/sitemap.xml');
} else {
    echo "❌ Error: Failed to generate sitemap!" . PHP_EOL;
}

/**
 * Ping search engines about sitemap update
 * Uncomment to use this feature
 */
function pingSearchEngines($sitemapUrl) {
    $engines = [
        'Google' => 'https://www.google.com/ping?sitemap=' . urlencode($sitemapUrl),
        'Bing' => 'https://www.bing.com/ping?sitemap=' . urlencode($sitemapUrl)
    ];

    foreach ($engines as $name => $pingUrl) {
        $ch = curl_init($pingUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            echo "✅ Pinged $name successfully" . PHP_EOL;
        } else {
            echo "⚠️ Failed to ping $name (HTTP $httpCode)" . PHP_EOL;
        }
    }
}

// If running from browser, provide HTML output
if (php_sapi_name() !== 'cli' && !isset($_SERVER['HTTP_ACCEPT']) || strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Sitemap Generator - FastCaisse</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 800px;
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
            .actions {
                text-align: center;
                margin-top: 30px;
            }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                margin: 5px;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
            }
            .btn:hover {
                background: #0056b3;
            }
            pre {
                background: #f4f4f4;
                padding: 15px;
                border-radius: 5px;
                overflow-x: auto;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🗺️ Sitemap Generator</h1>
            <div class="success">
                ✅ Sitemap has been generated successfully!
            </div>
            <div class="info">
                <h3>Details:</h3>
                <pre>
📄 File: sitemap.xml
📊 Total URLs: <?php echo count($pages); ?>

📅 Last Modified: <?php echo $lastmod; ?>

🔗 Sitemap URL: <?php echo $domain; ?>/sitemap.xml
                </pre>
            </div>
            <div class="info">
                <h3>Next Steps:</h3>
                <ol>
                    <li>Submit sitemap to Google Search Console</li>
                    <li>Submit sitemap to Bing Webmaster Tools</li>
                    <li>Add sitemap URL to robots.txt (already done)</li>
                    <li>Set up automatic generation (cron job)</li>
                </ol>
            </div>
            <div class="actions">
                <a href="/sitemap.xml" class="btn" target="_blank">View Sitemap</a>
                <a href="https://search.google.com/search-console" class="btn" target="_blank">Google Search Console</a>
                <a href="https://www.bing.com/webmasters" class="btn" target="_blank">Bing Webmaster Tools</a>
                <a href="/" class="btn">Back to Home</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
