document.addEventListener('DOMContentLoaded', () => {
  const loader = document.getElementById('loader');
  const header = document.querySelector('header');
  const footer = document.querySelector('footer');

  window.showLoader = function () {
    loader.classList.remove('hidden');
    document.body.classList.add('loader-active');
    if (header) header.style.visibility = 'hidden';
    if (footer) footer.style.visibility = 'hidden';
  };

  window.hideLoader = function () {
    loader.classList.add('hidden');
    document.body.classList.remove('loader-active');
    if (header) header.style.visibility = 'visible';
    if (footer) footer.style.visibility = 'visible';
  };

  showLoader();
  setTimeout(hideLoader, 3000);

  const contactForm = document.getElementById('contact-form');
  if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
      showLoader();
      setTimeout(() => {
        hideLoader();
      }, 2000);
    });
  }

  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', (e) => {
      showLoader();
      setTimeout(() => {
        hideLoader();
      }, 1000);
    });
  });

  //language switcher
  const desktopBtn = document.getElementById('desktopLangToggle');
  const desktopMenu = document.getElementById('desktopLangMenu');

  const mobileBtn = document.getElementById('mobileLangToggle');
  const mobileMenu = document.getElementById('mobileLangMenu');

  desktopBtn?.addEventListener('click', function (e) {
    e.stopPropagation();
    desktopMenu.classList.toggle('hidden');
    mobileMenu.classList.add('hidden');
  });

  mobileBtn?.addEventListener('click', function (e) {
    e.stopPropagation();
    mobileMenu.classList.toggle('hidden');
    desktopMenu.classList.add('hidden');
  });

  document.addEventListener('click', function (e) {
    if (!desktopBtn.contains(e.target) && !desktopMenu.contains(e.target)) {
      desktopMenu.classList.add('hidden');
    }

    if (!mobileBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
      mobileMenu.classList.add('hidden');
    }
  });
});

new Swiper('.clients-swiper', {
  loop: true,
  centeredSlides: true,
  keyboard: {
    enabled: true,
    onlyInViewport: true,
  },
  autoplay: {
    delay: 2500,
    disableOnInteraction: false,
  },
  slidesPerView: 1,
  spaceBetween: 24,
  breakpoints: {
    640: { slidesPerView: 2, centeredSlides: false },
    768: { slidesPerView: 3, centeredSlides: false },
    1024: { slidesPerView: 5, centeredSlides: false },
  },
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
});

// Initialize AOS
AOS.init({
  duration: 800,
  easing: 'ease-out-cubic',
  once: true,
  offset: 100,
});

