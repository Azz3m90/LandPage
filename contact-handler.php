<?php
/**
 * FastCaisse Contact Form Handler
 *
 * This script handles contact form submissions with:
 * - Email validation and security
 * - reCAPTCHA verification
 * - Admin notifications
 * - Client confirmations
 * - SweetAlert responses
 * - SMTP support
 */

// Include PHPMailer autoloader
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include configuration if available
if (file_exists('contact-form-config.php')) {
    require_once 'contact-form-config.php';
} else {
    // Load environment variables from .env file if config doesn't exist
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

    // Fallback configuration (without hardcoded passwords!)
    define('COMPANY_NAME', getenv('COMPANY_NAME') ?: 'FastCaisse');
    define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'contact@fastcaisse.be');
    define('USE_SMTP', filter_var(getenv('USE_SMTP') ?: 'true', FILTER_VALIDATE_BOOLEAN));
    define('SMTP_HOST', getenv('SMTP_HOST') ?: 'mail.infomaniak.com');
    define('SMTP_PORT', intval(getenv('SMTP_PORT') ?: 587));
    define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: 'contact@fastcaisse.be');
    define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: ''); // NEVER hardcode passwords!
    define('SMTP_ENCRYPTION', getenv('SMTP_ENCRYPTION') ?: 'tls');
}

// Only define fallback keys if not already defined in config
if (!defined('RECAPTCHA_SECRET_KEY')) {
    define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY') ?: '');
}
if (!defined('TURNSTILE_SECRET_KEY')) {
    define('TURNSTILE_SECRET_KEY', getenv('TURNSTILE_SECRET_KEY') ?: '');
}
if (!defined('TURNSTILE_SITE_KEY')) {
    define('TURNSTILE_SITE_KEY', getenv('TURNSTILE_SITE_KEY') ?: '');
}

// Set content type for JSON responses
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed',
        'type' => 'error'
    ]);
    exit();
}

