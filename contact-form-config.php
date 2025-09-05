<?php
/**
 * FastCaisse Contact Form Configuration
 *
 * Copy this file and customize the settings for your environment
 */

// Email Configuration
define('ADMIN_EMAIL', 'contact@fastcaisse.be'); // Updated to correct domain
define('COMPANY_NAME', 'FastCaisse');
define('COMPANY_ADDRESS', 'Chaussée de Haecht 1749, 1130 Brussels, Belgium');

// reCAPTCHA Configuration (Optional)
define('RECAPTCHA_SECRET_KEY', 'YOUR_RECAPTCHA_SECRET_KEY_HERE');
define('RECAPTCHA_SITE_KEY', 'YOUR_RECAPTCHA_SITE_KEY_HERE');

// SMTP Configuration (Optional - for better email delivery)
define('USE_SMTP', false); // Set to true to use SMTP
define('SMTP_HOST', 'smtp.your-provider.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@domain.com');
define('SMTP_PASSWORD', 'your-email-password');
define('SMTP_ENCRYPTION', 'tls'); // tls or ssl

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
define('ALLOWED_DOMAINS', ['fastcaisse.be', 'www.fastcaisse.be', 'fastcaisse.com', 'www.fastcaisse.com']); // Add your domains

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
