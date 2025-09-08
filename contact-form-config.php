<?php
/**
 * FastCaisse Contact Form Configuration
 *
 * This file loads configuration from environment variables for security
 */

// Load environment variables from .env file if it exists
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Remove quotes if present
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }

        // Set as environment variable if not already set
        if (!getenv($name)) {
            putenv("$name=$value");
        }
    }
}

// Email Configuration
define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'contact@fastcaisse.be');
define('COMPANY_NAME', getenv('COMPANY_NAME') ?: 'FastCaisse');
define('COMPANY_ADDRESS', getenv('COMPANY_ADDRESS') ?: 'Chaussée de Haecht 1749, 1130 Brussels, Belgium');

// reCAPTCHA Configuration (Optional)
define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY') ?: '');
define('RECAPTCHA_SITE_KEY', getenv('RECAPTCHA_SITE_KEY') ?: '');

// SMTP Configuration (Optional - for better email delivery)
define('USE_SMTP', filter_var(getenv('USE_SMTP') ?: 'true', FILTER_VALIDATE_BOOLEAN));
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'mail.infomaniak.com');
define('SMTP_PORT', intval(getenv('SMTP_PORT') ?: 587));
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: 'contact@fastcaisse.be');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: ''); // NEVER hardcode passwords!
define('SMTP_ENCRYPTION', getenv('SMTP_ENCRYPTION') ?: 'tls'); // tls or ssl

// File Upload Configuration (if you want to add file uploads)
define('ALLOW_FILE_UPLOADS', false);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB in bytes
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']);

// Rate Limiting Configuration
define('ENABLE_RATE_LIMITING', true);
define('MAX_SUBMISSIONS_PER_HOUR', 5); // Maximum submissions per IP per hour
define('MAX_SUBMISSIONS_PER_DAY', 20); // Maximum submissions per IP per day

// Logging Configuration
define('ENABLE_LOGGING', true);
define('LOG_FILE', 'contact-submissions.log');
define('LOG_ERRORS', true);
define('ERROR_LOG_FILE', 'contact-errors.log');

// Security Configuration
define('ENABLE_HONEYPOT', true); // Add hidden honeypot field to catch bots
define('REQUIRE_REFERER', true); // Require HTTP_REFERER header
define('ALLOWED_DOMAINS', ['fastcaisse.be', 'www.fastcaisse.be', 'fastcaisse.com', 'www.fastcaisse.com','localhost']); // Add your domains

// Response Configuration
define('REDIRECT_AFTER_SUCCESS', false); // Set to URL to redirect after success
define('SUCCESS_PAGE_URL', '/thank-you.html');

// Database Configuration (Optional - for storing submissions)
define('USE_DATABASE', false);
define('DB_HOST', 'localhost');
define('DB_NAME', 'fastcaisse_contacts');
define('DB_USERNAME', 'your_db_user');
define('DB_PASSWORD', 'your_db_password');

// Notification Settings
define('SEND_ADMIN_NOTIFICATION', true);
define('SEND_CLIENT_CONFIRMATION', true);
define('CC_ADMIN_EMAIL', ''); // Additional CC email for admin notifications
define('BCC_ADMIN_EMAIL', ''); // Additional BCC email for admin notifications

// Auto-reply Settings
define('AUTO_REPLY_ENABLED', true);
define('AUTO_REPLY_TEMPLATE', 'default'); // 'default' or custom template name

// Business Hours (for response time calculation)
define('BUSINESS_HOURS_START', 9); // 9 AM
define('BUSINESS_HOURS_END', 17); // 5 PM
define('BUSINESS_DAYS', [1, 2, 3, 4, 5]); // Monday to Friday (1=Monday, 7=Sunday)
define('TIMEZONE', 'Europe/Brussels');
?>
