/* ═══════════════════════════════════════════════
   SASIDA — cart.js
   Shopping Cart with authentication & error handling
═══════════════════════════════════════════════ */

(function() {
  'use strict';

  // ─── AUTH CHECK ────────────────────────────────

  function isLoggedIn() {
    return !!(window.SASIDA && window.SASIDA.auth && window.SASIDA.auth.isLoggedIn());
  }

  // If not logged in, redirect to login with return URL
  if (!isLoggedIn()) {
    var returnUrl = encodeURIComponent(window.location.pathname);
    window.location.href = 'login.html?return=' + returnUrl;
    return; // Stop execution
  }

  // ─── DOM refs ──────────────────────────────────
  var loader = document.getElementById('loader');
  var scrollTopBtn = document.getElementById('scrollTop');
  var header = document.getElementById('header');
  var themeToggle = document.getElementById('themeToggle');
  var hamburger = document.getElementById('hamburger');
  var mobileMenu = document.getElementById('mobileMenu');
  var cartContent = document.getElementById('cartContent');
  var cartCount = document.getElementById('cartCount');

  // ─── LOADER ────────────────────────────────────
  function hideLoader() {
    if (loader) loader.classList.add('hidden');
  }

  window.addEventListener('load', function() {
    setTimeout(hideLoader, 800);
    setTimeout(hideLoader, 100);
    setTimeout(function() {
      try {
        initCartPage();
        updateCartBadge();
      } catch (e) {
        console.error('Cart initialization error:', e);
        if (cartContent) {
          cartContent.innerHTML = '<div style="text-align:center;padding:60px 20px;color:#e05757;">' +
            '<p>Sorry, there was an error loading your cart.</p>' +
            '<button onclick="location.reload()" style="padding:10px 20px;margin-top:10px;background:var(--gold);color:#12100d;border:none;border-radius:8px;cursor:pointer;">Reload</button>' +
            '</div>';
        }
      }
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

  // ─── LOGO EXPAND ──────────────────────────────
  var logoWrapper = document.getElementById('logoWrapper');
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

  // ─── CART FUNCTIONS ────────────────────────────

  var CART_STORAGE_KEY = 'sasida_cart';

  function getCart() {
    try {
      var cart = localStorage.getItem(CART_STORAGE_KEY);
      return cart ? JSON.parse(cart) : [];
    } catch (e) {
      console.warn('Error reading cart:', e);
      return [];
    }
  }

  function saveCart(cart) {
    try {
      localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
      updateCartBadge();
    } catch (e) {
      console.warn('Error saving cart:', e);
    }
  }

  function updateCartBadge() {
    try {
      var cart = getCart();
      var count = 0;
      for (var i = 0; i < cart.length; i++) {
        count += (cart[i].quantity || 0);
      }
      if (cartCount) {
        cartCount.textContent = count;
        cartCount.style.transform = 'scale(1.4)';
        setTimeout(function() {
          cartCount.style.transform = 'scale(1)';
        }, 200);
      }
    } catch (e) {
      console.warn('Error updating badge:', e);
    }
  }

  function getProductById(id) {
    if (typeof productsData !== 'undefined' && productsData.all) {
      for (var i = 0; i < productsData.all.length; i++) {
        if (productsData.all[i].id === parseInt(id)) {
          return productsData.all[i];
        }
      }
    }
    return null;
  }

  function removeFromCart(productId, variant) {
    var cart = getCart();
    var newCart = [];
    for (var i = 0; i < cart.length; i++) {
      if (!(cart[i].id === productId && cart[i].variant === variant)) {
        newCart.push(cart[i]);
      }
    }
    saveCart(newCart);
    return newCart;
  }

  function updateQuantity(productId, variant, quantity) {
    var cart = getCart();
    var itemFound = false;
    for (var i = 0; i < cart.length; i++) {
      if (cart[i].id === productId && cart[i].variant === variant) {
        itemFound = true;
        if (quantity <= 0) {
          cart.splice(i, 1);
        } else {
          cart[i].quantity = Math.min(quantity, cart[i].maxQuantity || 99);
        }
        break;
      }
    }
    if (itemFound) {
      saveCart(cart);
    }
    return cart;
  }

  function clearCart() {
    localStorage.removeItem(CART_STORAGE_KEY);
    updateCartBadge();
    return [];
  }

  // ─── CART PAGE RENDERING ──────────────────────

  function renderCartPage() {
    if (!cartContent) {
      console.error('cartContent element not found');
      return;
    }

    try {
      var cart = getCart();

      if (cart.length === 0) {
        cartContent.innerHTML = '<div style="text-align:center;padding:60px 20px;">' +
          '<div style="font-size:4rem;margin-bottom:20px;">🛒</div>' +
          '<h2 style="margin-bottom:12px;">Your Cart is Empty</h2>' +
          '<p style="color:var(--text-muted);margin-bottom:24px;">You haven\'t added any products yet. Start shopping to find products you\'ll love.</p>' +
          '<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">' +
          '<a href="shop.html" class="btn btn-primary">Continue Shopping</a>' +
          '<a href="shop.html?category=all" class="btn btn-outline">Browse Categories</a>' +
          '</div>' +
          '</div>';
        return;
      }

      var subtotal = 0;
      var itemsHtml = '';

      for (var i = 0; i < cart.length; i++) {
        var item = cart[i];
        var product = getProductById(item.id);
        var itemName = product ? product.name : item.name;
        var itemPrice = product ? product.price : item.price;
        var itemImage = product ? product.image : (item.image || '📦');
        var itemTotal = itemPrice * item.quantity;
        subtotal += itemTotal;

        var imageHtml = itemImage && itemImage.startsWith('http')
          ? '<img src="' + itemImage + '" alt="' + itemName + '" style="width:80px;height:80px;object-fit:cover;border-radius:var(--radius-sm);" />'
          : '<div style="width:80px;height:80px;background:var(--bg-alt);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:2.5rem;">' + itemImage + '</div>';

        itemsHtml += '<div class="cart-item" style="display:grid;grid-template-columns:auto 1fr auto auto;gap:16px;align-items:center;padding:16px 0;border-bottom:1px solid var(--border);">' +
          '<div>' + imageHtml + '</div>' +
          '<div>' +
          '<h4 style="font-size:1rem;margin-bottom:4px;">' + itemName + '</h4>' +
          '<span style="font-size:0.8rem;color:var(--text-muted);">' + (item.variant || 'Default') + '</span>' +
          '<div style="font-size:0.9rem;color:var(--gold);margin-top:4px;">ETB ' + itemPrice.toFixed(2) + '</div>' +
          '</div>' +
          '<div style="display:flex;align-items:center;gap:8px;">' +
          '<button class="qty-btn" data-index="' + i + '" data-action="dec" style="width:30px;height:30px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer;display:flex;align-items:center;justify-content:center;">−</button>' +
          '<span style="min-width:30px;text-align:center;">' + item.quantity + '</span>' +
          '<button class="qty-btn" data-index="' + i + '" data-action="inc" style="width:30px;height:30px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface);color:var(--text);cursor:pointer;display:flex;align-items:center;justify-content:center;">+</button>' +
          '</div>' +
          '<div style="text-align:right;">' +
          '<div style="font-weight:500;">ETB ' + itemTotal.toFixed(2) + '</div>' +
          '<button class="remove-btn" data-index="' + i + '" style="background:none;border:none;color:#e05757;cursor:pointer;font-size:0.8rem;">Remove</button>' +
          '</div>' +
          '</div>';
      }

      var shipping = subtotal > 0 ? 150 : 0;
      var tax = subtotal * 0.15;
      var total = subtotal + shipping + tax;

      cartContent.innerHTML = '<div style="display:grid;grid-template-columns:1fr 360px;gap:40px;align-items:start;">' +
        '<div>' + itemsHtml + '</div>' +
        '<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:24px;position:sticky;top:100px;">' +
        '<h3 style="font-size:1.2rem;margin-bottom:16px;">Order Summary</h3>' +
        '<div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">' +
        '<span style="color:var(--text-muted);">Subtotal</span>' +
        '<span>ETB ' + subtotal.toFixed(2) + '</span>' +
        '</div>' +
        '<div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">' +
        '<span style="color:var(--text-muted);">Shipping</span>' +
        '<span>ETB ' + shipping.toFixed(2) + '</span>' +
        '</div>' +
        '<div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);">' +
        '<span style="color:var(--text-muted);">Tax (15%)</span>' +
        '<span>ETB ' + tax.toFixed(2) + '</span>' +
        '</div>' +
        '<div style="display:flex;justify-content:space-between;padding:12px 0;font-size:1.2rem;font-weight:600;">' +
        '<span>Total</span>' +
        '<span style="color:var(--gold);">ETB ' + total.toFixed(2) + '</span>' +
        '</div>' +
        '<a href="checkout.html" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:12px;">Proceed to Checkout</a>' +
        '<button id="clearCartBtn" style="width:100%;margin-top:8px;padding:10px;background:none;border:1px solid #e05757;color:#e05757;border-radius:var(--radius-sm);cursor:pointer;font-family:\'DM Sans\',sans-serif;font-size:0.85rem;transition:all 0.25s;">Clear Cart</button>' +
        '</div>' +
        '</div>';

      // Event listeners
      document.querySelectorAll('.qty-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var index = parseInt(this.dataset.index);
          var action = this.dataset.action;
          var cart = getCart();
          if (cart[index]) {
            var item = cart[index];
            if (action === 'inc') {
              updateQuantity(item.id, item.variant, item.quantity + 1);
            } else if (action === 'dec') {
              updateQuantity(item.id, item.variant, item.quantity - 1);
            }
            renderCartPage();
            updateCartBadge();
          }
        });
      });

      document.querySelectorAll('.remove-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var index = parseInt(this.dataset.index);
          var cart = getCart();
          if (cart[index]) {
            var item = cart[index];
            removeFromCart(item.id, item.variant);
            renderCartPage();
            updateCartBadge();
          }
        });
      });

      var clearBtn = document.getElementById('clearCartBtn');
      if (clearBtn) {
        clearBtn.addEventListener('click', function() {
          if (confirm('Are you sure you want to clear your cart?')) {
            clearCart();
            renderCartPage();
            updateCartBadge();
          }
        });
      }

    } catch (e) {
      console.error('Error rendering cart:', e);
      cartContent.innerHTML = '<div style="text-align:center;padding:60px 20px;color:#e05757;">' +
        '<p>There was an error loading your cart. Please refresh the page.</p>' +
        '<button onclick="location.reload()" style="padding:10px 20px;margin-top:10px;background:var(--gold);color:#12100d;border:none;border-radius:8px;cursor:pointer;">Refresh</button>' +
        '</div>';
    }
  }

  function initCartPage() {
    renderCartPage();
    updateCartBadge();
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