try {
    // Get and validate input data
    $input = json_decode(file_get_contents('php://input'), true);

    // If JSON decode fails, try $_POST
    if ($input === null) {
        $input = $_POST;
    }

    // Detect language from referer or input
    $language = detectLanguage($input);

    // Enhanced validation and sanitization
    $validationResult = validateFormData($input, $language);

    if (!empty($validationResult['errors'])) {
        $errorMessages = array_values($validationResult['errors']);
        $translations = getTranslations($language);
        echo json_encode([
            'success' => false,
            'message' => $translations['validation_failed'] . ': ' . implode(', ', $errorMessages),
            'type' => 'error',
            'fields' => $validationResult['errors']
        ]);
        exit();
    }

    // Use validated and sanitized data
    $data = $validationResult['data'];
    $data['language'] = $language;

    // Add additional fields not handled by validation function
    $data['recaptcha'] = isset($input['recaptcha']) ? $input['recaptcha'] : '';
    $data['cf-turnstile-response'] = isset($input['cf-turnstile-response']) ? $input['cf-turnstile-response'] : '';
    $data['honeypot'] = isset($input['honeypot']) ? $input['honeypot'] : '';

    // REQUIRE Turnstile verification - Block ALL submissions without it
    if (empty($data['cf-turnstile-response'])) {
        $translations = getTranslations($language);
        $securityMessage = $language === 'en' ? 'Security verification is required. Please complete the captcha to submit.' :
                          ($language === 'fr' ? 'La vérification de sécurité est requise. Veuillez compléter le captcha pour soumettre.' :
                           ($language === 'nl' ? 'Beveiligingsverificatie is vereist. Voltooi de captcha om te verzenden.' :
                            'Security verification is required. Please complete the captcha to submit.'));

        error_log('[SECURITY] Form submission blocked - No Turnstile token provided');

        echo json_encode([
            'success' => false,
            'message' => $securityMessage,
            'type' => 'security_error',
            'error_code' => 'CAPTCHA_REQUIRED'
        ]);
        exit();
    }

    // Verify the Turnstile response (now mandatory)
    $turnstileResult = verifyTurnstile($data['cf-turnstile-response']);
    if (!$turnstileResult['success']) {
        $translations = getTranslations($language);
        $securityMessage = $language === 'en' ? 'Security verification failed. Please try again.' :
                          ($language === 'fr' ? 'Échec de la vérification de sécurité. Veuillez réessayer.' :
                           ($language === 'nl' ? 'Veiligheidsverificatie mislukt. Probeer het opnieuw.' :
                            'Security verification failed. Please try again.'));

        error_log('[SECURITY] Form submission blocked - Invalid Turnstile token');

        echo json_encode([
            'success' => false,
            'message' => $securityMessage,
            'type' => 'security_error',
            'error_code' => 'CAPTCHA_INVALID'
        ]);
        exit();
    }

    // Basic spam protection
    if (detectSpam($data)) {
        $translations = getTranslations($language);
        $spamMessage = $language === 'en' ? 'Your message appears to be spam. Please contact us directly.' :
                      ($language === 'fr' ? 'Votre message semble être du spam. Contactez-nous directement.' :
                       'Uw bericht lijkt spam te zijn. Neem direct contact met ons op.');
        echo json_encode([
            'success' => false,
            'message' => $spamMessage,
            'type' => 'error'
        ]);
        exit();
    }

    // Send admin notification
    $adminEmailSent = sendAdminNotification($data, $language);

    // Send client confirmation
    $clientEmailSent = sendClientConfirmation($data, $language);

    // Log the submission (optional)
    logSubmission($data);

    // Check if at least admin email was sent
    if (!$adminEmailSent) {
        // If admin email failed, this is a critical error
        $translations = getTranslations($language);
        $errorMessage = $language === 'en' ? 'Unable to send your message at this time. Please try again later or contact us directly at contact@fastcaisse.be' :
                       ($language === 'fr' ? 'Impossible d\'envoyer votre message pour le moment. Veuillez réessayer plus tard ou nous contacter directement à contact@fastcaisse.be' :
                        'Kan uw bericht momenteel niet verzenden. Probeer het later opnieuw of neem rechtstreeks contact op via contact@fastcaisse.be');

        echo json_encode([
            'success' => false,
            'message' => $errorMessage,
            'type' => 'error',
            'details' => [
                'adminNotified' => false,
                'confirmationSent' => false
            ]
        ]);
        exit();
    }

    // Return success response
    $translations = getTranslations($language);
    echo json_encode([
        'success' => true,
        'message' => $translations['success_message'],
        'type' => 'success',
        'details' => [
            'adminNotified' => $adminEmailSent,
            'confirmationSent' => $clientEmailSent
        ]
    ]);

} catch (Exception $e) {
    // Log error (in production, log to file instead of displaying)
    error_log('Contact form error: ' . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your request. Please try again or contact us directly.',
        'type' => 'error'
    ]);
}

/**
 * Detect language from referer URL or input
 */
function detectLanguage($input) {
    // Check if language is explicitly passed
    if (isset($input['language']) && in_array($input['language'], ['en', 'fr', 'nl'])) {
        return $input['language'];
    }

    // Detect from HTTP referer
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    if (strpos($referer, '-en.html') !== false) {
        return 'en';
    } elseif (strpos($referer, '-nl.html') !== false) {
        return 'nl';
    } else {
        // Default to French for base URLs (.html without suffix)
        return 'fr';
    }
}

/**
 * Get translations for the specified language
 */
