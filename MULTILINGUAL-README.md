# 🌍 FastCaisse Multilingual Contact System

## Overview

The FastCaisse contact form now supports **full multilingual functionality** in three languages:

- **🇫🇷 French (fr)** - Default language
- **🇬🇧 English (en)** - For `-en.html` pages
- **🇳🇱 Dutch (nl)** - For `-nl.html` pages

## 🚀 Language Detection

### Automatic Detection

The system automatically detects the language based on the page URL:

```javascript
// Language detection in contact-form.js
const currentUrl = window.location.pathname;
let language = 'fr'; // Default to French

if (currentUrl.includes('-en.html')) {
  language = 'en';
} else if (currentUrl.includes('-nl.html')) {
  language = 'nl';
}
```

### Page Mapping

| Page Pattern         | Language | Code |
| -------------------- | -------- | ---- |
| `*.html` (no suffix) | French   | `fr` |
| `*-en.html`          | English  | `en` |
| `*-nl.html`          | Dutch    | `nl` |

## 📧 Multilingual Email Templates

### Admin Notification Emails

Professional admin notifications with language-specific content:

- **Language badge** indicating source language
- **Translated field labels** (Name, Email, Phone, etc.)
- **Localized timestamps** and formatting
- **Consistent branding** across all languages

### Client Confirmation Emails

Personalized client confirmations featuring:

- **Thank you messages** in the appropriate language
- **Next steps** clearly explained
- **Contact information** with localized labels
- **Professional formatting** with language indicators

### Email Examples

**French (fr):**

```
Subject: Nouvelle soumission de formulaire de contact - FastCaisse
Content: "Merci de nous avoir contactés! Nous avons reçu votre message..."
```

**English (en):**

```
Subject: New Contact Form Submission - FastCaisse
Content: "Thank you for contacting FastCaisse! We have received your message..."
```

**Dutch (nl):**

```
Subject: Nieuwe contactformulier inzending - FastCaisse
Content: "Bedankt voor het contact! We hebben uw bericht ontvangen..."
```

## ✅ Multilingual Validation

### Server-Side Validation

Enhanced PHP validation with localized error messages:

```php
// French validation
'required' => '%s est requis'
'email_invalid' => 'Veuillez saisir une adresse email valide'

// English validation
'required' => '%s is required'
'email_invalid' => 'Please enter a valid email address'

// Dutch validation
'required' => '%s is vereist'
'email_invalid' => 'Voer een geldig e-mailadres in'
```

### Client-Side Validation

JavaScript validation with real-time multilingual feedback:

```javascript
// Localized validation messages
const messages = {
  en: { firstNameRequired: 'First name is required' },
  fr: { firstNameRequired: 'Le prénom est requis' },
  nl: { firstNameRequired: 'Voornaam is vereist' },
};
```

## 🔧 Implementation Details

### PHP Backend (`contact-handler.php`)

```php
// Language detection function
function detectLanguage($input) {
    if (isset($input['language']) && in_array($input['language'], ['en', 'fr', 'nl'])) {
        return $input['language'];
    }

    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (strpos($referer, '-en.html') !== false) {
        return 'en';
    } elseif (strpos($referer, '-nl.html') !== false) {
        return 'nl';
    } else {
        return 'fr'; // Default
    }
}

// Multilingual email functions
sendAdminNotification($data, $language);
sendClientConfirmation($data, $language);
```

### JavaScript Frontend (`contact-form.js`)

```javascript
// Language detection and form submission
const language = detectLanguageFromURL();
data.language = language;

// Localized user interface messages
const messages = getLocalizedMessages(language);
Swal.fire({
  title: messages.successTitle,
  text: messages.successText,
});
```

## 🧪 Testing Multilingual Functionality

### Enhanced Test Suite

The advanced test script (`test-email-advanced.php`) includes:

- **Multilingual email testing** - Sends test emails in all three languages
- **Validation testing** - Tests validation rules in each language
- **Language detection testing** - Verifies correct language identification
- **SMTP configuration testing** - Tests connectivity for all language variants

