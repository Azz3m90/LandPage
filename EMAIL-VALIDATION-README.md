# 🔐 FastCaisse Enhanced Email & Validation System

## Overview

The FastCaisse contact form has been upgraded with comprehensive validation, security features, and SMTP email delivery using Infomaniak's email service.

## 📧 Email Configuration

### SMTP Settings

- **Provider**: Infomaniak
- **Host**: `mail.infomaniak.com`
- **Port**: `587`
- **Encryption**: `TLS`
- **Username**: `contact@fastcaisse.be`
- **Admin Email**: `contact@fastcaisse.be`

### Email Features

- ✅ **Professional SMTP delivery** via Infomaniak
- ✅ **HTML formatted emails** with responsive design
- ✅ **Admin notifications** for all form submissions
- ✅ **Client confirmations** with professional branding
- ✅ **Automatic fallback** to PHP mail() if SMTP fails
- ✅ **Detailed error logging** for troubleshooting

## 🛡️ Enhanced Validation Features

### Required Field Validation

- **First Name**: 2-50 characters
- **Last Name**: 2-50 characters
- **Email**: Must be valid format, under 254 characters
- **Subject**: 5-200 characters
- **Message**: 10-2000 characters

### Advanced Email Validation

- ✅ **Format validation** using PHP filter_var
- ✅ **Length limits** (max 254 characters)
- ✅ **Character filtering** (blocks suspicious characters)
- ✅ **Domain validation** (exactly one @ symbol)
- ✅ **Disposable email blocking** (10minutemail, guerrillamail, etc.)

### Phone Number Validation (Optional)

- ✅ **Format validation** (10-20 digits with formatting)
- ✅ **Pattern detection** (blocks repeated digits spam)
- ✅ **International format support** (+, -, (), .)

### Content Security Validation

- 🚫 **Spam keyword detection** (viagra, casino, lottery, etc.)
- 🚫 **URL blocking** in messages (prevents link spam)
- 🚫 **HTML/Script tag filtering**
- 🚫 **Repeated character detection** (aaaaaaa spam)
- 🚫 **JavaScript injection prevention**

### Rate Limiting

- ⏱️ **5-minute cooldown** between submissions per email
- 🔄 **Automatic cleanup** of rate limit files
- 📊 **Per-email tracking** using secure hashing

### XSS & Security Protection

- 🔒 **HTML entity encoding** for all user input
- 🧹 **Strip HTML tags** from form data
- 🚫 **Control character removal** (null bytes, etc.)
- 🛡️ **CSRF protection ready** (honeypot field support)

## 🧪 Testing Tools

### Basic Test Script: `test-email-config.php`

Simple testing interface with:

- SMTP connection testing
- Email delivery verification
- Configuration validation
- Error log checking

### Advanced Test Suite: `test-email-advanced.php`

Comprehensive testing dashboard with:

- **Connection Testing**: DNS, Port, SMTP handshake, TLS negotiation
- **Email Testing**: Full delivery testing with custom recipients
- **Validation Testing**: Complete form validation rule testing
- **Security Audit**: File permissions, PHP config, security checks
- **System Diagnostics**: PHP info, extensions, server environment

## 📁 File Structure

```
├── contact-form-config.php          # Main configuration file
├── contact-handler.php              # Enhanced form processor
├── test-email-config.php           # Basic testing tool
├── test-email-advanced.php         # Advanced testing suite
├── assets/js/contact-form.js        # Frontend form handling
├── assets/css/contact-form.css      # Form styling
└── EMAIL-VALIDATION-README.md      # This documentation
```

## 🚀 Getting Started

### 1. Basic Setup Verification

```bash
# Open basic test page
http://your-domain.com/test-email-config.php

# Click "Test SMTP Connection"
# Click "Send Test Email"
```

### 2. Advanced Testing

```bash
# Open advanced test suite
http://your-domain.com/test-email-advanced.php

# Run all tests in different tabs
```

