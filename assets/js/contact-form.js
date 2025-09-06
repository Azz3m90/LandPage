/**
 * FastCaisse Contact Form Handler with SweetAlert
 *
 * This script handles:
 * - Form validation
 * - AJAX form submission
 * - SweetAlert responses
 * - Cloudflare Turnstile integration
 */

document.addEventListener('DOMContentLoaded', function () {
  const contactForm = document.getElementById('contact-form');

  if (!contactForm) {
    console.warn('Contact form not found');
    return;
  }

  // Rate limiting variables
  let lastSubmissionTime = localStorage.getItem('lastFormSubmission')
    ? parseInt(localStorage.getItem('lastFormSubmission'))
    : 0;
  const RATE_LIMIT_DURATION = 60000; // 1 minute in milliseconds

  // Security flag to track if Turnstile is loaded
  let turnstileLoaded = false;
  let turnstileWidgetId = null;
  let turnstileVerified = false;

  // Get submit button
  const submitButton = contactForm.querySelector('button[type="submit"]');

  // Check if Turnstile is available on page load
  if (typeof turnstile !== 'undefined') {
    turnstileLoaded = true;
  } else {
    // Disable submit button if Turnstile is not loaded
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.style.opacity = '0.6';
      submitButton.style.cursor = 'not-allowed';
      submitButton.title = 'Security verification loading...';
    }
  }

  // Monitor for Turnstile loading
  window.addEventListener('load', function () {
    setTimeout(function () {
      if (typeof turnstile !== 'undefined') {
        turnstileLoaded = true;
        if (submitButton) {
          submitButton.disabled = false;
          submitButton.style.opacity = '1';
          submitButton.style.cursor = 'pointer';
          submitButton.title = '';
        }
      }
    }, 1000);
  });

  // Form submission handler
  contactForm.addEventListener('submit', function (e) {
    e.preventDefault();

    // SECURITY: Immediate check for Turnstile
    if (!turnstileLoaded || typeof turnstile === 'undefined') {
      console.error('[SECURITY] Turnstile not loaded - blocking submission');
      Swal.fire({
        title: 'Security Error',
        text: 'Security verification system not loaded. Please refresh the page and try again.',
        icon: 'error',
        confirmButtonColor: '#dc3545',
      });
      return;
    }

    // Detect language from current page URL
    const currentUrl = window.location.pathname;
    let language = 'fr'; // Default to French

    if (currentUrl.includes('-en.html')) {
      language = 'en';
    } else if (currentUrl.includes('-nl.html')) {
      language = 'nl';
    }

    // Override for test page
    if (typeof window.forceLanguage === 'function') {
      language = window.forceLanguage();
    }

    // Get localized messages
    const messages = getLocalizedMessages(language);

    // Check rate limiting FIRST before any processing
    const now = Date.now();
    const timeSinceLastSubmission = now - lastSubmissionTime;

    if (timeSinceLastSubmission < RATE_LIMIT_DURATION) {
      const remainingTime = Math.ceil((RATE_LIMIT_DURATION - timeSinceLastSubmission) / 1000);

      // Get rate limit message based on language
      const rateLimitMessage =
        language === 'en'
          ? `Please wait ${remainingTime} seconds before submitting again.`
          : language === 'nl'
            ? `Wacht alstublieft ${remainingTime} seconden voordat u opnieuw verzendt.`
            : `Veuillez attendre ${remainingTime} secondes avant de soumettre à nouveau.`;

      const rateLimitTitle =
        language === 'en'
          ? 'Please Wait'
          : language === 'nl'
            ? 'Even geduld'
            : 'Veuillez patienter';

      Swal.fire({
        title: rateLimitTitle,
        text: rateLimitMessage,
        icon: 'info',
        confirmButtonText: 'OK',
        confirmButtonColor: '#007bff',
        timer: remainingTime * 1000,
        timerProgressBar: true,
        didOpen: () => {
          // Update the message every second
          const content = Swal.getHtmlContainer();
          const interval = setInterval(() => {
            const currentRemaining = Math.ceil(
              (RATE_LIMIT_DURATION - (Date.now() - lastSubmissionTime)) / 1000
            );
            if (currentRemaining > 0) {
              const updatedMessage =
                language === 'en'
                  ? `Please wait ${currentRemaining} seconds before submitting again.`
                  : language === 'nl'
                    ? `Wacht alstublieft ${currentRemaining} seconden voordat u opnieuw verzendt.`
                    : `Veuillez attendre ${currentRemaining} secondes avant de soumettre à nouveau.`;
              content.querySelector('.swal2-html-container').textContent = updatedMessage;
            } else {
              clearInterval(interval);
            }
          }, 1000);

          // Clean up interval when alert closes
          Swal.getConfirmButton().addEventListener('click', () => clearInterval(interval));
        },
      });
      return;
    }

    // Immediately show processing message
    Swal.fire({
      title: messages.processing || 'Processing...',
      text: messages.validating || 'Validating your information...',
      icon: 'info',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    // Small delay to let the loading message appear
    setTimeout(() => {
      // Collect form data
      const formData = new FormData(contactForm);
      const data = {};

      for (let [key, value] of formData.entries()) {
        data[key] = value;
      }

      // Explicitly check for GDPR consent checkbox (checkboxes only appear in FormData when checked)
      const gdprCheckbox = contactForm.querySelector('#gdpr-consent');
      if (gdprCheckbox) {
        data['gdpr-consent'] = gdprCheckbox.checked ? 'on' : '';
      }

      // Add detected language to form data
      data.language = language;

      // Debug: Log collected form data
      console.log('Form data collected:', data);

      // Basic client-side validation
      const validationResult = validateFormData(data, messages);
      if (!validationResult.valid) {
        console.log('Validation failed:', validationResult.message);

        // Show validation error without "Oops"
        const errorMessage =
          validationResult.message ||
          messages.errorText ||
          'Please complete all required fields correctly.';

        Swal.fire({
          title: messages.validationErrorTitle || 'Missing Information',
          text: errorMessage,
          icon: 'warning',
          confirmButtonText: messages.tryAgain || 'Try Again',
          confirmButtonColor: '#007bff',
        });
        return;
      }

      console.log('Validation passed, proceeding to submit...');

      // Update loading message to show sending
      Swal.fire({
        title: messages.sending || 'Sending...',
        text: messages.pleaseWait || 'Please wait while we send your message',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      // MANDATORY: Get Turnstile token - no submission without it
      let turnstileResponse = '';
      let turnstileValid = false;

      if (typeof turnstile !== 'undefined') {
        try {
          turnstileResponse = turnstile.getResponse();
          // Double check: response must exist AND not be empty string
          if (turnstileResponse && turnstileResponse.length > 0) {
            data['cf-turnstile-response'] = turnstileResponse;
            turnstileValid = true;
            console.log('[SECURITY] Turnstile token obtained successfully');
          } else {
            console.error('[SECURITY] Turnstile response is empty');
          }
        } catch (error) {
          console.error('[SECURITY] Turnstile error:', error);
          turnstileValid = false;
        }
      } else {
        console.error('[SECURITY] Turnstile object not available');
        turnstileValid = false;
      }

      // ABSOLUTE BLOCK: No submission without valid Turnstile
      if (!turnstileValid || !turnstileResponse || turnstileResponse.length === 0) {
        console.error('[SECURITY BLOCK] Form submission prevented - No valid captcha');

        // Show security verification required message
        const securityMessage =
          language === 'en'
            ? 'Please complete the security verification to submit your message.'
            : language === 'nl'
              ? 'Voltooi de beveiligingsverificatie om uw bericht te verzenden.'
              : 'Veuillez compléter la vérification de sécurité pour envoyer votre message.';

        const securityTitle =
          language === 'en'
            ? 'Security Verification Required'
            : language === 'nl'
              ? 'Beveiligingsverificatie vereist'
              : 'Vérification de sécurité requise';

        Swal.fire({
          title: securityTitle,
          text: securityMessage,
          icon: 'warning',
          confirmButtonText: messages.tryAgain || 'Try Again',
          confirmButtonColor: '#007bff',
        });
        return;
      }

      // Submit the form with a small delay to show the sending message
      setTimeout(() => {
        submitForm(data, messages);
      }, 100);
    }, 100); // End of first setTimeout
  });

  /**
   * Submit form data to PHP handler
   */
  function submitForm(data, messages) {
    fetch('contact-handler.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    })
      .then((response) => response.json())
      .then((result) => {
        handleResponse(result, messages);
      })
      .catch((error) => {
        console.error('Error:', error);
        Swal.fire({
          title: messages.errorTitle,
          text: messages.networkError,
          icon: 'error',
          confirmButtonText: 'Try Again',
          confirmButtonColor: '#dc3545',
        });
      });
  }

  /**
   * Handle server response
   */
  function handleResponse(result, messages) {
    if (result.success) {
      // Save submission time for rate limiting
      lastSubmissionTime = Date.now();
      localStorage.setItem('lastFormSubmission', lastSubmissionTime.toString());
      console.log('Form submitted successfully, rate limit set for 1 minute');

      // Success - show confirmation and reset form
      Swal.fire({
        title: messages.successTitle,
        text: result.message,
        icon: 'success',
        confirmButtonText: 'Great!',
        confirmButtonColor: '#28a745',
        showClass: {
          popup: 'animate__animated animate__fadeInDown',
        },
        hideClass: {
          popup: 'animate__animated animate__fadeOutUp',
        },
      }).then(() => {
        // Reset form after success
        contactForm.reset();

        // Reset Turnstile widget
        if (typeof turnstile !== 'undefined') {
          try {
            turnstile.reset();
          } catch (error) {
            console.warn('Turnstile reset error:', error);
          }
        }

        // Remove any validation classes
        const formGroups = contactForm.querySelectorAll('.form-group');
        formGroups.forEach((group) => {
          group.classList.remove('has-error', 'has-success');
        });
      });

      // Optional: Track success event for analytics
      if (typeof gtag !== 'undefined') {
        gtag('event', 'form_submit', {
          event_category: 'contact',
          event_label: 'success',
        });
      }
    } else {
      // Error - show error message
      Swal.fire({
        title: messages.errorTitle,
        text: result.message || messages.errorText,
        icon: 'error',
        confirmButtonText: 'Try Again',
        confirmButtonColor: '#dc3545',
        footer: 'If the problem persists, please contact us directly at contact@fastcaisse.be',
      });

      // Optional: Track error event for analytics
      if (typeof gtag !== 'undefined') {
        gtag('event', 'form_error', {
          event_category: 'contact',
          event_label: result.message,
        });
      }
    }
  }

  /**
   * Get localized messages for JavaScript UI
   */
  function getLocalizedMessages(language) {
    const messages = {
      fr: {
        processing: 'Traitement en cours...',
        validating: 'Vérification de vos informations...',
        sending: 'Envoi en cours...',
        pleaseWait: 'Veuillez patienter pendant que nous envoyons votre message',
        successTitle: 'Message envoyé !',
        successText: 'Merci pour votre message! Nous vous répondrons dans les 24-48 heures.',
        errorTitle: 'Erreur',
        validationErrorTitle: 'Informations manquantes',
        tryAgain: 'Réessayer',
        errorText:
          "Une erreur s'est produite lors de l'envoi de votre message. Veuillez réessayer.",
        firstNameRequired: 'Le prénom est requis',
        lastNameRequired: 'Le nom est requis',
        emailRequired: "L'email est requis",
        emailInvalid: 'Veuillez saisir une adresse email valide',
        subjectRequired: 'Le sujet est requis',
        messageRequired: 'Le message est requis',
        messageMinLength: 'Veuillez fournir un message plus détaillé (au moins 10 caractères)',
        gdprRequired:
          'Veuillez accepter la politique de confidentialité et les conditions de service',
        networkError:
          'Erreur de connexion. Veuillez vérifier votre connexion Internet et réessayer.',
      },
      en: {
        processing: 'Processing...',
        validating: 'Validating your information...',
        sending: 'Sending...',
        pleaseWait: 'Please wait while we send your message',
        successTitle: 'Message Sent!',
        successText: 'Thank you for your message! We will respond within 24-48 hours.',
        errorTitle: 'Error',
        validationErrorTitle: 'Missing Information',
        tryAgain: 'Try Again',
        errorText: 'An error occurred while sending your message. Please try again.',
        firstNameRequired: 'First name is required',
        lastNameRequired: 'Last name is required',
        emailRequired: 'Email is required',
        emailInvalid: 'Please enter a valid email address',
        subjectRequired: 'Subject is required',
        messageRequired: 'Message is required',
        messageMinLength: 'Please provide a more detailed message (at least 10 characters)',
        gdprRequired: 'Please accept the privacy policy and terms of service',
        networkError: 'Connection error. Please check your internet connection and try again.',
      },
      nl: {
        processing: 'Verwerken...',
        validating: 'Uw informatie valideren...',
        sending: 'Verzenden...',
        pleaseWait: 'Even geduld terwijl we uw bericht verzenden',
        successTitle: 'Bericht Verzonden!',
        successText: 'Bedankt voor uw bericht! Wij reageren binnen 24-48 uur.',
        errorTitle: 'Fout',
        validationErrorTitle: 'Ontbrekende informatie',
        tryAgain: 'Probeer opnieuw',
        errorText:
          'Er is een fout opgetreden bij het verzenden van uw bericht. Probeer het opnieuw.',
        firstNameRequired: 'Voornaam is vereist',
        lastNameRequired: 'Achternaam is vereist',
        emailRequired: 'E-mail is vereist',
        emailInvalid: 'Voer een geldig e-mailadres in',
        subjectRequired: 'Onderwerp is vereist',
        messageRequired: 'Bericht is vereist',
        messageMinLength: 'Geef een meer gedetailleerd bericht (minimaal 10 tekens)',
        gdprRequired: 'Accepteer het privacybeleid en de servicevoorwaarden',
        networkError: 'Verbindingsfout. Controleer uw internetverbinding en probeer opnieuw.',
      },
    };

    return messages[language] || messages['fr']; // Default to French if language not found
  }

  /**
   * Client-side form validation with multilingual support
   */
  function validateFormData(data, messages) {
    // Debug: Log what we're validating
    console.log('Validating data:', data);
    console.log('Available messages:', messages);

    // Check required fields
    if (!data.firstName || data.firstName.trim() === '') {
      console.log('Validation failed: firstName is empty');
      return { valid: false, message: messages.firstNameRequired || 'First name is required' };
    }
    if (!data.lastName || data.lastName.trim() === '') {
      console.log('Validation failed: lastName is empty');
      return { valid: false, message: messages.lastNameRequired || 'Last name is required' };
    }
    if (!data.email || data.email.trim() === '') {
      console.log('Validation failed: email is empty');
      return { valid: false, message: messages.emailRequired || 'Email is required' };
    }
    if (!data.subject || data.subject.trim() === '') {
      console.log('Validation failed: subject is empty');
      return { valid: false, message: messages.subjectRequired || 'Subject is required' };
    }
    if (!data.message || data.message.trim() === '') {
      console.log('Validation failed: message is empty');
      return { valid: false, message: messages.messageRequired || 'Message is required' };
    }

    // Check GDPR consent
    if (!data['gdpr-consent'] || data['gdpr-consent'] === '') {
      console.log('GDPR consent not checked:', data['gdpr-consent']);
      return {
        valid: false,
        message: messages.gdprRequired || 'Please accept the privacy policy',
      };
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(data.email)) {
      return {
        valid: false,
        message: messages.emailInvalid,
      };
    }

    // Message length validation
    if (data.message.length < 10) {
      return {
        valid: false,
        message: messages.messageMinLength,
      };
    }

    if (data.message.length > 2000) {
      return {
        valid: false,
        message: 'Message is too long (maximum 2000 characters)',
      };
    }

    return { valid: true };
  }

  /**
   * Real-time field validation
   */
  function setupRealTimeValidation() {
    const fields = contactForm.querySelectorAll('input, textarea, select');

    fields.forEach((field) => {
      // Only validate on blur if the field has been touched (has value or was focused)
      let hasBeenTouched = false;

      field.addEventListener('focus', function () {
        hasBeenTouched = true;
      });

      field.addEventListener('blur', function () {
        // Only validate if field has been touched and has a value or is required
        if (hasBeenTouched && (this.value.trim() || this.hasAttribute('required'))) {
          validateField(this);
        }
      });

      field.addEventListener('input', function () {
        // Remove error state when user starts typing
        const formGroup = this.closest('.form-group');
        if (formGroup && formGroup.classList.contains('has-error')) {
          formGroup.classList.remove('has-error');
          // Remove error message
          const errorMsg = formGroup.querySelector('.error-message');
          if (errorMsg) {
            errorMsg.remove();
          }
        }
      });
    });
  }

  /**
   * Validate individual field
   */
  function validateField(field) {
    const formGroup = field.closest('.form-group');
    if (!formGroup) return;

    let isValid = true;
    let errorMessage = '';

    // Check if required field is empty
    if (field.hasAttribute('required') && !field.value.trim()) {
      isValid = false;
      errorMessage = 'This field is required';
    }

    // Email validation
    if (field.type === 'email' && field.value.trim()) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(field.value)) {
        isValid = false;
        errorMessage = 'Please enter a valid email address';
      }
    }

    // Update form group appearance
    if (isValid) {
      formGroup.classList.remove('has-error');
      formGroup.classList.add('has-success');
    } else {
      formGroup.classList.remove('has-success');
      formGroup.classList.add('has-error');

      // Show error message
      let errorElement = formGroup.querySelector('.error-message');
      if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        formGroup.appendChild(errorElement);
      }
      errorElement.textContent = errorMessage;
    }
  }

  /**
   * Character counter for message field
   */
  function setupCharacterCounter() {
    const messageField = contactForm.querySelector('#message');
    if (!messageField) return;

    // Check if counter already exists
    let counter = messageField.parentNode.querySelector('.character-counter');
    if (counter) return; // Counter already exists, don't create another

    counter = document.createElement('div');
    counter.className = 'character-counter';
    counter.style.cssText = 'text-align: right; margin-top: 5px; font-size: 12px; color: #666;';

    messageField.parentNode.appendChild(counter);

    function updateCounter() {
      const length = messageField.value.length;
      const maxLength = 2000;
      counter.textContent = `${length}/${maxLength} characters`;

      if (length > maxLength) {
        counter.style.color = '#dc3545';
      } else if (length > maxLength * 0.8) {
        counter.style.color = '#ffc107';
      } else {
        counter.style.color = '#666';
      }
    }

    messageField.addEventListener('input', updateCounter);
    updateCounter(); // Initial count
  }

  // Initialize features
  setupRealTimeValidation();
  setupCharacterCounter();

  // Auto-resize textarea
  const textareas = contactForm.querySelectorAll('textarea');
  textareas.forEach((textarea) => {
    textarea.addEventListener('input', function () {
      this.style.height = 'auto';
      this.style.height = this.scrollHeight + 'px';
    });
  });
});

/**
 * SweetAlert2 Configuration
 */
if (typeof Swal !== 'undefined') {
  // Set default SweetAlert configuration
  const swalWithBootstrapButtons = Swal.mixin({
    customClass: {
      confirmButton: 'btn btn-success mx-2',
      cancelButton: 'btn btn-danger mx-2',
    },
    buttonsStyling: false,
  });

  // Make it globally available
  window.swalWithBootstrapButtons = swalWithBootstrapButtons;
}

/**
 * Utility function to show quick messages
 */
window.showContactMessage = function (type, title, message) {
  const icons = {
    success: 'success',
    error: 'error',
    warning: 'warning',
    info: 'info',
  };

  const colors = {
    success: '#28a745',
    error: '#dc3545',
    warning: '#ffc107',
    info: '#007bff',
  };

  Swal.fire({
    title: title,
    text: message,
    icon: icons[type] || 'info',
    confirmButtonColor: colors[type] || '#007bff',
    timer: type === 'success' ? 3000 : undefined,
    timerProgressBar: type === 'success',
    showClass: {
      popup: 'animate__animated animate__fadeInDown',
    },
    hideClass: {
      popup: 'animate__animated animate__fadeOutUp',
    },
  });
};

/**
 * Turnstile callback functions - these are called by the Turnstile widget
 */
window.enableBtn = function () {
  console.log('Turnstile: enableBtn called');
  const submitBtn = document.getElementById('submit-btn');
  if (submitBtn) {
    submitBtn.disabled = false;
    console.log('Submit button enabled');
  }
};

window.disableBtn = function () {
  console.log('Turnstile: disableBtn called');
  const submitBtn = document.getElementById('submit-btn');
  if (submitBtn) {
    submitBtn.disabled = true;
    console.log('Submit button disabled');
  }
};
