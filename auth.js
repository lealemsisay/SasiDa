/* ═══════════════════════════════════════════════
   SASIDA — auth.js
   Authentication helpers + login/register logic
═══════════════════════════════════════════════ */

(function() {
  'use strict';

  // ─── AUTH HELPERS ──────────────────────────────

  function isLoggedIn() {
    return !!localStorage.getItem('sasida_user');
  }

  function getCurrentUser() {
    try {
      return JSON.parse(localStorage.getItem('sasida_user'));
    } catch {
      return null;
    }
  }

  function isAdmin() {
    var user = getCurrentUser();
    return user && user.role === 'admin';
  }

  function loginUser(userData) {
    localStorage.setItem('sasida_user', JSON.stringify(userData));
  }

  function logoutUser() {
    localStorage.removeItem('sasida_user');
  }

  // Expose globally
  window.SASIDA = window.SASIDA || {};
  window.SASIDA.auth = {
    isLoggedIn: isLoggedIn,
    getCurrentUser: getCurrentUser,
    isAdmin: isAdmin,
    loginUser: loginUser,
    logoutUser: logoutUser
  };

  // ─── DOM refs ──────────────────────────────────
  var loader = document.getElementById('loader');
  var scrollTopBtn = document.getElementById('scrollTop');
  var header = document.getElementById('header');
  var themeToggle = document.getElementById('themeToggle');
  var hamburger = document.getElementById('hamburger');
  var mobileMenu = document.getElementById('mobileMenu');

  // ─── LOADER ────────────────────────────────────
  function hideLoader() {
    if (loader) loader.classList.add('hidden');
  }

  hideLoader();
  document.addEventListener('DOMContentLoaded', hideLoader);
  window.addEventListener('load', function() {
    setTimeout(hideLoader, 100);
  });
  setTimeout(hideLoader, 1500);

  // ─── THEME ─────────────────────────────────────
  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);    
    // Update icon visibility
    const sunIcon = document.querySelector('.theme-icon.sun');
    const moonIcon = document.querySelector('.theme-icon.moon');
    
    if (theme === 'light') {
      if (sunIcon) sunIcon.style.display = 'none';
      if (moonIcon) moonIcon.style.display = 'inline-block';
    } else {
      if (sunIcon) sunIcon.style.display = 'inline-block';
      if (moonIcon) moonIcon.style.display = 'none';
    }  }

  var storedTheme = localStorage.getItem('theme');
  if (storedTheme) applyTheme(storedTheme);
  else applyTheme('light');

  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      var current = document.documentElement.getAttribute('data-theme');
      var next = current === 'light' ? 'dark' : 'light';
      applyTheme(next);
    });
  }

  // ─── MOBILE MENU ──────────────────────────────
  if (hamburger && mobileMenu) {
    hamburger.addEventListener('click', function() {
      hamburger.classList.toggle('open');
      mobileMenu.classList.toggle('open');
    });
  }

  document.querySelectorAll('.mob-link').forEach(function(link) {
    link.addEventListener('click', function() {
      if (hamburger) hamburger.classList.remove('open');
      if (mobileMenu) mobileMenu.classList.remove('open');
    });
  });

  // ─── SCROLL-TO-TOP ────────────────────────────
  window.addEventListener('scroll', function() {
    if (scrollTopBtn) {
      if (window.scrollY > 300) scrollTopBtn.classList.add('show');
      else scrollTopBtn.classList.remove('show');
    }
  });

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', function() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ─── HEADER SHADOW ─────────────────────────────
  window.addEventListener('scroll', function() {
    if (header) {
      if (window.scrollY > 40) header.classList.add('scrolled');
      else header.classList.remove('scrolled');
    }
  });

  // ─── PASSWORD TOGGLE ──────────────────────────
  document.querySelectorAll('[id^="toggle"]').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      // Try to find the password input field
      var input = null;
      
      // Method 1: Previous element sibling
      if (this.previousElementSibling && this.previousElementSibling.type === 'password') {
        input = this.previousElementSibling;
      }
      
      // Method 2: Find input in parent
      if (!input && this.parentElement) {
        input = this.parentElement.querySelector('input[type="password"]');
      }
      
      // Method 3: Find input in parent's parent
      if (!input && this.parentElement && this.parentElement.parentElement) {
        input = this.parentElement.parentElement.querySelector('input[type="password"], input[type="text"]');
      }
      
      if (input) {
        if (input.type === 'password') {
          input.type = 'text';
          this.textContent = '🙈';
        } else {
          input.type = 'password';
          this.textContent = '👁';
        }
      }
    });
  });

  // Mock form submit handlers removed to allow native PHP form submissions.

  // ─── HELPER FUNCTIONS ────────────────────────
  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function showError(id, message) {
    var el = document.getElementById(id);
    if (el) {
      el.textContent = message;
      el.style.display = 'block';
    }
  }

  function clearErrors(ids) {
    ids.forEach(function(id) {
      var el = document.getElementById(id);
      if (el) {
        el.textContent = '';
        el.style.display = 'none';
      }
    });
  }

  function showSuccess(message) {
    var errorEl = document.getElementById('loginError') || document.getElementById('registerError');
    if (errorEl) {
      errorEl.style.color = '#52c48b';
      errorEl.textContent = message;
      errorEl.style.display = 'block';
    }
  }

  // ─── LOGO EXPAND (for center logo) ────────────
  var centerLogo = document.getElementById('centerLogoWrapper');
  if (centerLogo) {
    centerLogo.addEventListener('click', function(e) {
      e.stopPropagation();
      this.classList.toggle('logo-expanded');
    });
    document.addEventListener('click', function(e) {
      if (centerLogo && !centerLogo.contains(e.target)) {
        centerLogo.classList.remove('logo-expanded');
      }
    });
  }

  // ─── NEWSLETTER FORMS ─────────────────────────
  var newsletterForms = document.querySelectorAll('.newsletter-form');
  newsletterForms.forEach(function(form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var input = form.querySelector('input[type="email"]');
      var successMsg = form.parentElement.querySelector('.newsletter-success');
      if (input && input.value.trim() !== '') {
        if (successMsg) successMsg.classList.add('show');
        input.value = '';
        setTimeout(function() {
          if (successMsg) successMsg.classList.remove('show');
        }, 3000);
      }
    });
  });

  // ─── CLOSE MOBILE MENU ON RESIZE ──────────────
  window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
      if (hamburger) hamburger.classList.remove('open');
      if (mobileMenu) mobileMenu.classList.remove('open');
    }
  });

})();