function getTranslations($language) {
    $translations = [
        'en' => [
            'validation_failed' => 'Validation failed',
            'success_message' => 'Thank you for your message! We have received your inquiry and will get back to you within 24-48 hours.',
            'email_subject_admin' => 'New Contact Form Submission - FastCaisse',
            'email_subject_client' => 'Thank you for contacting FastCaisse',
            'new_contact_submission' => 'New Contact Form Submission',
            'received_message' => 'You have received a new message through the FastCaisse website contact form.',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'subject' => 'Subject',
            'message' => 'Message',
            'submitted' => 'Submitted',
            'auto_generated' => 'This email was automatically generated by the FastCaisse contact form system.',
            'thank_you_title' => 'Thank You for Contacting FastCaisse!',
            'thank_you_message' => 'We have received your message and appreciate your interest in our POS & CRM solutions.',
            'response_time' => 'Our team will review your inquiry and respond within 24-48 hours.',
            'what_happens_next' => 'What happens next?',
            'step1' => 'Our team reviews your message',
            'step2' => 'We prepare a personalized response',
            'step3' => 'You receive our detailed reply',
            'contact_info' => 'In the meantime, feel free to explore our website or contact us directly:',
            'visit_website' => 'Visit our website: https://fastcaisse.be',
            'direct_email' => 'Email us directly: contact@fastcaisse.be',
            'best_regards' => 'Best regards,<br>The FastCaisse Team'
        ],
        'fr' => [
            'validation_failed' => 'Échec de la validation',
            'success_message' => 'Merci pour votre message ! Nous avons reçu votre demande et vous répondrons dans les 24-48 heures.',
            'email_subject_admin' => 'Nouvelle soumission de formulaire de contact - FastCaisse',
            'email_subject_client' => 'Merci de nous avoir contactés - FastCaisse',
            'new_contact_submission' => 'Nouvelle soumission de formulaire de contact',
            'received_message' => 'Vous avez reçu un nouveau message via le formulaire de contact du site FastCaisse.',
            'name' => 'Nom',
            'email' => 'Email',
            'phone' => 'Téléphone',
            'subject' => 'Sujet',
            'message' => 'Message',
            'submitted' => 'Soumis',
            'auto_generated' => 'Cet email a été généré automatiquement par le système de formulaire de contact FastCaisse.',
            'thank_you_title' => 'Merci de nous avoir contactés !',
            'thank_you_message' => 'Nous avons reçu votre message et apprécions votre intérêt pour nos solutions de caisse et CRM.',
            'response_time' => 'Notre équipe examinera votre demande et vous répondra dans les 24-48 heures.',
            'what_happens_next' => 'Que se passe-t-il maintenant ?',
            'step1' => 'Notre équipe examine votre message',
            'step2' => 'Nous préparons une réponse personnalisée',
            'step3' => 'Vous recevez notre réponse détaillée',
            'contact_info' => 'En attendant, n\'hésitez pas à explorer notre site web ou nous contacter directement :',
            'visit_website' => 'Visitez notre site : https://fastcaisse.be',
            'direct_email' => 'Contactez-nous directement : contact@fastcaisse.be',
            'best_regards' => 'Meilleures salutations,<br>L\'équipe FastCaisse'
        ],
        'nl' => [
            'validation_failed' => 'Validatie mislukt',
            'success_message' => 'Bedankt voor uw bericht! We hebben uw aanvraag ontvangen en zullen binnen 24-48 uur reageren.',
            'email_subject_admin' => 'Nieuwe contactformulier inzending - FastCaisse',
            'email_subject_client' => 'Bedankt voor het contact - FastCaisse',
            'new_contact_submission' => 'Nieuwe contactformulier inzending',
            'received_message' => 'U heeft een nieuw bericht ontvangen via het contactformulier van de FastCaisse website.',
            'name' => 'Naam',
            'email' => 'E-mail',
            'phone' => 'Telefoon',
            'subject' => 'Onderwerp',
            'message' => 'Bericht',
            'submitted' => 'Ingediend',
            'auto_generated' => 'Deze e-mail is automatisch gegenereerd door het FastCaisse contactformulier systeem.',
            'thank_you_title' => 'Bedankt voor het contact!',
            'thank_you_message' => 'We hebben uw bericht ontvangen en waarderen uw interesse in onze kassasysteem en CRM oplossingen.',
            'response_time' => 'Ons team zal uw aanvraag bekijken en binnen 24-48 uur reageren.',
            'what_happens_next' => 'Wat gebeurt er nu?',
            'step1' => 'Ons team bekijkt uw bericht',
            'step2' => 'We bereiden een gepersonaliseerd antwoord voor',
            'step3' => 'U ontvangt ons gedetailleerde antwoord',
            'contact_info' => 'In de tussentijd kunt u gerust onze website verkennen of direct contact opnemen:',
            'visit_website' => 'Bezoek onze website: https://fastcaisse.be',
            'direct_email' => 'E-mail ons direct: contact@fastcaisse.be',
            'best_regards' => 'Met vriendelijke groeten,<br>Het FastCaisse Team'
        ]
    ];

    return $translations[$language] ?? $translations['fr'];
}

/**
 * Get validation error messages in the specified language
 */