document.addEventListener('DOMContentLoaded', function () {
  // Mobile menu toggle
  const menuToggle = document.getElementById('menu-toggle');
  const mobileMenu = document.getElementById('mobile-menu');
  if (menuToggle && mobileMenu) {
    menuToggle.addEventListener('click', function () {
      const isExpanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', !isExpanded);
      mobileMenu.classList.toggle('hidden');
      mobileMenu.classList.toggle('active');
      const icon = this.querySelector('i');
      icon.classList.toggle('fa-bars');
      icon.classList.toggle('fa-times');
    });
  }

  // Smooth scrolling with better mobile support
  document.addEventListener('click', function (e) {
    // Check if the clicked element is an anchor with a hash href
    if (e.target.closest('a[href^="#"]')) {
      e.preventDefault();
      const anchor = e.target.closest('a');
      const targetId = anchor.getAttribute('href');

      if (targetId === '#') return;

      const target = document.querySelector(targetId);
      if (!target) return;

      // Calculate the target position
      const headerOffset = document.querySelector('header')?.offsetHeight || 0;
      const elementPosition = target.getBoundingClientRect().top;
      const offsetPosition = elementPosition + window.pageYOffset - headerOffset - 20;

      // Smooth scroll to target
      window.scrollTo({
        top: offsetPosition,
        behavior: 'smooth',
      });

      // Update URL without jumping
      history.pushState(null, null, targetId);

      // Close mobile menu if open
      if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
        mobileMenu.classList.add('hidden');
        mobileMenu.classList.remove('active');
        if (menuToggle) {
          menuToggle.setAttribute('aria-expanded', 'false');
          const icon = menuToggle.querySelector('i');
          if (icon) {
            icon.classList.add('fa-bars');
            icon.classList.remove('fa-times');
          }
        }
      }
    }
  });

  // Back to top button
  const backToTop = document.getElementById('back-to-top');
  function updateBackToTop() {
    if (window.scrollY > 400) {
      backToTop.classList.add('visible');
    } else {
      backToTop.classList.remove('visible');
    }
  }
  backToTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
  window.addEventListener('scroll', updateBackToTop, { passive: true });
  updateBackToTop();

  // Form validation and submission
  const contactForm = document.getElementById('contact-form');
  if (contactForm) {
    contactForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      const data = Object.fromEntries(formData);

      // Basic client-side validation
      if (!data['first-name'] || !data['last-name'] || !data.email || !data.message) {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'Please fill out all required fields.',
          confirmButtonColor: '#2563eb',
        });
        return;
      }

      // Show loading
      Swal.fire({
        title: 'Sending...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });

      // Simulate API call
      await new Promise((resolve) => setTimeout(resolve, 1500));

      // Reset form and reCAPTCHA
      this.reset();
      if (typeof grecaptcha !== 'undefined') {
        grecaptcha.reset();
      }
      document.getElementById('submit-btn').disabled = true;

      // Success message
      Swal.fire({
        icon: 'success',
        title: 'Message Sent!',
        text: 'We’ll get back to you soon.',
        confirmButtonColor: '#2563eb',
      });
    });
  }

  // reCAPTCHA callbacks
  window.enableBtn = function () {
    document.getElementById('submit-btn').disabled = false;
  };
  window.disableBtn = function () {
    document.getElementById('submit-btn').disabled = true;
  };
});
document.addEventListener('DOMContentLoaded', function () {
  // Select all navigation links
  const navLinks = document.querySelectorAll('.nav-link');
  const mobileNavLinks = document.querySelectorAll('#mobile-menu a');

  // Combine both desktop and mobile nav links
  const allNavLinks = [...navLinks, ...mobileNavLinks];

  // Function to remove active class from all links
  function removeActiveClasses() {
    allNavLinks.forEach((link) => link.classList.remove('active'));
  }

  // Function to add active class to the current link
  function setActiveLink(sectionId) {
    removeActiveClasses();
    allNavLinks.forEach((link) => {
      if (link.getAttribute('href') === `#${sectionId}`) {
        link.classList.add('active');
      }
    });
  }

  // IntersectionObserver to detect visible sections
  const sections = document.querySelectorAll('section[id]');
  const observerOptions = {
    root: null,
    rootMargin: '-100px 0px -50% 0px', // Adjust to trigger when section is in view
    threshold: 0.1, // Trigger when 10% of the section is visible
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const sectionId = entry.target.getAttribute('id');
        setActiveLink(sectionId);
      }
    });
  }, observerOptions);

  // Observe each section
  sections.forEach((section) => observer.observe(section));

  // Handle initial page load with hash in URL
  const hash = window.location.hash;
  if (hash) {
    const sectionId = hash.replace('#', '');
    setActiveLink(sectionId);
  }

  // Update active link on click
  allNavLinks.forEach((link) => {
    link.addEventListener('click', (e) => {
      const sectionId = link.getAttribute('href').replace('#', '');
      setActiveLink(sectionId);
    });
  });
});

const langToggle = document.getElementById('lang-toggle');
const langMenu = document.getElementById('lang-menu');
const langToggleMobile = document.getElementById('lang-toggle-mobile');
const langMenuMobile = document.getElementById('lang-menu-mobile');

langToggle?.addEventListener('click', () => {
  langMenu.classList.toggle('hidden');
});

langToggleMobile?.addEventListener('click', () => {
  langMenuMobile.classList.toggle('hidden');
});

window.addEventListener('click', (e) => {
  if (!langToggle?.contains(e.target) && !langMenu?.contains(e.target)) {
    langMenu?.classList.add('hidden');
  }

  if (!langToggleMobile?.contains(e.target) && !langMenuMobile?.contains(e.target)) {
    langMenuMobile?.classList.add('hidden');
  }
});
