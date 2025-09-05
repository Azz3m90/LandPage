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

  // Form submission handler
  contactForm.addEventListener('submit', function (e) {
    e.preventDefault();

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

    // Show loading state
    Swal.fire({
      title: messages.sending,
      text: messages.pleaseWait,
      icon: 'info',
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    // Collect form data
    const formData = new FormData(contactForm);
    const data = {};

    for (let [key, value] of formData.entries()) {
      data[key] = value;
    }

    // Add detected language to form data
    data.language = language;

    // Debug: Log collected form data
    console.log('Form data collected:', data);

    // Basic client-side validation
    const validationResult = validateFormData(data, messages);
    if (!validationResult.valid) {
      console.log('Validation failed:', validationResult.message);
      Swal.fire({
        title: messages.errorTitle,
        text: validationResult.message,
        icon: 'error',
        confirmButtonText: 'OK',
        confirmButtonColor: '#007bff',
      });
      return;
    }

    // Add Turnstile token if available
    let turnstileResponse = '';
    if (typeof turnstile !== 'undefined') {
      try {
        turnstileResponse = turnstile.getResponse();
        if (turnstileResponse) {
          data['cf-turnstile-response'] = turnstileResponse;
        }
      } catch (error) {
        console.warn('Turnstile error:', error);
      }
    }

    // Check if Turnstile completed (optional validation)
    if (!turnstileResponse) {
      console.warn('Turnstile verification not completed, but proceeding anyway for testing');
      // Temporarily commented out to debug form validation
      // Swal.fire({
      //   title: 'Security Verification Required',
      //   text: 'Please complete the security verification to submit your message.',
      //   icon: 'warning',
      //   confirmButtonText: 'OK',
      //   confirmButtonColor: '#007bff',
      // });
      // return;
    }

    submitForm(data, messages);
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
        sending: 'Envoi en cours...',
        pleaseWait: 'Veuillez patienter pendant que nous traitons votre demande',
        successTitle: 'Message envoyé !',
        successText: 'Merci pour votre message! Nous vous répondrons dans les 24-48 heures.',
        errorTitle: 'Erreur',
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
      },
      en: {
        sending: 'Sending...',
        pleaseWait: 'Please wait while we process your request',
        successTitle: 'Message Sent!',
        successText: 'Thank you for your message! We will respond within 24-48 hours.',
        errorTitle: 'Error',
        errorText: 'An error occurred while sending your message. Please try again.',
        firstNameRequired: 'First name is required',
        lastNameRequired: 'Last name is required',
        emailRequired: 'Email is required',
        emailInvalid: 'Please enter a valid email address',
        subjectRequired: 'Subject is required',
        messageRequired: 'Message is required',
        messageMinLength: 'Please provide a more detailed message (at least 10 characters)',
        gdprRequired: 'Please accept the privacy policy and terms of service',
      },
      nl: {
        sending: 'Verzenden...',
        pleaseWait: 'Even geduld terwijl we uw verzoek verwerken',
        successTitle: 'Bericht Verzonden!',
        successText: 'Bedankt voor uw bericht! Wij reageren binnen 24-48 uur.',
        errorTitle: 'Fout',
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

    // Check required fields
    if (!data.firstName || data.firstName.trim() === '') {
      return { valid: false, message: messages.firstNameRequired };
    }
    if (!data.lastName || data.lastName.trim() === '') {
      return { valid: false, message: messages.lastNameRequired };
    }
    if (!data.email || data.email.trim() === '') {
      return { valid: false, message: messages.emailRequired };
    }
    if (!data.subject || data.subject.trim() === '') {
      return { valid: false, message: messages.subjectRequired };
    }
    if (!data.message || data.message.trim() === '') {
      return { valid: false, message: messages.messageRequired };
    }

    // Check GDPR consent
    if (!data['gdpr-consent']) {
      console.log('GDPR consent not checked');
      const consentMessage =
        data.language === 'en'
          ? 'Please accept the privacy policy and terms of service'
          : data.language === 'fr'
            ? 'Veuillez accepter la politique de confidentialité et les conditions de service'
            : 'Accepteer het privacybeleid en de servicevoorwaarden';
      return {
        valid: false,
        message: consentMessage,
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
      field.addEventListener('blur', function () {
        validateField(this);
      });

      field.addEventListener('input', function () {
        // Remove error state when user starts typing
        const formGroup = this.closest('.form-group');
        if (formGroup && formGroup.classList.contains('has-error')) {
          formGroup.classList.remove('has-error');
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

    const counter = document.createElement('div');
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