### Test Coverage

✅ **Email delivery** in all languages
✅ **Validation messages** localized correctly
✅ **Language detection** from page URLs
✅ **Error handling** with appropriate translations
✅ **Admin notifications** with language badges
✅ **Client confirmations** fully localized

## 📋 Supported Fields & Translations

### Form Fields

| Field      | French    | English    | Dutch      |
| ---------- | --------- | ---------- | ---------- |
| First Name | Prénom    | First Name | Voornaam   |
| Last Name  | Nom       | Last Name  | Achternaam |
| Email      | Email     | Email      | E-mail     |
| Phone      | Téléphone | Phone      | Telefoon   |
| Subject    | Sujet     | Subject    | Onderwerp  |
| Message    | Message   | Message    | Bericht    |

### Validation Messages

| Type          | French                 | English               | Dutch                |
| ------------- | ---------------------- | --------------------- | -------------------- |
| Required      | %s est requis          | %s is required        | %s is vereist        |
| Email Invalid | Adresse email invalide | Invalid email address | Ongeldig e-mailadres |
| Too Long      | Trop long (%d max)     | Too long (%d max)     | Te lang (%d max)     |
| Too Short     | Trop court (%d min)    | Too short (%d min)    | Te kort (%d min)     |

## 🚀 Usage Examples

### Basic Form Submission

```html
<!-- French page: index.html -->
<form id="contact-form">
  <!-- Form automatically detects 'fr' language -->
</form>

<!-- English page: index-en.html -->
<form id="contact-form">
  <!-- Form automatically detects 'en' language -->
</form>

<!-- Dutch page: index-nl.html -->
<form id="contact-form">
  <!-- Form automatically detects 'nl' language -->
</form>
```

### Manual Language Override

```javascript
// Override automatic detection
data.language = 'en'; // Force English
```

### Email Template Customization

```php
// Add custom translations
$translations['en']['custom_message'] = 'Custom English message';
$translations['fr']['custom_message'] = 'Message français personnalisé';
$translations['nl']['custom_message'] = 'Aangepast Nederlands bericht';
```

## 🔒 Security Features

All multilingual features maintain the enhanced security:

- ✅ **XSS protection** with proper encoding
- ✅ **Input validation** in all languages
- ✅ **Rate limiting** per email address
- ✅ **Spam detection** with multilingual keywords
- ✅ **CSRF protection** ready for implementation

## 📊 Performance Impact

The multilingual implementation is optimized for performance:

- **Minimal overhead** - Language detection is fast
- **Cached translations** - Messages loaded once per request
- **Efficient validation** - Same rules, localized messages
- **SMTP optimization** - Single connection for all languages

## 🎯 Future Enhancements

Planned improvements for the multilingual system:

- 📅 **Date/time localization** with regional formats
- 💱 **Currency formatting** for pricing pages
- 🌐 **Additional languages** (German, Spanish, Italian)
- 🗂️ **Translation management** system for easy updates
- 📱 **Mobile-specific** language detection

## 💡 Best Practices

### For Developers

1. **Always test** in all three languages
2. **Validate translations** with native speakers
3. **Use language detection** rather than manual flags
4. **Test email delivery** in each language variant
5. **Monitor error rates** per language for issues

### For Content Managers

1. **Keep translations consistent** across pages
2. **Update all language versions** simultaneously
3. **Test contact forms** on each page variant
4. **Monitor email deliverability** in all languages
5. **Review spam detection** for language-specific terms

---

## 🎉 Summary

The FastCaisse multilingual contact system provides:

✅ **Automatic language detection** from page URLs
✅ **Complete email localization** for admin and client messages
✅ **Multilingual validation** with appropriate error messages
✅ **Enhanced user experience** in French, English, and Dutch
✅ **Professional email templates** with language indicators
✅ **Comprehensive testing tools** for all languages
✅ **Maintained security** across all language variants

Your website visitors now receive a fully localized experience that matches their language preference, improving engagement and professionalism! 🌍
