/* ═══════════════════════════════════════════════
   SASIDA — shop.js
   Product listing page logic with filters + auth
   Robust error handling & guaranteed loader hide
═══════════════════════════════════════════════ */

(function() {
  'use strict';

  console.log('shop.js loaded');

  // ─── DOM refs ──────────────────────────────────
  const loader = document.getElementById('loader');
  const scrollTopBtn = document.getElementById('scrollTop');
  const header = document.getElementById('header');
  const themeToggle = document.getElementById('themeToggle');
  const hamburger = document.getElementById('hamburger');
  const mobileMenu = document.getElementById('mobileMenu');
  const productsGrid = document.getElementById('shopProductsGrid');
  const paginationEl = document.getElementById('pagination');
  const categoryFilter = document.getElementById('categoryFilter');
  const sortFilter = document.getElementById('sortFilter');
  const productCount = document.getElementById('productCount');
  const cartCount = document.getElementById('cartCount');

  // ─── HELPER: hide loader ──────────────────────
  function hideLoader() {
    if (loader) loader.classList.add('hidden');
  }

  // ─── GUARANTEE loader hides ────────────────────
  hideLoader(); // immediate
  setTimeout(hideLoader, 500);
  setTimeout(hideLoader, 1000);

  // ─── CHECK DATA AVAILABILITY ──────────────────
  if (typeof productsData === 'undefined') {
    console.error('productsData is not defined! Check data.js.');
    if (productsGrid) {
      productsGrid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px 0;color:#e05757;">' +
        '<p style="font-size:1.2rem;">⚠️ Data not loaded. Please refresh.</p>' +
        '<p>Ensure data.js is loaded before shop.js</p>' +
        '<button onclick="window.location.reload()" style="padding:10px 20px;margin-top:10px;background:var(--gold);color:#12100d;border:none;border-radius:8px;cursor:pointer;">Refresh Page</button>' +
        '</div>';
    }
    return; // Stop execution
  }

  if (typeof categoriesData === 'undefined') {
    console.warn('categoriesData is not defined – categories filter may be empty.');
  }

  // ─── ENSURE ESSENTIAL ELEMENTS EXIST ──────────
  if (!productsGrid) {
    console.error('shopProductsGrid not found!');
    document.body.innerHTML += '<p style="color:red;padding:20px;">Error: shopProductsGrid missing.</p>';
    return;
  }

  // ─── THEME ─────────────────────────────────────
  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
  }

  const storedTheme = localStorage.getItem('theme');
  if (storedTheme) applyTheme(storedTheme);
  else applyTheme('dark');

  if (themeToggle) {
    themeToggle.addEventListener('click', function() {
      const current = document.documentElement.getAttribute('data-theme');
      const next = current === 'light' ? 'dark' : 'light';
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
  const logoWrapper = document.getElementById('logoWrapper');
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
  const CART_STORAGE_KEY = 'sasida_cart';

  function getCart() {
    try {
      return JSON.parse(localStorage.getItem(CART_STORAGE_KEY)) || [];
    } catch {
      return [];
    }
  }

  function saveCart(cart) {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
    updateCartBadge();
  }

  function updateCartBadge() {
    const cart = getCart();
    const count = cart.reduce(function(sum, item) {
      return sum + (item.quantity || 0);
    }, 0);
    if (cartCount) {
      cartCount.textContent = count;
      cartCount.style.transform = 'scale(1.4)';
      setTimeout(function() {
        cartCount.style.transform = 'scale(1)';
      }, 200);
    }
  }

  function addToCart(productId, quantity, variant) {
    if (!window.SASIDA || !window.SASIDA.auth || !window.SASIDA.auth.isLoggedIn()) {
      const returnUrl = encodeURIComponent(window.location.pathname + window.location.search);
      window.location.href = 'login.php?return=' + returnUrl;
      return false;
    }

    let cart = getCart();
    const existing = cart.find(function(item) {
      return item.id === productId && item.variant === variant;
    });
    if (existing) {
      existing.quantity += quantity;
    } else {
      const product = getProductById(productId);
      if (product) {
        cart.push({
          id: productId,
          name: product.name,
          price: product.price,
          image: product.image,
          quantity: quantity,
          variant: variant || 'Default',
          maxQuantity: 99
        });
      }
    }
    saveCart(cart);
    return true;
  }

  function getProductById(id) {
    return productsData.all.find(function(p) {
      return p.id === parseInt(id);
    });
  }

  // ─── SHOP STATE ────────────────────────────────

  let currentProducts = [];
  let filteredProducts = [];
  let currentPage = 1;
  const itemsPerPage = 8;

  function initShop() {
    console.log('initShop called');
    try {
      // Read URL parameters
      const urlParams = new URLSearchParams(window.location.search);
      const categoryParam = urlParams.get('category') || 'all';
      const sortParam = urlParams.get('sort') || 'default';

      // Populate category filter
      populateCategories();

      // Set filter values from URL
      if (categoryParam !== 'all') {
        const catOption = categoryFilter && categoryFilter.querySelector('option[value="' + categoryParam + '"]');
        if (catOption) categoryFilter.value = categoryParam;
      }
      if (sortParam) {
        const sortOption = sortFilter && sortFilter.querySelector('option[value="' + sortParam + '"]');
        if (sortOption) sortFilter.value = sortParam;
      }

      // Load products
      loadProducts();

      // Event listeners
      if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
          updateURL();
          applyFilters();
        });
      }
      if (sortFilter) {
        sortFilter.addEventListener('change', function() {
          updateURL();
          applyFilters();
        });
      }
    } catch (err) {
      console.error('Error in initShop:', err);
      showError('An error occurred while loading the shop. Please refresh.');
    }
  }

  function populateCategories() {
    if (!categoriesData) return;
    const categories = [...new Set(productsData.all.map(function(p) { return p.categoryId; }))];
    categories.forEach(function(catId) {
      const cat = categoriesData.find(function(c) { return c.id === catId; });
      if (cat && categoryFilter) {
        const option = document.createElement('option');
        option.value = catId;
        option.textContent = cat.name;
        categoryFilter.appendChild(option);
      }
    });
  }

  function updateURL() {
    if (!categoryFilter || !sortFilter) return;
    const category = categoryFilter.value;
    const sort = sortFilter.value;
    let params = [];
    if (category && category !== 'all') params.push('category=' + category);
    if (sort && sort !== 'default') params.push('sort=' + sort);
    const newUrl = window.location.pathname + (params.length ? '?' + params.join('&') : '');
    window.history.replaceState({}, '', newUrl);
  }

  function loadProducts() {
    currentProducts = productsData.all;
    applyFilters();
  }

  function applyFilters() {
    if (!categoryFilter || !sortFilter) return;
    const category = parseInt(categoryFilter.value) || 'all';
    const sort = sortFilter.value;

    let filtered = category === 'all' ? currentProducts : currentProducts.filter(function(p) {
      return p.categoryId === category;
    });

    switch (sort) {
      case 'price-asc':
        filtered.sort(function(a, b) { return a.price - b.price; });
        break;
      case 'price-desc':
        filtered.sort(function(a, b) { return b.price - a.price; });
        break;
      case 'rating':
        filtered.sort(function(a, b) { return (b.rating || 0) - (a.rating || 0); });
        break;
      case 'newest':
        filtered.sort(function(a, b) { return b.id - a.id; });
        break;
      default:
        filtered.sort(function(a, b) { return a.id - b.id; });
        break;
    }

    filteredProducts = filtered;
    if (productCount) productCount.textContent = filteredProducts.length;
    currentPage = 1;
    renderProducts();
    renderPagination();
  }

  function renderProducts() {
    console.log('renderProducts called, filteredProducts length:', filteredProducts.length);
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageItems = filteredProducts.slice(start, end);

    if (pageItems.length === 0) {
      productsGrid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px 0;color:var(--text-muted);">' +
        '<p style="font-size:1.2rem;">No products found</p>' +
        '<p>Try adjusting your filters</p>' +
        '</div>';
      return;
    }

    const gradients = [
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
    pageItems.forEach(function(p, index) {
      var bg = gradients[index % gradients.length];
      var oldPriceHtml = p.oldPrice ? '<span class="old-price">ETB ' + p.oldPrice.toFixed(2) + '</span>' : '';
      var badgeHtml = p.badge ? '<span class="product-badge">' + p.badge + '</span>' : '';
      var ratingHtml = p.rating ? '<div style="font-size:0.8rem;color:var(--gold);margin-bottom:6px;">' + '★'.repeat(Math.round(p.rating)) + ' (' + (p.reviews || 0) + ')</div>' : '';
      var imageHtml = p.image && p.image.startsWith('http')
        ? '<img src="' + p.image + '" alt="' + p.name + '" style="width:100%;height:100%;object-fit:cover;" />'
        : '<span class="product-placeholder" style="font-size:3.5rem;">' + (p.image || '📦') + '</span>';

      html += '<div class="product-card reveal" data-product-id="' + p.id + '">' +
        '<a href="product.php?id=' + p.id + '" class="product-link" style="text-decoration:none;color:inherit;display:block;">' +
        '<div class="product-img-wrap" style="background:' + bg + ';">' +
        imageHtml +
        badgeHtml +
        '</div>' +
        '<div class="product-body">' +
        '<h4>' + p.name + '</h4>' +
        ratingHtml +
        '<div class="product-price">' + oldPriceHtml + ' ETB ' + p.price.toFixed(2) + '</div>' +
        '</div>' +
        '</a>' +
        '<div style="padding:0 20px 20px;">' +
        '<button class="btn btn-outline btn-sm add-to-cart-btn" data-product-id="' + p.id + '" style="width:100%;">Add to Cart</button>' +
        '</div>' +
        '</div>';
    });
    productsGrid.innerHTML = html;

    // Add to cart buttons
    productsGrid.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var productId = parseInt(this.dataset.productId);
        var product = getProductById(productId);
        if (product) {
          var success = addToCart(product.id, 1, 'Default');
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
        }
      });
    });
  }

  function renderPagination() {
    var totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
    if (totalPages <= 1) {
      paginationEl.innerHTML = '';
      return;
    }

    var html = '';
    html += '<button class="page-btn" data-page="prev" ' + (currentPage === 1 ? 'disabled' : '') + '>‹</button>';
    for (var i = 1; i <= totalPages; i++) {
      html += '<button class="page-btn ' + (i === currentPage ? 'active' : '') + '" data-page="' + i + '">' + i + '</button>';
    }
    html += '<button class="page-btn" data-page="next" ' + (currentPage === totalPages ? 'disabled' : '') + '>›</button>';
    paginationEl.innerHTML = html;

    paginationEl.querySelectorAll('.page-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        if (this.disabled) return;
        var page = this.dataset.page;
        if (page === 'prev' && currentPage > 1) {
          currentPage--;
        } else if (page === 'next' && currentPage < totalPages) {
          currentPage++;
        } else if (!isNaN(page)) {
          currentPage = parseInt(page);
        } else {
          return;
        }
        renderProducts();
        renderPagination();
        productsGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }

  function showError(message) {
    if (productsGrid) {
      productsGrid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px 0;color:#e05757;">' +
        '<p style="font-size:1.2rem;">⚠️ ' + message + '</p>' +
        '<p>Please try refreshing the page.</p>' +
        '<button onclick="window.location.reload()" style="padding:10px 20px;margin-top:10px;background:var(--gold);color:#12100d;border:none;border-radius:8px;cursor:pointer;">Refresh Page</button>' +
        '</div>';
    }
  }

  function updateHeader() {
    if (window.SASIDA && window.SASIDA.auth && window.SASIDA.auth.updateHeader) {
      window.SASIDA.auth.updateHeader();
    }
  }

  // ─── INITIALIZE ──────────────────────────────────
  // Wait for DOM to be ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      initShop();
      updateHeader();
      updateCartBadge();
    });
  } else {
    initShop();
    updateHeader();
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