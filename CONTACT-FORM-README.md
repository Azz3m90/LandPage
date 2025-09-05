# FastCaisse Contact Form System

A complete contact form solution with PHP backend, JavaScript frontend, and SweetAlert integration for the FastCaisse landing page.

## 🚀 Features

- **Complete Form Handling**: PHP backend processing with email notifications
- **SweetAlert Integration**: Beautiful alert messages for user feedback
- **Email Notifications**: Automatic emails to both admin and client
- **Form Validation**: Client-side and server-side validation
- **Security Features**: reCAPTCHA support, spam detection, input sanitization
- **Responsive Design**: Works on all devices
- **Real-time Validation**: Instant feedback as users type
- **Character Counter**: Shows remaining characters for message field

## 📁 Files Overview

### Core Files

- `contact-handler.php` - Main PHP script that processes form submissions
- `assets/js/contact-form.js` - JavaScript handler with SweetAlert integration
- `assets/css/contact-form.css` - Styling for contact forms
- `contact-form-example.html` - Complete example implementation
- `contact-form-config.php` - Configuration file (customize for your setup)

## 🛠 Installation & Setup

### 1. Basic Setup

1. **Upload Files**: Upload all contact form files to your web server
2. **Configure Email**: Edit `contact-handler.php` and set your admin email:
   ```php
   define('ADMIN_EMAIL', 'your-email@fastcaisse.com');
   ```
3. **Test**: Open `contact-form-example.html` in a browser to test

### 2. Integration into Existing Pages

Add these includes to your HTML pages:

```html
<!-- In the <head> section -->
<link
  href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.min.css"
  rel="stylesheet"
/>
<link href="assets/css/contact-form.css" rel="stylesheet" />

<!-- Before closing </body> tag -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.27/dist/sweetalert2.all.min.js"></script>
<script src="assets/js/contact-form.js"></script>
```

### 3. HTML Form Structure

Use this basic form structure:

```html
<form id="contactForm" novalidate>
  <div class="form-group">
    <label for="firstName" class="form-label">First Name <span class="required">*</span></label>
    <input type="text" class="form-control" id="firstName" name="firstName" required />
  </div>

  <div class="form-group">
    <label for="lastName" class="form-label">Last Name <span class="required">*</span></label>
    <input type="text" class="form-control" id="lastName" name="lastName" required />
  </div>

  <div class="form-group">
    <label for="email" class="form-label">Email <span class="required">*</span></label>
    <input type="email" class="form-control" id="email" name="email" required />
  </div>

  <div class="form-group">
    <label for="subject" class="form-label">Subject <span class="required">*</span></label>
    <select class="form-control" id="subject" name="subject" required>
      <option value="">Select a subject</option>
      <option value="Restaurant POS Inquiry">Restaurant POS Inquiry</option>
      <option value="Retail POS Inquiry">Retail POS Inquiry</option>
      <!-- Add more options -->
    </select>
  </div>

  <div class="form-group">
    <label for="message" class="form-label">Message <span class="required">*</span></label>
    <textarea class="form-control" id="message" name="message" required></textarea>
  </div>

  <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Send Message</button>
</form>
```

## ⚙️ Configuration Options

### Email Settings

Edit `contact-handler.php` to configure email settings:

```php
define('ADMIN_EMAIL', 'admin@fastcaisse.com');
define('COMPANY_NAME', 'FastCaisse');
```

### reCAPTCHA Setup (Optional)

