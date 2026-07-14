/* ═══════════════════════════════════════════════
   SASIDA — new_arrivals.js
   New Arrivals product listing
═══════════════════════════════════════════════ */

(function() {
  'use strict';

  var loader = document.getElementById('loader');
  var scrollTopBtn = document.getElementById('scrollTop');
  var header = document.getElementById('header');
  var themeToggle = document.getElementById('themeToggle');
  var hamburger = document.getElementById('hamburger');
  var mobileMenu = document.getElementById('mobileMenu');
  var productsGrid = document.getElementById('newArrivalsGrid');
  var countEl = document.getElementById('newArrivalsCount');

  var CART_KEY = 'sasida_cart';
  var WISHLIST_KEY = 'sasida_wishlist';

  function hideLoader() {
    if (loader) loader.classList.add('hidden');
  }

  hideLoader();
  document.addEventListener('DOMContentLoaded', init);
  setTimeout(hideLoader, 1500);

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

  function isLoggedIn() {
    return window.SASIDA && window.SASIDA.auth && window.SASIDA.auth.isLoggedIn();
  }

  function getCategoryName(categoryId) {
    if (typeof categoriesData === 'undefined') return 'Uncategorized';
    var cat = categoriesData.find(function(c) { return c.id === categoryId; });
    return cat ? cat.name : 'Uncategorized';
  }

  function getWishlist() {
    try { return JSON.parse(localStorage.getItem(WISHLIST_KEY)) || []; }
    catch (e) { return []; }
  }

  function saveWishlist(list) {
    localStorage.setItem(WISHLIST_KEY, JSON.stringify(list));
  }

  function toggleWishlist(productId) {
    if (!isLoggedIn()) {
      window.location.href = 'login.php?return=' + encodeURIComponent(window.location.pathname);
      return;
    }
    var list = getWishlist();
    var idx = list.indexOf(productId);
    if (idx === -1) list.push(productId);
    else list.splice(idx, 1);
    saveWishlist(list);
    renderProducts();
  }

  function addToCart(product) {
    if (!isLoggedIn()) {
      window.location.href = 'login.php?return=' + encodeURIComponent(window.location.pathname);
      return false;
    }
    var cart;
    try { cart = JSON.parse(localStorage.getItem(CART_KEY)) || []; }
    catch (e) { cart = []; }
    var existing = cart.find(function(item) { return item.id === product.id; });
    if (existing) existing.quantity += 1;
    else {
      cart.push({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
        quantity: 1,
        variant: 'Default',
        maxQuantity: 99
      });
    }
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    return true;
  }

  function init() {
    hideLoader();
    if (typeof productsData === 'undefined') {
      showError('Unable to load products. Please refresh the page.');
      return;
    }
    renderProducts();
  }

  function renderProducts() {
    if (!productsGrid) return;

    var newArrivals = productsData.newArrivals || [];
    if (countEl) countEl.textContent = newArrivals.length;

    if (newArrivals.length === 0) {
      productsGrid.innerHTML =
        '<div class="empty-state" style="grid-column:1/-1;text-align:center;padding:80px 20px;">' +
        '<div style="font-size:3rem;margin-bottom:16px;opacity:0.5;">✨</div>' +
        '<h3 style="font-family:\'Cormorant Garamond\',serif;font-size:1.8rem;margin-bottom:8px;">No New Arrivals Yet</h3>' +
        '<p style="color:var(--text-muted);margin-bottom:24px;">No new arrivals are available at the moment. Please check back soon.</p>' +
        '<a href="shop.php" class="btn btn-primary">Browse All Products</a>' +
        '</div>';
      return;
    }

    var wishlist = getWishlist();
    var gradients = [
      'linear-gradient(135deg,#f5d6c6,#f0b8a0)',
      'linear-gradient(135deg,#c9d4d1,#a3b8b3)',
      'linear-gradient(135deg,#d4c9b0,#b8a88c)',
      'linear-gradient(135deg,#d5c9d4,#b8a8b0)'
    ];

    var html = '';
    newArrivals.forEach(function(p, index) {
      var bg = gradients[index % gradients.length];
      var isWishlisted = wishlist.indexOf(p.id) !== -1;
      var categoryName = getCategoryName(p.categoryId);
      var oldPriceHtml = p.oldPrice ? '<span class="old-price">ETB ' + p.oldPrice.toFixed(2) + '</span>' : '';
      var ratingHtml = p.rating
        ? '<div class="product-rating">' + '★'.repeat(Math.round(p.rating)) + '<span>(' + (p.reviews || 0) + ')</span></div>'
        : '';
      var imageHtml = p.image && p.image.startsWith('http')
        ? '<img src="' + p.image + '" alt="' + p.name + '" style="width:100%;height:100%;object-fit:cover;" />'
        : '<span class="product-placeholder">' + (p.image || '✨') + '</span>';

      html += '<div class="product-card reveal" data-product-id="' + p.id + '">' +
        '<div class="product-img-wrap" style="background:' + bg + ';">' +
        imageHtml +
        '<span class="product-badge">New Arrival</span>' +
        '<button class="wishlist-btn' + (isWishlisted ? ' active' : '') + '" data-wishlist-id="' + p.id + '" aria-label="Add to wishlist">' +
        (isWishlisted ? '♥' : '♡') + '</button>' +
        '<button class="quick-view-btn" data-quick-view="' + p.id + '" aria-label="Quick view">Quick View</button>' +
        '</div>' +
        '<div class="product-body">' +
        '<span class="product-category">' + categoryName + '</span>' +
        '<h4>' + p.name + '</h4>' +
        ratingHtml +
        '<div class="product-price">' + oldPriceHtml + ' ETB ' + p.price.toFixed(2) + '</div>' +
        '</div>' +
        '<div class="product-actions">' +
        '<button class="btn btn-outline btn-sm add-to-cart-btn" data-cart-id="' + p.id + '">Add to Cart</button>' +
        '</div>' +
        '</div>';
    });

    productsGrid.innerHTML = html;

    productsGrid.querySelectorAll('.wishlist-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleWishlist(parseInt(this.dataset.wishlistId, 10));
      });
    });

    productsGrid.querySelectorAll('.quick-view-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        window.location.href = 'product.php?id=' + this.dataset.quickView;
      });
    });

    productsGrid.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        var productId = parseInt(this.dataset.cartId, 10);
        var product = newArrivals.find(function(p) { return p.id === productId; });
        if (product && addToCart(product)) {
          var original = this.textContent;
          this.textContent = '✓ Added!';
          this.style.background = '#52c48b';
          this.style.color = '#fff';
          var self = this;
          setTimeout(function() {
            self.textContent = original;
            self.style.background = '';
            self.style.color = '';
          }, 1500);
        }
      });
    });

    productsGrid.querySelectorAll('.product-card .product-body').forEach(function(body, i) {
      body.style.cursor = 'pointer';
      body.addEventListener('click', function() {
        window.location.href = 'product.php?id=' + newArrivals[i].id;
      });
    });

    var revealElements = productsGrid.querySelectorAll('.reveal');
    var revealObserver = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) entry.target.classList.add('visible');
      });
    }, { threshold: 0.1 });
    revealElements.forEach(function(el) { revealObserver.observe(el); });
  }

  function showError(message) {
    if (productsGrid) {
      productsGrid.innerHTML =
        '<div style="grid-column:1/-1;text-align:center;padding:60px 0;color:#e05757;">' +
        '<p style="font-size:1.2rem;">⚠️ ' + message + '</p>' +
        '</div>';
    }
  }

})();
