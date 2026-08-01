/* ═══════════════════════════════════════════════
   SASIDA — script.js
   Landing page & global UI interactions + Live DB Sync
   ═══════════════════════════════════════════════ */

(function() {
  'use strict';

  // ─── DOM REFS ──────────────────────────────────
  const loader = document.getElementById('loader');
  const scrollTopBtn = document.getElementById('scrollTop');
  const header = document.getElementById('header');
  const themeToggle = document.getElementById('themeToggle');
  const hamburger = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobileMenu');
  const logoWrapper = document.getElementById('logoWrapper');
  const cartCount = document.getElementById('cartCount');

  // ─── LOADER ────────────────────────────────────
  function hideLoader() {
    if (loader) loader.classList.add('hidden');
  }
  hideLoader();
  setTimeout(hideLoader, 500);
  setTimeout(hideLoader, 1000);

  // ─── THEME ─────────────────────────────────────
  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
  }

  const storedTheme = localStorage.getItem('theme');
  applyTheme(storedTheme || 'dark');

  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      const current = document.documentElement.getAttribute('data-theme');
      applyTheme(current === 'light' ? 'dark' : 'light');
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

  // ─── SCROLL & HEADER ──────────────────────────
  window.addEventListener('scroll', function() {
    if (scrollTopBtn) {
      if (window.scrollY > 300) scrollTopBtn.classList.add('show');
      else scrollTopBtn.classList.remove('show');
    }
    if (header) {
      if (window.scrollY > 40) header.classList.add('scrolled');
      else header.classList.remove('scrolled');
    }
  });

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', function() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

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

  // ─── CART ──────────────────────────────────────
  const CART_STORAGE_KEY = 'sasida_cart';

  function getCart() {
    try { return JSON.parse(localStorage.getItem(CART_STORAGE_KEY)) || []; } catch { return []; }
  }

  function saveCart(cart) {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    updateCartBadge();
  }

  function updateCartBadge() {
    const cart = getCart();
    const count = cart.reduce(function(sum, item) { return sum + (item.quantity || 0); }, 0);
    if (cartCount) {
      cartCount.textContent = count;
    }
  }

  function addToCart(productId, quantity, variant) {
    if (!window.SASIDA || !window.SASIDA.auth || !window.SASIDA.auth.isLoggedIn()) {
      const returnUrl = encodeURIComponent(window.location.pathname + window.location.search);
      window.location.href = 'login.php?return=' + returnUrl;
      return false;
    }

    let cart = getCart();
    const existing = cart.find(function(item) { return item.id === productId && item.variant === variant; });
    const product = (typeof productsData !== 'undefined' && productsData.all) 
      ? productsData.all.find(p => p.id === productId) 
      : null;

    if (existing) {
      existing.quantity += quantity;
    } else {
      cart.push({
        id: productId,
        name: product ? product.name : 'Product #' + productId,
        price: product ? product.price : 0,
        image: product ? product.image : '',
        quantity: quantity,
        variant: variant || 'Default',
        maxQuantity: 99
      });
    }
    saveCart(cart);
    return true;
  }

  // ─── DYNAMIC RENDERING FOR LANDING PAGE ────────
  function renderImageHtml(image, name) {
    if (image && image.trim() !== '') {
      var clean = image.replace(/^\/+/, '');
      return '<img src="' + clean + '" alt="' + (name || 'Product') + '" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';" />' +
             '<span class="product-placeholder" style="font-size:3.5rem;display:none;">📦</span>';
    }
    return '<span class="product-placeholder" style="font-size:3.5rem;">📦</span>';
  }

  function renderProductCard(p, index) {
    const gradients = [
      'linear-gradient(135deg,#f5d6c6,#f0b8a0)',
      'linear-gradient(135deg,#c9d4d1,#a3b8b3)',
      'linear-gradient(135deg,#d4c9b0,#b8a88c)',
      'linear-gradient(135deg,#d5c9d4,#b8a8b0)',
      'linear-gradient(135deg,#e8d5c4,#d4b8a4)',
      'linear-gradient(135deg,#b5c9d6,#8fb0c4)'
    ];
    var bg = gradients[index % gradients.length];
    var oldPriceHtml = p.oldPrice ? '<span class="old-price" style="text-decoration:line-through;color:var(--text-muted);font-size:0.85rem;margin-right:6px;">ETB ' + p.oldPrice.toFixed(2) + '</span>' : '';
    var badgeHtml = p.badge ? '<span class="product-badge">' + p.badge + '</span>' : '';
    var ratingHtml = p.rating ? '<div style="font-size:0.8rem;color:var(--gold);margin-bottom:6px;">' + '★'.repeat(Math.round(p.rating)) + ' (' + (p.reviews || 0) + ')</div>' : '';

    var stockBadge = '';
    var buttonHtml = '<button class="btn btn-outline btn-sm add-to-cart-btn" data-product-id="' + p.id + '" style="width:100%;">Add to Cart</button>';
    
    if (p.status === 'outofstock' || !p.inStock) {
      stockBadge = '<span style="display:inline-block;padding:2px 8px;background:#e74c3c;color:#fff;font-size:0.75rem;border-radius:4px;margin-bottom:6px;font-weight:600;">Out of Stock</span>';
      buttonHtml = '<button class="btn btn-outline btn-sm" disabled style="width:100%;opacity:0.6;cursor:not-allowed;">Out of Stock</button>';
    }

    return '<div class="product-card reveal" data-product-id="' + p.id + '">' +
      '<a href="product.php?id=' + p.id + '" class="product-link">' +
      '<div class="product-img-wrap" style="background:' + bg + ';">' +
      renderImageHtml(p.image, p.name) +
      badgeHtml +
      '</div>' +
      '<div class="product-body">' +
      '<div class="product-body-top">' +
      '<div class="product-category">' + (p.categoryName || 'General') + '</div>' +
      '<h4>' + p.name + '</h4>' +
      ratingHtml +
      stockBadge +
      '</div>' +
      '<div class="product-body-bottom">' +
      '<div class="product-price">' + oldPriceHtml + ' ETB ' + p.price.toFixed(2) + '</div>' +
      buttonHtml +
      '</div>' +
      '</div>' +
      '</a>' +
      '</div>';
  }

  function bindCartButtons(container) {
    if (!container) return;
    container.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var productId = parseInt(this.dataset.productId);
        var success = addToCart(productId, 1, 'Default');
        if (success) {
          var originalText = this.textContent;
          this.textContent = '✓ Added!';
          this.style.background = '#52c48b';
          this.style.color = '#fff';
          setTimeout(function() {
            btn.textContent = originalText;
            btn.style.background = '';
            btn.style.color = '';
          }, 1500);
        }
      });
    });
  }

  function renderLandingPageData() {
    if (typeof productsData === 'undefined' || !productsData.all) return;

    // 1. Featured Products (first 4 visible products)
    var featuredContainer = document.getElementById('featuredProducts');
    if (featuredContainer) {
      var featuredList = productsData.all.slice(0, 4);
      if (featuredList.length === 0) {
        featuredContainer.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:40px;">No featured products available.</p>';
      } else {
        featuredContainer.innerHTML = featuredList.map((p, idx) => renderProductCard(p, idx)).join('');
        bindCartButtons(featuredContainer);
      }
    }

    // 2. Best Sellers
    var bestsellerContainer = document.getElementById('bestsellerProducts');
    if (bestsellerContainer) {
      var bestsellersList = productsData.all.filter(p => p.badge === 'Best Seller');
      if (bestsellersList.length === 0) bestsellersList = productsData.all.slice(0, 4);
      bestsellerContainer.innerHTML = bestsellersList.map((p, idx) => renderProductCard(p, idx)).join('');
      bindCartButtons(bestsellerContainer);
    }

    // 3. New Arrivals
    var newArrivalContainer = document.getElementById('newArrivalProducts');
    if (newArrivalContainer) {
      var newArrivalsList = productsData.all.filter(p => p.badge === 'New' || p.badge === 'New Arrival');
      if (newArrivalsList.length === 0) newArrivalsList = productsData.all.slice(0, 4);
      newArrivalContainer.innerHTML = newArrivalsList.map((p, idx) => renderProductCard(p, idx)).join('');
      bindCartButtons(newArrivalContainer);
    }

    // 4. Categories Grid
    var categoriesGrid = document.querySelector('.categories-grid');
    if (categoriesGrid && typeof categoriesData !== 'undefined' && categoriesData.length > 0) {
      categoriesGrid.innerHTML = categoriesData.map(function(cat) {
        return '<a href="shop.php?category=' + cat.id + '" class="category-card reveal" style="text-decoration:none;">' +
          '<div class="category-icon-wrap" style="background:' + (cat.color || '#d4d9d1') + ';">' +
          '<span class="category-icon">' + (cat.icon || '📦') + '</span>' +
          '</div>' +
          '<h3>' + cat.name + '</h3>' +
          '<p class="category-count">' + (cat.productCount || 0) + ' Products</p>' +
          '</a>';
      }).join('');
    }

    // 5. Hydrate About and Contact from database settings
    if (typeof aboutData !== 'undefined') {
      var tagEl = document.getElementById('aboutTag'); if (tagEl && aboutData.tag) tagEl.textContent = aboutData.tag;
      var titleEl = document.getElementById('aboutTitle'); if (titleEl && aboutData.title) titleEl.textContent = aboutData.title;
      var d1El = document.getElementById('aboutDesc1'); if (d1El && aboutData.desc1) d1El.textContent = aboutData.desc1;
      var d2El = document.getElementById('aboutDesc2'); if (d2El && aboutData.desc2) d2El.textContent = aboutData.desc2;
      var rEl = document.getElementById('aboutRating'); if (rEl && aboutData.rating) rEl.textContent = aboutData.rating;
    }

    if (typeof contactData !== 'undefined') {
      var phoneEl = document.getElementById('contactPhone'); if (phoneEl && contactData.phone) phoneEl.textContent = contactData.phone;
      var emailEl = document.getElementById('contactEmail'); if (emailEl && contactData.email) emailEl.textContent = contactData.email;
      var waEl = document.getElementById('contactWhatsApp'); if (waEl && contactData.whatsapp) waEl.textContent = contactData.whatsapp;
      var instaEl = document.getElementById('contactInstagram'); 
      if (instaEl) { 
        if (contactData.instagramText) instaEl.textContent = contactData.instagramText;
        if (contactData.instagram) instaEl.href = contactData.instagram;
      }
      var ttEl = document.getElementById('contactTikTok');
      if (ttEl) {
        if (contactData.tiktokText) ttEl.textContent = contactData.tiktokText;
        if (contactData.tiktok) ttEl.href = contactData.tiktok;
      }
      var addrEl = document.getElementById('contactAddress'); if (addrEl && contactData.address) addrEl.textContent = contactData.address;
      var hoursEl = document.getElementById('contactHours'); if (hoursEl && contactData.hours) hoursEl.textContent = contactData.hours;
    }

    // Trigger reveal observer for dynamic elements
    var revealElements = document.querySelectorAll('.reveal');
    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(e) { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    revealElements.forEach(el => observer.observe(el));
  }

  // ─── REAL-TIME SYNCHRONIZATION ─────────────────
  let lastSyncTime = 0;
  function syncProductsFromDB() {
    fetch('api/products.php?t=' + Date.now())
      .then(res => res.json())
      .then(data => {
        if (data.success && data.products) {
          var productsChanged = JSON.stringify(data.products) !== JSON.stringify(window.productsData ? window.productsData.all : []);
          if (productsChanged) {
            window.productsData = {
              all: data.products,
              featured: data.products.slice(0, 4),
              bestsellers: data.products.filter(p => p.badge === 'Best Seller'),
              newArrivals: data.products.filter(p => p.badge === 'New' || p.badge === 'New Arrival')
            };
            if (data.categories) {
              window.categoriesData = data.categories;
            }
            renderLandingPageData();
          }
        }
      })
      .catch(err => {
        console.warn('Sync check failed silently:', err);
      });
  }

  // Poll DB every 10 seconds for real-time synchronization
  setInterval(syncProductsFromDB, 10000);

  // ─── INIT ──────────────────────────────────────
  function initPage() {
    updateCartBadge();
    if (window.SASIDA && window.SASIDA.auth && window.SASIDA.auth.updateHeader) {
      window.SASIDA.auth.updateHeader();
    }
    renderLandingPageData();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPage);
  } else {
    initPage();
  }

  // ─── NEWSLETTER ────────────────────────────────
  document.querySelectorAll('.newsletter-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var input = form.querySelector('input[type="email"]');
      var successMsg = form.parentElement.querySelector('.newsletter-success');
      if (input && input.value.trim() !== '') {
        if (successMsg) successMsg.classList.add('show');
        input.value = '';
        setTimeout(function() { if (successMsg) successMsg.classList.remove('show'); }, 3000);
      }
    });
  });

})();