### 3. Production Checklist

- [ ] SMTP connection tested successfully
- [ ] Test emails delivered to admin inbox
- [ ] Validation rules working correctly
- [ ] Rate limiting functioning
- [ ] Security audit passed
- [ ] **Test files deleted** (`test-email-*.php`)

## 🔧 Configuration Options

### Email Settings (`contact-form-config.php`)

```php
// Enable SMTP
define('USE_SMTP', true);

// SMTP Configuration
define('SMTP_HOST', 'mail.infomaniak.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'contact@fastcaisse.be');
define('SMTP_PASSWORD', '2VeryFast4u&me');
define('SMTP_ENCRYPTION', 'tls');

// Admin Email
define('ADMIN_EMAIL', 'contact@fastcaisse.be');
```

### Security Settings

```php
// Domain Restrictions
define('ALLOWED_DOMAINS', ['fastcaisse.be', 'fastcaisse.com']);

// Honeypot Protection
define('ENABLE_HONEYPOT', true);

// Referrer Checking
define('REQUIRE_REFERER', true);
```

## 🐛 Troubleshooting

### Common Issues

#### SMTP Connection Failed

```bash
# Check server connectivity
telnet mail.infomaniak.com 587

# Verify credentials in configuration
# Check firewall rules for port 587
```

#### Emails Not Delivered

1. **Check spam folder** first
2. **Verify admin email** is accessible
3. **Check server logs** for SMTP errors
4. **Test basic SMTP** with telnet

#### Validation Too Strict

```php
// Adjust validation rules in validateFormData()
$lengthRules = [
    'subject' => ['min' => 3, 'max' => 300],  // Less strict
    'message' => ['min' => 5, 'max' => 3000]  // Allow longer messages
];
```

### Log Locations

- Server error logs
- PHP error logs
- Application specific logs in contact form directory

## 🔐 Security Best Practices

### Production Security

1. **Remove test files** after testing
2. **Use environment variables** for credentials
3. **Implement CSRF tokens**
4. **Enable HTTPS** for all form submissions
5. **Regular security updates** for PHP and server
6. **Monitor email bounce rates**
7. **Set up log monitoring**

### File Permissions

```bash
# Secure configuration file
chmod 644 contact-form-config.php

# Remove test files
rm test-email-*.php
```

## 📊 Monitoring & Analytics

### Email Delivery Metrics

- Track successful/failed deliveries
- Monitor SMTP connection issues
- Watch for spam pattern increases
- Analyze form abandonment rates

### Security Monitoring

- Rate limiting triggers
- Spam detection alerts
- Failed validation attempts
- Suspicious pattern detection

## 📞 Support

For technical issues with the FastCaisse email system:

1. **Check this documentation** first
2. **Run diagnostic tests** with test-email-advanced.php
3. **Check server logs** for specific errors
4. **Verify SMTP credentials** with Infomaniak
5. **Contact system administrator** with diagnostic results

---

## 🎯 Validation Rules Summary

| Field      | Min Length | Max Length | Special Rules               |
| ---------- | ---------- | ---------- | --------------------------- |
| First Name | 2 chars    | 50 chars   | No HTML/special chars       |
| Last Name  | 2 chars    | 50 chars   | No HTML/special chars       |
| Email      | -          | 254 chars  | Valid format, no disposable |
| Phone      | 10 digits  | 20 digits  | Optional, pattern validated |
| Subject    | 5 chars    | 200 chars  | No spam keywords            |
| Message    | 10 chars   | 2000 chars | No URLs, spam, or scripts   |

## ⚡ Performance Features

- **Efficient validation** with early exit on errors
- **Cached DNS resolution** for SMTP connections
- **Connection pooling** for multiple emails
- **Optimized regular expressions** for pattern matching
- **Minimal memory footprint** for rate limiting

This enhanced system provides enterprise-level email delivery and security while maintaining ease of use for your FastCaisse website visitors.
