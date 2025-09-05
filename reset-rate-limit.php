<?php
/**
 * Rate Limit Reset Utility
 * Use this to clear rate limiting for testing purposes
 */

$rateFiles = glob('rate_limit_*.tmp');
$cleared = 0;

foreach ($rateFiles as $file) {
    if (unlink($file)) {
        $cleared++;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => "Rate limiting cleared. Removed $cleared rate limit files.",
    'files_cleared' => $cleared
]);
?>
