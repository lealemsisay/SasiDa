/* ═══════════════════════════════════════════════
   SASIDA — script.js
   Core UI + Data-driven homepage (reads from localStorage)
═══════════════════════════════════════════════ */

(function() {
  'use strict';

  // ─── DOM refs ──────────────────────────────────
  var loader = document.getElementById('loader');
  var scrollTopBtn = document.getElementById('scrollTop');
  var header = document.getElementById('header');
  var themeToggle = document.getElementById('themeToggle');
  var hamburger = document.getElementById('hamburger');
  var mobileMenu = document.getElementById('mobileMenu');
  var logoWrapper = document.getElementById('logoWrapper');
  var navList = document.getElementById('navLinks');

  // ─── LOADER ────────────────────────────────────
  function hideLoader() {
    if (loader) loader.classList.add('hidden');
  }

  window.addEventListener('load', function() {
    setTimeout(hideLoader, 800);
    setTimeout(hideLoader, 100);
    setTimeout(function() {
      initNavigation();
      initScrollSpy();
      setActiveNavFromHash();
      renderAll();
      updateHeader();
    }, 150);
  });
  setTimeout(hideLoader, 2000);

  // ─── THEME ─────────────────────────────────────
  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
  }

  var storedTheme = localStorage.getItem('theme');
  if (storedTheme) applyTheme(storedTheme);
  else applyTheme('dark');

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

  // ─── SCROLL REVEAL ────────────────────────────
  var revealElements = document.querySelectorAll('.reveal');
  var revealObserver = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

  revealElements.forEach(function(el) { revealObserver.observe(el); });

  // ─── LOGO EXPAND ──────────────────────────────
  if (logoWrapper) {
    logoWrapper.addEventListener('click', function(e) {
      e.stopPropagation();
      this.classList.toggle('logo-expanded');
    });
    document.addEventListener('click', function(e) {
      if (!logoWrapper.contains(e.target)) {
        logoWrapper.classList.remove('logo-expanded');
      }
    });
  }

  // ─── GET DATA FROM LOCALSTORAGE ────────────────
  function getCategories() {
    return JSON.parse(localStorage.getItem('sasida_categories') || '[]');
  }

  function getProducts() {
    return JSON.parse(localStorage.getItem('sasida_products') || '[]');
  }

  function getContent() {
    return JSON.parse(localStorage.getItem('sasida_content') || '{}');
  }

  function getHero() {
    var content = getContent();
    return content.hero || {
      eyebrow: 'Since 2025 · Premium Lifestyle',
      title: 'Discover Your<br /><em>Signature Style</em>',
      subtitle: 'From cosmetics to fashion, find everything you need<br />to elevate your everyday.',
      primaryCta: 'Shop Now',
      primaryLink: 'shop.php',
      secondaryCta: 'Explore Categories',
      secondaryLink: 'shop.php'
    };
  }

  function getAbout() {
    var content = getContent();
    return content.about || {
      tag: 'Our Story',
      title: 'Elevating Everyday<br /><em>Lifestyle</em>',
      desc1: 'Founded in 2025, SASIDA was born from a passion for curating the finest products across beauty, fashion, and wellness. We believe that everyone deserves access to premium quality at fair prices.',
      desc2: 'Our team travels the world to bring you the best – from artisan perfumes to sustainable fashion. Every product is handpicked to ensure it meets our high standards.',
      rating: '4.9'
    };
  }

  function getContact() {
    var content = getContent();
    return content.contact || {
      phone: '+251 911 234 567',
      email: 'info@sasida.com',
      whatsapp: '+251 911 234 567',
      instagram: 'https://instagram.com/sasida_shop',
      instagramText: '@sasida_shop',
      tiktok: 'https://tiktok.com/@sasida_shop',
      tiktokText: '@sasida_shop',
      address: 'Addis Ababa, Ethiopia',
      hours: 'Mon–Sat 9am – 9pm'
    };
  }

  function getPromotions() {
    var content = getContent();
    return content.promotions || [
      { id: 1, title: 'Summer Sale', subtitle: 'Up to 40% off selected items', cta: 'Shop Sale', link: 'shop.php', bg: 'linear-gradient(135deg, #1a1a2e, #16213e)' },
      { id: 2, title: 'New Arrivals', subtitle: 'Fresh styles just landed', cta: 'Explore', link: 'shop.php', bg: 'linear-gradient(135deg, #2d1b1b, #3d2b2b)' }
    ];
  }

  function getTestimonials() {
    var content = getContent();
    return content.testimonials || [
      { id: 1, name: 'Amara Chen', initials: 'AC', avatar: 'av1', rating: 5, text: 'SASIDA offers the best selection of premium products. I love the quality and the fast delivery.', location: 'New York, USA' },
      { id: 2, name: 'Lena Kovacs', initials: 'LK', avatar: 'av2', rating: 5, text: 'The cosmetics range is incredible – my skin has never looked better. Highly recommend!', location: 'Budapest, Hungary' },
      { id: 3, name: 'David Okafor', initials: 'DO', avatar: 'av3', rating: 5, text: 'Great customer service and amazing value for money. I keep coming back for more.', location: 'Lagos, Nigeria' }
    ];
  }

  // ─── RENDER FUNCTIONS ──────────────────────────

  function renderHero() {
    var hero = getHero();
    var el = document.querySelector('.hero');
    if (!el) return;
    var eyebrow = el.querySelector('.hero-eyebrow');
    var title = el.querySelector('.hero-heading');
    var sub = el.querySelector('.hero-sub');
    var primary = el.querySelector('.btn-primary');
    var secondary = el.querySelector('.btn-ghost');
    if (eyebrow) eyebrow.textContent = hero.eyebrow;
    if (title) title.innerHTML = hero.title;
    if (sub) sub.innerHTML = hero.subtitle;
    if (primary) { primary.textContent = hero.primaryCta; primary.href = hero.primaryLink; }
    if (secondary) { secondary.textContent = hero.secondaryCta; secondary.href = hero.secondaryLink; }
  }

  function renderCategories() {
    var grid = document.querySelector('.categories-grid');
    if (!grid) return;
    var categories = getCategories();
    var visible = categories.filter(function(c) { return c.visible !== false; });
    var html = '';
    visible.forEach(function(cat) {
      html += '<div class="category-card reveal" style="background:' + cat.color + ';">' +
        '<div class="category-icon">' + cat.icon + '</div>' +
        '<h4>' + cat.name + '</h4>' +
        '</div>';
    });
    grid.innerHTML = html;
  }

  function renderProducts(containerSelector, productList) {
    var container = document.querySelector(containerSelector);
    if (!container) return;
    var gradients = [
      'linear-gradient(135deg,#f5d6c6,#f0b8a0)',
      'linear-gradient(135deg,#c9d4d1,#a3b8b3)',
      'linear-gradient(135deg,#d4c9b0,#b8a88c)',
      'linear-gradient(135deg,#d5c9d4,#b8a8b0)',
      'linear-gradient(135deg,#e8d5c4,#d4b8a4)',
      'linear-gradient(135deg,#b5c9d6,#8fb0c4)',
      'linear-gradient(135deg,#c9d4b8,#b0c0a0)',
      'linear-gradient(135deg,#d4c8d4,#b8a8b8)'
    ];
    var html = '';
    var visibleProducts = productList.filter(function(p) { return p.status !== 'hidden' && p.status !== 'archived'; });
    visibleProducts.forEach(function(p, index) {
      var bg = gradients[index % gradients.length];
      var oldPriceHtml = p.oldPrice ? '<span class="old-price">ETB ' + p.oldPrice.toFixed(2) + '</span>' : '';
      var badgeHtml = p.badge ? '<span class="product-badge">' + p.badge + '</span>' : '';
      var imageHtml = p.image && p.image.startsWith('http')
        ? '<img src="' + p.image + '" alt="' + p.name + '" style="width:100%;height:100%;object-fit:cover;" />'
        : '<span class="product-placeholder" style="font-size:3.5rem;">' + (p.image || '📦') + '</span>';
      var stockBadge = (p.status === 'outofstock' || p.inStock === false)
        ? '<span class="product-badge" style="background:#e05757;color:#fff;">Out of Stock</span>'
        : '';
      html += '<div class="product-card reveal">' +
        '<div class="product-img-wrap" style="background:' + bg + ';">' +
        imageHtml +
        badgeHtml +
        stockBadge +
        '</div>' +
        '<div class="product-body">' +
        '<h4>' + p.name + '</h4>' +
        '<div class="product-price">' + oldPriceHtml + ' ETB ' + p.price.toFixed(2) + '</div>' +
        '</div>' +
        '</div>';
    });
    container.innerHTML = html;
  }

  function renderPromotions() {
    var container = document.querySelector('.promotions-grid');
    if (!container) return;
    var promotions = getPromotions();
    var html = '';
    promotions.forEach(function(promo) {
      html += '<div class="promo-card reveal" style="background:' + promo.bg + ';">' +
        '<div class="promo-content">' +
        '<span class="promo-tag">' + promo.title + '</span>' +
        '<p>' + promo.subtitle + '</p>' +
        '<a href="' + promo.link + '" class="btn btn-primary">' + promo.cta + '</a>' +
        '</div>' +
        '</div>';
    });
    container.innerHTML = html;
  }

  function renderTestimonials() {
    var container = document.getElementById('testimonialTrack');
    if (!container) return;
    var testimonials = getTestimonials();
    var html = '';
    testimonials.forEach(function(t) {
      var stars = '★'.repeat(t.rating) + '☆'.repeat(5 - t.rating);
      html += '<div class="testimonial-card reveal">' +
        '<div class="stars">' + stars + '</div>' +
        '<p>"' + t.text + '"</p>' +
        '<div class="testimonial-author">' +
        '<div class="author-avatar ' + t.avatar + '">' + t.initials + '</div>' +
        '<div>' +
        '<strong>' + t.name + '</strong>' +
        '<span>' + t.location + '</span>' +
        '</div>' +
        '</div>' +
        '</div>';
    });
    container.innerHTML = html;
  }

  function renderContact() {
    var contact = getContact();
    var textElements = ['contactPhone', 'contactEmail', 'contactWhatsApp', 'contactAddress', 'contactHours'];
    textElements.forEach(function(id) {
      var el = document.getElementById(id);
      if (el) {
        var key = el.dataset.contact;
        if (key && contact[key] !== undefined) {
          el.textContent = contact[key];
        }
      }
    });
    var instagramLink = document.getElementById('contactInstagram');
    if (instagramLink) {
      instagramLink.href = contact.instagram;
      instagramLink.textContent = contact.instagramText;
    }
    var tiktokLink = document.getElementById('contactTikTok');
    if (tiktokLink) {
      tiktokLink.href = contact.tiktok;
      tiktokLink.textContent = contact.tiktokText;
    }
  }

  function renderAbout() {
    var about = getAbout();
    var el = function(id) { return document.getElementById(id); };
    if (el('aboutTag')) el('aboutTag').textContent = about.tag;
    if (el('aboutTitle')) el('aboutTitle').innerHTML = about.title;
    if (el('aboutDesc1')) el('aboutDesc1').textContent = about.desc1;
    if (el('aboutDesc2')) el('aboutDesc2').textContent = about.desc2;
    if (el('aboutRating')) el('aboutRating').textContent = about.rating;
  }

  function renderAll() {
    renderHero();
    renderCategories();
    var products = getProducts();
    var featured = products.slice(0, 4);
    var bestsellers = products.filter(function(p) { return p.badge === 'Best Seller'; });
    var newArrivals = products.filter(function(p) { return p.badge === 'New'; });
    renderProducts('#featuredProducts', featured);
    renderProducts('#bestsellerProducts', bestsellers);
    renderProducts('#newArrivalProducts', newArrivals);
    renderPromotions();
    renderTestimonials();
    renderContact();
    renderAbout();
  }

  function updateHeader() {
    if (window.SASIDA && window.SASIDA.auth && window.SASIDA.auth.updateHeader) {
      window.SASIDA.auth.updateHeader();
    }
  }

  // ─── NAVIGATION SYSTEM ──────────────────────────

  var underline = document.querySelector('.nav-underline');
  if (!underline) {
    underline = document.createElement('span');
    underline.className = 'nav-underline';
    navList.appendChild(underline);
  }

  var currentActiveId = null;
  var isScrolling = false;
  var scrollTimeout = null;

  function getNavbarHeight() {
    return header.getBoundingClientRect().height;
  }

  function updateUnderline(link) {
    if (!link) return;
    var rect = link.getBoundingClientRect();
    var navRect = navList.getBoundingClientRect();
    var left = rect.left - navRect.left;
    var width = rect.width;
    underline.style.transform = 'translateX(' + left + 'px)';
    underline.style.width = width + 'px';
    underline.style.opacity = '1';
  }

  function setActiveLink(link) {
    if (!link) return;
    var page = link.dataset.page || link.getAttribute('href')?.replace('#', '')?.replace('.html', '')?.replace('.php', '');
    if (!page) return;
    if (currentActiveId === page) return;

    document.querySelectorAll('.nav-link, .mob-link').forEach(function(l) { l.classList.remove('active'); });
    document.querySelectorAll('.nav-link[data-page="' + page + '"], .mob-link[data-page="' + page + '"]').forEach(function(l) { l.classList.add('active'); });
    document.querySelectorAll('.nav-link[href*="' + page + '"], .mob-link[href*="' + page + '"]').forEach(function(l) { l.classList.add('active'); });

    var desktopLink = document.querySelector('.nav-link[data-page="' + page + '"]') || document.querySelector('.nav-link[href*="' + page + '"]');
    if (desktopLink) updateUnderline(desktopLink);

    currentActiveId = page;
  }

  function scrollToSection(sectionId) {
    var section = document.getElementById(sectionId);
    if (!section) return;
    var navbarHeight = getNavbarHeight();
    var extraOffset = 16;
    var targetPosition = section.getBoundingClientRect().top + window.pageYOffset - navbarHeight - extraOffset;
    window.scrollTo({
      top: targetPosition,
      behavior: 'smooth'
    });
  }

  function initScrollSpy() {
    var thresholds = [0, 0.25, 0.5, 0.75, 1];
    var rootMargin = '-' + getNavbarHeight() + 'px 0px -20% 0px';
    var observer = new IntersectionObserver(function(entries) {
      var bestEntry = null;
      var bestRatio = 0;
      for (var i = 0; i < entries.length; i++) {
        if (entries[i].isIntersecting && entries[i].intersectionRatio > bestRatio) {
          bestRatio = entries[i].intersectionRatio;
          bestEntry = entries[i];
        }
      }
      if (bestEntry) {
        var sectionId = bestEntry.target.id;
        if (sectionId !== currentActiveId && !isScrolling) {
          var link = document.querySelector('.nav-link[data-page="' + sectionId + '"]') ||
                       document.querySelector('.nav-link[href="#' + sectionId + '"]');
          if (link) setActiveLink(link);
        }
      }
    }, { threshold: thresholds, rootMargin: rootMargin });

    var sectionIds = ['home', 'shop', 'categories', 'newarrivals', 'about', 'contact'];
    sectionIds.forEach(function(id) {
      var section = document.getElementById(id);
      if (section) observer.observe(section);
    });
  }

  function initNavigation() {
    document.querySelectorAll('.nav-link, .mob-link').forEach(function(link) {
      link.addEventListener('click', function(e) {
        var href = this.getAttribute('href');
        if (href && href.startsWith('#')) {
          e.preventDefault();
          var sectionId = href.replace('#', '');
          if (['home','shop','categories','newarrivals','about','contact'].indexOf(sectionId) !== -1) {
            setActiveLink(this);
            isScrolling = true;
            clearTimeout(scrollTimeout);
            scrollToSection(sectionId);
            if (history.pushState) history.pushState(null, null, href);
            scrollTimeout = setTimeout(function() { isScrolling = false; }, 600);
          }
        }
      });
    });

    window.addEventListener('hashchange', setActiveNavFromHash);

    var resizeTimer;
    window.addEventListener('resize', function() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function() {
        var activeLink = document.querySelector('.nav-link.active');
        if (activeLink) updateUnderline(activeLink);
      }, 200);
    });

    document.fonts?.ready?.then(function() {
      var activeLink = document.querySelector('.nav-link.active');
      if (activeLink) updateUnderline(activeLink);
    });
  }

  function setActiveNavFromHash() {
    var hash = window.location.hash || '#home';
    var sectionId = hash.replace('#', '');
    var link = document.querySelector('.nav-link[data-page="' + sectionId + '"]') ||
                 document.querySelector('.nav-link[href="#' + sectionId + '"]');
    if (link) setActiveLink(link);
    else {
      var homeLink = document.querySelector('.nav-link[data-page="home"]');
      if (homeLink) setActiveLink(homeLink);
    }
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