1. Get reCAPTCHA keys from [Google reCAPTCHA](https://www.google.com/recaptcha/)
2. Add your keys to `contact-handler.php`:
   ```php
   define('RECAPTCHA_SECRET_KEY', 'your-secret-key');
   ```
3. Add the site key to `contact-form.js`:
   ```javascript
   grecaptcha.execute('YOUR_SITE_KEY', { action: 'contact' });
   ```
4. Include reCAPTCHA script in your HTML:
   ```html
   <script src="https://www.google.com/recaptcha/api.js?render=YOUR_SITE_KEY"></script>
   ```

### SMTP Configuration (Recommended)

For better email delivery, configure SMTP in `contact-handler.php`:

```php
// Use PHPMailer or similar library for SMTP
// Example configuration:
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.your-provider.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@domain.com';
$mail->Password = 'your-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

## 🎨 Customization

### Styling

Modify `assets/css/contact-form.css` to match your brand:

```css
.btn-submit {
  background: linear-gradient(135deg, #your-color 0%, #your-color-2 100%);
}

.contact-form-header {
  background: linear-gradient(135deg, #your-brand-color 0%, #your-brand-color-2 100%);
}
```

### SweetAlert Themes

Customize SweetAlert appearance in `contact-form.js`:

```javascript
Swal.fire({
  title: 'Success!',
  text: 'Your message was sent',
  icon: 'success',
  confirmButtonColor: '#your-color',
  // Add custom CSS class
  customClass: {
    popup: 'your-custom-class',
  },
});
```

### Form Fields

Add custom fields by:

1. Adding HTML input to your form
2. Adding validation in `contact-form.js`
3. Processing the field in `contact-handler.php`

Example - Add company field:

```html
<div class="form-group">
  <label for="company" class="form-label">Company</label>
  <input type="text" class="form-control" id="company" name="company" />
</div>
```

```javascript
// Add to validation if required
if (!data.company && isRequired) {
  return { valid: false, message: 'Company is required' };
}
```

```php
// Add to email template
$data['company'] = sanitizeInput($input['company']);
```

## 🔒 Security Features

### Built-in Protection

1. **Input Sanitization**: All inputs are sanitized before processing
2. **Email Validation**: Server-side email format validation
3. **Spam Detection**: Basic keyword and link detection
4. **Rate Limiting**: Prevents form spam (can be enhanced)
5. **CSRF Protection**: Can be added with tokens

### Additional Security

Add these for enhanced security:

```php
// Add CSRF token
session_start();
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('CSRF token mismatch');
}

// Add IP-based rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
// Implement rate limiting logic
```

## 📧 Email Templates

### Admin Notification Email

The admin receives a formatted email with:

- Contact details
- Subject and message
- Timestamp
- Professional HTML formatting

### Client Confirmation Email

The client receives:

- Thank you message
- Confirmation of receipt
- Contact information
- Expected response time

### Customizing Email Templates

Modify the email HTML in `sendAdminNotification()` and `sendClientConfirmation()` functions in `contact-handler.php`.

## 🐛 Troubleshooting

### Common Issues

1. **Emails not sending**
   - Check your server's mail() function
   - Verify SMTP settings
   - Check spam folders

2. **Form not submitting**
   - Check JavaScript console for errors
   - Verify file paths are correct
   - Ensure PHP file has proper permissions

3. **SweetAlert not showing**
   - Verify SweetAlert2 library is loaded
   - Check for JavaScript errors
   - Ensure DOM is loaded before script runs

### Debug Mode

Enable debug mode in `contact-handler.php`:

```php
// Add at the top for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Add logging
error_log('Contact form data: ' . print_r($data, true));
```

## 📱 Mobile Optimization

The contact form is fully responsive and includes:

- Touch-friendly form inputs
- Optimized button sizes
- Responsive layout
- Mobile-specific styling

## 🧪 Testing

### Test Checklist

- [ ] Form submits successfully
- [ ] Admin email received
- [ ] Client confirmation sent
- [ ] SweetAlert shows success message
- [ ] Form validation works
- [ ] Mobile responsiveness
- [ ] Cross-browser compatibility

### Test Data

Use this test data:

- Name: John Doe
- Email: john@example.com
- Subject: Test Inquiry
- Message: This is a test message to verify the contact form is working properly.

## 📈 Analytics Integration

Track form submissions with Google Analytics:

```javascript
// Add to contact-form.js after successful submission
gtag('event', 'form_submit', {
  event_category: 'contact',
  event_label: 'success',
  value: 1,
});
```

## 🔄 Updates & Maintenance

### Regular Maintenance

1. **Monitor logs**: Check contact-submissions.log regularly
2. **Update libraries**: Keep SweetAlert2 and other libraries updated
3. **Security updates**: Update PHP and server software
4. **Backup**: Regular backups of contact data/logs

### Version Updates

When updating:

1. Backup current files
2. Test on staging environment
3. Update production files
4. Monitor for issues

## 📞 Support

For support with this contact form system:

- Check the troubleshooting section
- Review PHP error logs
- Test with different browsers
- Verify server configuration

---

## 🎯 Quick Start Summary

1. Upload files to your server
2. Edit `ADMIN_EMAIL` in `contact-handler.php`
3. Include CSS and JS in your HTML
4. Add the form HTML with `id="contactForm"`
5. Test the form
6. Customize styling as needed

That's it! Your FastCaisse contact form is ready to use.