function getValidationMessages($language) {
    $messages = [
        'en' => [
            'required' => '%s is required',
            'email_invalid' => 'Please enter a valid email address',
            'email_too_long' => 'Email address is too long',
            'email_invalid_chars' => 'Email contains invalid characters',
            'email_multiple_at' => 'Email must contain exactly one @ symbol',
            'email_disposable' => 'Please use a permanent email address',
            'phone_invalid' => 'Please enter a valid phone number (10-20 digits)',
            'phone_repeated' => 'Phone number appears to be invalid (too many repeated digits)',
            'length_min' => '%s must be at least %d characters',
            'length_max' => '%s must be less than %d characters',
            'spam_content' => 'Message contains suspicious content',
            'rate_limit' => 'Please wait at least 1 minute between submissions'
        ],
        'fr' => [
            'required' => '%s est requis',
            'email_invalid' => 'Veuillez saisir une adresse email valide',
            'email_too_long' => 'L\'adresse email est trop longue',
            'email_invalid_chars' => 'L\'email contient des caractères non valides',
            'email_multiple_at' => 'L\'email doit contenir exactement un symbole @',
            'email_disposable' => 'Veuillez utiliser une adresse email permanente',
            'phone_invalid' => 'Veuillez saisir un numéro de téléphone valide (10-20 chiffres)',
            'phone_repeated' => 'Le numéro de téléphone semble invalide (trop de chiffres répétés)',
            'length_min' => '%s doit contenir au moins %d caractères',
            'length_max' => '%s doit contenir moins de %d caractères',
            'spam_content' => 'Le message contient du contenu suspect',
            'rate_limit' => 'Veuillez attendre au moins 1 minute entre les soumissions'
        ],
        'nl' => [
            'required' => '%s is vereist',
            'email_invalid' => 'Voer een geldig e-mailadres in',
            'email_too_long' => 'E-mailadres is te lang',
            'email_invalid_chars' => 'E-mail bevat ongeldige tekens',
            'email_multiple_at' => 'E-mail moet precies één @ symbool bevatten',
            'email_disposable' => 'Gebruik een permanent e-mailadres',
            'phone_invalid' => 'Voer een geldig telefoonnummer in (10-20 cijfers)',
            'phone_repeated' => 'Telefoonnummer lijkt ongeldig (te veel herhaalde cijfers)',
            'length_min' => '%s moet minimaal %d tekens bevatten',
            'length_max' => '%s moet minder dan %d tekens bevatten',
            'spam_content' => 'Bericht bevat verdachte inhoud',
            'rate_limit' => 'Wacht minstens 1 minuut tussen inzendingen'
        ]
    ];

    return $messages[$language] ?? $messages['fr'];
}

/**
 * Get field names in the specified language
 */
