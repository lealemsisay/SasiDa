/* ═══════════════════════════════════════════════
   SASIDA — product.js
   Dynamic product details, multiple image gallery, and related products
   ═══════════════════════════════════════════════ */

(function() {
  'use strict';

  // ─── DOM REFS ──────────────────────────────────
  var loader = document.getElementById('loader');
  var scrollTopBtn = document.getElementById('scrollTop');
  var header = document.getElementById('header');
  var themeToggle = document.getElementById('themeToggle');
  var hamburger = document.getElementById('hamburger');
  var mobileMenu = document.getElementById('mobileMenu');
  var logoWrapper = document.getElementById('logoWrapper');

  // ─── LOADER ────────────────────────────────────
  function hideLoader() {
    if (loader) loader.classList.add('hidden');
  }
  hideLoader();
  setTimeout(hideLoader, 500);

  // ─── THEME ─────────────────────────────────────
  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
  }
  var storedTheme = localStorage.getItem('theme');
  applyTheme(storedTheme || 'dark');

  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      var current = document.documentElement.getAttribute('data-theme');
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

  // ─── GALLERY INTERACTION & DYNAMIC POPULATION ───
  function initGallery() {
    var mainImg = document.getElementById('mainDisplayImg');
    var mainContainer = document.getElementById('mainImage');
    var thumbnails = document.querySelectorAll('.thumbnail');

    if (thumbnails.length > 0) {
      thumbnails.forEach(function(thumb) {
        thumb.addEventListener('click', function() {
          var src = this.getAttribute('data-src');
          if (src && mainContainer) {
            mainContainer.innerHTML = '<img src="' + src + '" alt="Product Image" style="width:100%;height:100%;object-fit:cover;" id="mainDisplayImg" />';
          }
          thumbnails.forEach(function(t) {
            t.style.borderColor = 'var(--border)';
            t.classList.remove('active');
          });
          this.style.borderColor = 'var(--gold)';
          this.classList.add('active');
        });

        // Also add hover feedback
        thumb.addEventListener('mouseenter', function() {
          var src = this.getAttribute('data-src');
          if (src && mainContainer) {
            mainContainer.innerHTML = '<img src="' + src + '" alt="Product Image" style="width:100%;height:100%;object-fit:cover;" id="mainDisplayImg" />';
          }
        });
      });
    }
  }

  // ─── ADD TO CART HANDLER ON PRODUCT PAGE ────────
  function initAddToCart() {
    var addBtn = document.getElementById('addToCartBtn');
    if (!addBtn) return;

    addBtn.addEventListener('click', function() {
      var prodId = parseInt(this.dataset.productId);
      if (!prodId) return;

      if (!window.SASIDA || !window.SASIDA.auth || !window.SASIDA.auth.isLoggedIn()) {
        var returnUrl = encodeURIComponent(window.location.pathname + window.location.search);
        window.location.href = 'login.php?return=' + returnUrl;
        return;
      }

      var cartKey = 'sasida_cart';
      var cart = [];
      try { cart = JSON.parse(localStorage.getItem(cartKey)) || []; } catch(e) { cart = []; }

      var existing = cart.find(item => item.id === prodId);
      if (existing) {
        existing.quantity += 1;
      } else {
        var nameEl = document.getElementById('productName');
        var priceEl = document.getElementById('productPrice');
        var mainImg = document.getElementById('mainDisplayImg');
        
        var name = nameEl ? nameEl.textContent.trim() : 'Product #' + prodId;
        var priceText = priceEl ? priceEl.textContent.replace(/[^0-9.]/g, '') : '0';
        var image = mainImg ? mainImg.getAttribute('src') : '';

        cart.push({
          id: prodId,
          name: name,
          price: parseFloat(priceText) || 0,
          image: image,
          quantity: 1,
          variant: 'Default',
          maxQuantity: 99
        });
      }

      localStorage.setItem(cartKey, JSON.stringify(cart));

      // Update cart count badge
      var cartCountEl = document.getElementById('cartCount');
      if (cartCountEl) {
        var count = cart.reduce((sum, item) => sum + (item.quantity || 0), 0);
        cartCountEl.textContent = count;
      }

      var originalText = addBtn.textContent;
      addBtn.textContent = '✓ Added to Cart!';
      addBtn.style.background = '#52c48b';
      addBtn.style.color = '#fff';
      setTimeout(function() {
        addBtn.textContent = originalText;
        addBtn.style.background = '';
        addBtn.style.color = '';
      }, 2000);
    });
  }

  // ─── RELATED PRODUCTS RENDERING ─────────────────
  function initRelatedProducts() {
    var relatedContainer = document.getElementById('relatedProducts');
    if (!relatedContainer) return;

    var urlParams = new URLSearchParams(window.location.search);
    var currentId = parseInt(urlParams.get('id') || '0');

    fetch('api/products.php?t=' + Date.now())
      .then(res => res.json())
      .then(data => {
        if (data.success && data.products) {
          var otherProds = data.products.filter(p => p.id !== currentId && p.status === 'visible').slice(0, 4);
          if (otherProds.length === 0) {
            relatedContainer.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:40px;">No related products found.</p>';
            return;
          }

          var html = otherProds.map((p, idx) => {
            var gradients = [
              'linear-gradient(135deg,#f5d6c6,#f0b8a0)',
              'linear-gradient(135deg,#c9d4d1,#a3b8b3)',
              'linear-gradient(135deg,#d4c9b0,#b8a88c)',
              'linear-gradient(135deg,#d5c9d4,#b8a8b0)'
            ];
            var bg = gradients[idx % gradients.length];
            var imgHtml = p.image ? '<img src="' + p.image.replace(/^\/+/, '') + '" alt="' + p.name + '" style="width:100%;height:100%;object-fit:cover;" />' : '<span class="product-placeholder">📦</span>';
            var oldPrice = p.oldPrice ? '<span class="old-price">ETB ' + p.oldPrice.toFixed(2) + '</span>' : '';
            var categoryTag = '<div class="product-category">' + (p.categoryName || 'General') + '</div>';

            return '<div class="product-card reveal" data-product-id="' + p.id + '">' +
              '<a href="product.php?id=' + p.id + '" class="product-link">' +
              '<div class="product-img-wrap" style="background:' + bg + ';">' +
              imgHtml +
              '</div>' +
              '<div class="product-body">' +
              '<div class="product-body-top">' +
              categoryTag +
              '<h4>' + p.name + '</h4>' +
              '</div>' +
              '<div class="product-body-bottom">' +
              '<div class="product-price">' + oldPrice + ' ETB ' + p.price.toFixed(2) + '</div>' +
              '</div>' +
              '</div>' +
              '</a>' +
              '</div>';
          }).join('');

          relatedContainer.innerHTML = html;

          var revealElements = relatedContainer.querySelectorAll('.reveal');
          var observer = new IntersectionObserver(entries => {
            entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
          }, { threshold: 0.1 });
          revealElements.forEach(el => observer.observe(el));
        }
      })
      .catch(err => {
        console.warn('Error loading related products:', err);
      });
  }

  // ─── INIT ──────────────────────────────────────
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      initGallery();
      initAddToCart();
      initRelatedProducts();
    });
  } else {
    initGallery();
    initAddToCart();
    initRelatedProducts();
  }

})();