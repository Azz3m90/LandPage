# PowerShell script to add Google Tag Manager to all HTML pages
$htmlFiles = @(
    "fastcaisse-retail-pos.html",
    "fastcaisse-retail-pos-en.html",
    "fastcaisse-retail-pos-nl.html",
    "fastcaisse-crm-business-management.html",
    "fastcaisse-crm-business-management-en.html",
    "fastcaisse-crm-business-management-nl.html",
    "general-conditions-fr.html",
    "404.html"
)

$gtmHeadCode = @"
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-N2947X9D');</script>
    <!-- End Google Tag Manager -->

"@

$gtmBodyCode = @"
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N2947X9D"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

"@

$updated = 0
$alreadyHasGTM = 0

foreach ($file in $htmlFiles) {
    $filePath = "c:\xampp\htdocs\LandPage\$file"

    if (Test-Path $filePath) {
        $content = Get-Content $filePath -Raw

        # Check if GTM is already added
        if ($content -match "GTM-N2947X9D") {
            Write-Host "✓ $file already has GTM" -ForegroundColor Green
            $alreadyHasGTM++
            continue
        }

        # Add GTM head code right after <head> tag, but preserve existing GA4 code
        if ($content -match '(<head>[\s\S]*?)(<!-- Google tag \(gtag\.js\) -->)') {
            # GTM goes before GA4
            $content = $content -replace '(<head>[\s\S]*?)(<!-- Google tag \(gtag\.js\) -->)', "`$1$gtmHeadCode`$2"
        } else {
            # No GA4 found, add GTM right after <head>
            $content = $content -replace '(<head>)', "`$1`n$gtmHeadCode"
        }

        # Add GTM body code right after <body> tag
        $content = $content -replace '(<body[^>]*>)', "`$1`n$gtmBodyCode"

        # Save the updated content
        Set-Content -Path $filePath -Value $content -Encoding UTF8
        Write-Host "✓ Updated $file with GTM" -ForegroundColor Green
        $updated++
    } else {
        Write-Host "✗ File not found: $file" -ForegroundColor Red
    }
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "Google Tag Manager Implementation Complete!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Files updated: $updated" -ForegroundColor Yellow
Write-Host "Files already had GTM: $alreadyHasGTM" -ForegroundColor Yellow
Write-Host "GTM Container ID: GTM-N2947X9D" -ForegroundColor Yellow
Write-Host "`nNext steps:" -ForegroundColor Cyan
Write-Host "1. Go to https://tagmanager.google.com" -ForegroundColor White
Write-Host "2. Access your GTM container (GTM-N2947X9D)" -ForegroundColor White
Write-Host "3. Add your GA4 tag and other marketing tags through GTM" -ForegroundColor White
Write-Host "4. Publish your GTM container to make tags live" -ForegroundColor White