function getFieldNames($language) {
    $names = [
        'en' => [
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'subject' => 'Subject',
            'message' => 'Message'
        ],
        'fr' => [
            'firstName' => 'Prénom',
            'lastName' => 'Nom',
            'email' => 'Email',
            'phone' => 'Téléphone',
            'subject' => 'Sujet',
            'message' => 'Message'
        ],
        'nl' => [
            'firstName' => 'Voornaam',
            'lastName' => 'Achternaam',
            'email' => 'E-mail',
            'phone' => 'Telefoon',
            'subject' => 'Onderwerp',
            'message' => 'Bericht'
        ]
    ];

    return $names[$language] ?? $names['fr'];
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Verify Cloudflare Turnstile response
 */
function verifyTurnstile($turnstileResponse) {
    $secretKey = "0x4AAAAAABzaI6mJ9vTZWTTGJEjdGyRtPBA";
    $verifyURL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    $data = [
        'secret' => $secretKey,
        'response' => $turnstileResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($verifyURL, false, $context);

    return json_decode($result, true);
}

/**
 * Verify reCAPTCHA response (Legacy)
 */
function verifyRecaptcha($recaptchaResponse) {
    $secretKey = RECAPTCHA_SECRET_KEY;
    $verifyURL = 'https://www.google.com/recaptcha/api/siteverify';

    $data = [
        'secret' => $secretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($verifyURL, false, $context);

    return json_decode($result, true);
}

/**
 * Send admin notification email with multilingual support
 */
function sendAdminNotification($data, $language = 'fr') {
    $to = ADMIN_EMAIL;
    $translations = getTranslations($language);
    $subject = $translations['email_subject_admin'];

    $body = "
    <html>
    <head>
        <title>" . htmlspecialchars($translations['new_contact_submission']) . "</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
            .container { background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background-color: #007bff; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; position: relative; }
            .field { margin-bottom: 15px; padding: 10px; border-left: 3px solid #007bff; background-color: #f8f9fa; }
            .label { font-weight: bold; color: #333; margin-bottom: 5px; }
            .value { color: #555; }
            .message-box { background-color: white; padding: 15px; border: 1px solid #ddd; border-radius: 5px; line-height: 1.5; }
            .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; }
            .language-badge { position: absolute; top: 10px; right: 15px; background: #28a745; color: white; padding: 3px 8px; border-radius: 12px; font-size: 11px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <span class='language-badge'>" . strtoupper($language) . "</span>
                <h2>🔔 " . htmlspecialchars($translations['new_contact_submission']) . "</h2>
                <p>" . htmlspecialchars($translations['received_message']) . "</p>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>" . htmlspecialchars($translations['name']) . ":</div>
                    <div class='value'>" . htmlspecialchars($data['firstName'] . ' ' . $data['lastName']) . "</div>
                </div>

                <div class='field'>
                    <div class='label'>" . htmlspecialchars($translations['email']) . ":</div>
                    <div class='value'>" . htmlspecialchars($data['email']) . "</div>
                </div>

                " . (!empty($data['phone']) ? "
                <div class='field'>
                    <div class='label'>" . htmlspecialchars($translations['phone']) . ":</div>
                    <div class='value'>" . htmlspecialchars($data['phone']) . "</div>
                </div>
                " : "") . "

                <div class='field'>
                    <div class='label'>" . htmlspecialchars($translations['subject']) . ":</div>
                    <div class='value'>" . htmlspecialchars($data['subject']) . "</div>
                </div>

                <div class='field'>
                    <div class='label'>" . htmlspecialchars($translations['message']) . ":</div>
                    <div class='message-box'>" . nl2br(htmlspecialchars($data['message'])) . "</div>
                </div>

                <div class='field'>
                    <div class='label'>" . htmlspecialchars($translations['submitted']) . ":</div>
                    <div class='value'>" . date('Y-m-d H:i:s') . "</div>
                </div>
            </div>
            <div class='footer'>
                <p>" . htmlspecialchars($translations['auto_generated']) . "</p>
            </div>
        </div>
    </body>
    </html>";

    // Always use SMTP with PHPMailer
    // Use SMTP_USERNAME as from address to match authentication
    return sendEmailSMTP($to, $subject, $body, SMTP_USERNAME, 'FastCaisse Website', $data['email']);
}

/**
 * Send client confirmation email with multilingual support
 */
function sendClientConfirmation($data, $language = 'fr') {
    $to = $data['email'];
    $translations = getTranslations($language);
    $subject = $translations['email_subject_client'];

    $body = "
    <html>
    <head>
        <title>" . htmlspecialchars($translations['thank_you_title']) . "</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
            .container { background-color: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background-color: #28a745; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; position: relative; }
            .content { line-height: 1.6; color: #333; }
            .highlight { color: #007bff; font-weight: bold; }
            .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px; text-align: center; }
            ul { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }
            li { margin-bottom: 5px; }
            .steps { background-color: #e8f4f8; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .step { padding: 5px 0; }
            .language-badge { position: absolute; top: 10px; right: 15px; background: #17a2b8; color: white; padding: 3px 8px; border-radius: 12px; font-size: 11px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <span class='language-badge'>" . strtoupper($language) . "</span>
                <h2>✅ " . htmlspecialchars($translations['thank_you_title']) . "</h2>
                <p>" . htmlspecialchars($translations['thank_you_message']) . "</p>
            </div>
            <div class='content'>
                <p>Dear <span class='highlight'>" . htmlspecialchars($data['firstName'] . ' ' . $data['lastName']) . "</span>,</p>

                <p>" . htmlspecialchars($translations['response_time']) . "</p>

                <div class='steps'>
                    <h3>" . htmlspecialchars($translations['what_happens_next']) . "</h3>
                    <div class='step'>✓ " . htmlspecialchars($translations['step1']) . "</div>
                    <div class='step'>✓ " . htmlspecialchars($translations['step2']) . "</div>
                    <div class='step'>✓ " . htmlspecialchars($translations['step3']) . "</div>
                </div>

                <p>" . htmlspecialchars($translations['contact_info']) . "</p>
                <ul>
                    <li><strong>" . ($language === 'en' ? 'Website' : ($language === 'fr' ? 'Site web' : 'Website')) . ":</strong> https://fastcaisse.be</li>
                    <li><strong>Email:</strong> contact@fastcaisse.be</li>
                    <li><strong>" . ($language === 'en' ? 'Address' : ($language === 'fr' ? 'Adresse' : 'Adres')) . ":</strong> Chaussée de Haecht 1749, 1130 Brussels, Belgium</li>
                </ul>

                <p>" . $translations['best_regards'] . "</p>
            </div>
            <div class='footer'>
                <p>" . htmlspecialchars($translations['auto_generated']) . "</p>
                <p>© " . date('Y') . " FastCaisse. " . ($language === 'en' ? 'All rights reserved' : ($language === 'fr' ? 'Tous droits réservés' : 'Alle rechten voorbehouden')) . ".</p>
            </div>
        </div>
    </body>
    </html>";

    // Always use SMTP with PHPMailer
    // Use SMTP_USERNAME as from address to match authentication
    return sendEmailSMTP($to, $subject, $body, SMTP_USERNAME, 'FastCaisse Team');
}

/**
 * Basic spam detection (lenient for business inquiries)
 */
function detectSpam($data) {
    $spamKeywords = ['viagra', 'casino', 'lottery', 'click here', 'free money'];
    $message = strtolower($data['message']);

    foreach ($spamKeywords as $keyword) {
        if (strpos($message, $keyword) !== false) {
            return true;
        }
    }

    // Check for too many links (increased limit for business messages)
    if (preg_match_all('/http[s]?:\/\//', $data['message']) > 5) {
        return true;
    }

    // Check for excessive caps (more than 50% of the message)
    $capsCount = preg_match_all('/[A-Z]/', $data['message']);
    $totalLetters = preg_match_all('/[a-zA-Z]/', $data['message']);
    if ($totalLetters > 0 && ($capsCount / $totalLetters) > 0.5) {
        return true;
    }

    return false;
}

/**
 * Enhanced form data validation and sanitization with multilingual support
 */
function validateFormData($data, $language = 'fr') {
    $errors = [];
    $messages = getValidationMessages($language);
    $fieldNames = getFieldNames($language);

    // Required fields validation
    $requiredFields = ['firstName', 'lastName', 'email', 'subject', 'message'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field]) || trim($data[$field]) === '') {
            $fieldName = $fieldNames[$field] ?? ucfirst($field);
            $errors[$field] = sprintf($messages['required'], $fieldName);
        }
    }

    // Email validation with multiple checks
    if (!empty($data['email'])) {
        $email = trim($data['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = $messages['email_invalid'];
        } elseif (strlen($email) > 254) {
            $errors['email'] = $messages['email_too_long'];
        } elseif (preg_match('/[<>"\']/', $email)) {
            $errors['email'] = $messages['email_invalid_chars'];
        } elseif (substr_count($email, '@') !== 1) {
            $errors['email'] = $messages['email_multiple_at'];
        } else {
            // Check for disposable email domains
            $disposableDomains = ['10minutemail.com', 'guerrillamail.com', 'tempmail.org', 'throwaway.email'];
            $domain = strtolower(substr(strrchr($email, '@'), 1));
            if (in_array($domain, $disposableDomains)) {
                $errors['email'] = $messages['email_disposable'];
            }
        }
        $data['email'] = $email;
    }

    // Enhanced phone validation (optional)
    if (!empty($data['phone'])) {
        $phone = preg_replace('/\s+/', '', trim($data['phone']));
        if (!preg_match('/^[\+]?[0-9\-\(\)\.]{10,20}$/', $phone)) {
            $errors['phone'] = $messages['phone_invalid'];
        } elseif (preg_match('/(\d)\1{8,}/', $phone)) {
            $errors['phone'] = $messages['phone_repeated'];
        }
        $data['phone'] = $phone;
    }

    // Enhanced length validation with min/max
    $lengthRules = [
        'firstName' => ['min' => 2, 'max' => 50],
        'lastName' => ['min' => 2, 'max' => 50],
        'subject' => ['min' => 0, 'max' => 200],  // Removed minimum length requirement
        'message' => ['min' => 10, 'max' => 2000]
    ];

    foreach ($lengthRules as $field => $rules) {
        if (!empty($data[$field])) {
            $length = strlen(trim($data[$field]));
            $fieldName = $fieldNames[$field] ?? ucfirst($field);

            if ($length < $rules['min']) {
                $errors[$field] = sprintf($messages['length_min'], $fieldName, $rules['min']);
            } elseif ($length > $rules['max']) {
                $errors[$field] = sprintf($messages['length_max'], $fieldName, $rules['max']);
            }
        }
    }

    // Content validation - check for suspicious patterns (more lenient for business messages)
    $suspiciousPatterns = [
        '/\b(?:viagra|cialis|casino|lottery|winner|click here|free money|make money|weight loss)\b/i',
        // Removed URL pattern check to allow business website mentions
        '/[<>{}]/i', // HTML/script tags
        '/\b(?:script|javascript|onclick|onload)\b/i', // Script content
        '/(\w)\1{15,}/i', // Increased to 15 repeated characters (was 10)
    ];

    foreach (['subject', 'message'] as $field) {
        if (!empty($data[$field])) {
            // Only check message field for spam patterns, not subject
            if ($field === 'message') {
                foreach ($suspiciousPatterns as $pattern) {
                    if (preg_match($pattern, $data[$field])) {
                        $errors['spam'] = $messages['spam_content'];
                        break 2;
                    }
                }
            }
        }
    }

    // Rate limiting check
    if (!empty($data['email'])) {
        $rateLimitFile = 'rate_limit_' . md5($data['email']) . '.tmp';
        if (file_exists($rateLimitFile)) {
            $lastSubmission = (int)file_get_contents($rateLimitFile);
            if (time() - $lastSubmission < 60) { // 1 minute
                $errors['rate_limit'] = $messages['rate_limit'];
            }
        }
        file_put_contents($rateLimitFile, time());
    }

    // Enhanced XSS protection and sanitization
    foreach (['firstName', 'lastName', 'subject', 'message'] as $field) {
        if (!empty($data[$field])) {
            $data[$field] = htmlspecialchars(strip_tags(trim($data[$field])), ENT_QUOTES, 'UTF-8');
            // Remove null bytes and control characters
            $data[$field] = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $data[$field]);
        }
    }

    return ['data' => $data, 'errors' => $errors];
}

/**
 * Validate SMTP configuration
 */
function validateSMTPConfig() {
    $errors = [];

    if (!defined('SMTP_HOST') || empty(SMTP_HOST)) {
        $errors[] = 'SMTP_HOST is not configured';
    }

    if (!defined('SMTP_PORT') || !is_numeric(SMTP_PORT) || SMTP_PORT < 1 || SMTP_PORT > 65535) {
        $errors[] = 'SMTP_PORT must be a valid port number (1-65535)';
    }

    if (!defined('SMTP_USERNAME') || empty(SMTP_USERNAME)) {
        $errors[] = 'SMTP_USERNAME is not configured';
    }

    if (!defined('SMTP_PASSWORD') || empty(SMTP_PASSWORD)) {
        $errors[] = 'SMTP_PASSWORD is not configured';
    }

    if (!defined('SMTP_ENCRYPTION') || !in_array(SMTP_ENCRYPTION, ['tls', 'ssl', ''])) {
        $errors[] = 'SMTP_ENCRYPTION must be "tls", "ssl", or empty';
    }

    if (!filter_var(SMTP_USERNAME, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'SMTP_USERNAME should be a valid email address';
    }

    return $errors;
}

/**
 * Send email using PHPMailer with SMTP
 */
function sendEmailSMTP($to, $subject, $htmlBody, $fromEmail, $fromName = '', $replyTo = '') {
    try {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = (SMTP_ENCRYPTION === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Set timeout
        $mail->Timeout = 30;

        // Recipients
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);

        // Add reply-to if provided
        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo);
        }

        // Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        // Create plain text version from HTML
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $htmlBody));

        // Send email
        $result = $mail->send();

        if ($result) {
            error_log("Email sent successfully to: $to");
        }

        return $result;

    } catch (Exception $e) {
        // Log detailed error for debugging
        error_log("PHPMailer Error sending to $to: " . $e->getMessage());
        error_log("PHPMailer Debug Info: " . $mail->ErrorInfo);

        // Additional debug information
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("SMTP Settings - Host: " . SMTP_HOST . ", Port: " . SMTP_PORT . ", User: " . SMTP_USERNAME);
        }

        return false;
    }
}

/**
 * Log form submission (optional)
 */
function logSubmission($data) {
    $logFile = 'contact-submissions.log';
    $logEntry = date('Y-m-d H:i:s') . ' - ' . $data['email'] . ' - ' . $data['subject'] . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}
?>
