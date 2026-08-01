/* ═══════════════════════════════════════════════
   SASIDA — shop.js
   Product listing page logic with dynamic DB filters & real-time sync
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
  const productsGrid = document.getElementById('shopProductsGrid');
  const paginationEl = document.getElementById('pagination');
  const categoryFilter = document.getElementById('categoryFilter');
  const priceFilter = document.getElementById('priceFilter');
  const sortFilter = document.getElementById('sortFilter');
  const shopSearch = document.getElementById('shopSearch');
  const productCount = document.getElementById('productCount');
  const cartCount = document.getElementById('cartCount');

  // ─── LOADER ────────────────────────────────────
  function hideLoader() {
    if (loader) loader.classList.add('hidden');
  }
  hideLoader();
  setTimeout(hideLoader, 500);

  if (!productsGrid) return;

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

  // ─── SCROLL-TO-TOP & HEADER ───────────────────
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
    const count = cart.reduce((sum, item) => sum + (item.quantity || 0), 0);
    if (cartCount) cartCount.textContent = count;
  }
  function addToCart(productId, quantity, variant) {
    if (!window.SASIDA || !window.SASIDA.auth || !window.SASIDA.auth.isLoggedIn()) {
      const returnUrl = encodeURIComponent(window.location.pathname + window.location.search);
      window.location.href = 'login.php?return=' + returnUrl;
      return false;
    }
    let cart = getCart();
    const existing = cart.find(item => item.id === productId && item.variant === variant);
    const product = getProductById(productId);
    if (existing) {
      existing.quantity += quantity;
    } else {
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
    if (typeof productsData === 'undefined' || !productsData.all) return null;
    return productsData.all.find(p => p.id === parseInt(id));
  }

  // ─── SHOP STATE & FILTERS ──────────────────────
  let currentProducts = [];
  let filteredProducts = [];
  let currentPage = 1;
  const itemsPerPage = 8;

  function initShop() {
    if (typeof productsData === 'undefined') return;

    populateCategories();

    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = urlParams.get('category') || 'all';
    const sortParam = urlParams.get('sort') || 'default';
    const searchParam = urlParams.get('search') || '';

    if (categoryParam !== 'all' && categoryFilter) categoryFilter.value = categoryParam;
    if (sortParam && sortFilter) sortFilter.value = sortParam;
    if (searchParam && shopSearch) shopSearch.value = searchParam;

    loadProducts();

    if (categoryFilter) categoryFilter.addEventListener('change', () => { updateURL(); applyFilters(); });
    if (sortFilter) sortFilter.addEventListener('change', () => { updateURL(); applyFilters(); });
    if (priceFilter) priceFilter.addEventListener('change', () => { updateURL(); applyFilters(); });
    if (shopSearch) shopSearch.addEventListener('input', () => { updateURL(); applyFilters(); });
  }

  function populateCategories() {
    if (typeof categoriesData === 'undefined' || !categoryFilter) return;
    categoryFilter.innerHTML = '<option value="all">All Categories</option>';
    categoriesData.forEach(function(cat) {
      const option = document.createElement('option');
      option.value = cat.id;
      option.textContent = cat.name + ' (' + cat.productCount + ')';
      categoryFilter.appendChild(option);
    });
  }

  function updateURL() {
    if (!categoryFilter || !sortFilter) return;
    const category = categoryFilter.value;
    const sort = sortFilter.value;
    const search = shopSearch ? shopSearch.value.trim() : '';
    let params = [];
    if (category && category !== 'all') params.push('category=' + category);
    if (sort && sort !== 'default') params.push('sort=' + sort);
    if (search) params.push('search=' + encodeURIComponent(search));
    const newUrl = window.location.pathname + (params.length ? '?' + params.join('&') : '');
    window.history.replaceState({}, '', newUrl);
  }

  function loadProducts() {
    currentProducts = (typeof productsData !== 'undefined' && productsData.all) ? productsData.all : [];
    applyFilters();
  }

  function applyFilters() {
    const category = categoryFilter ? categoryFilter.value : 'all';
    const priceRange = priceFilter ? priceFilter.value : 'all';
    const sort = sortFilter ? sortFilter.value : 'default';
    const search = shopSearch ? shopSearch.value.trim().toLowerCase() : '';

    let filtered = currentProducts.slice();

    // 1. Category Filter
    if (category !== 'all') {
      const catId = parseInt(category);
      filtered = filtered.filter(p => p.categoryId === catId);
    }

    // 2. Price Filter
    if (priceRange !== 'all') {
      if (priceRange === '0-2000') filtered = filtered.filter(p => p.price <= 2000);
      else if (priceRange === '2000-5000') filtered = filtered.filter(p => p.price > 2000 && p.price <= 5000);
      else if (priceRange === '5000-10000') filtered = filtered.filter(p => p.price > 5000 && p.price <= 10000);
      else if (priceRange === '10000+') filtered = filtered.filter(p => p.price > 10000);
    }

    // 3. Search Filter
    if (search) {
      filtered = filtered.filter(p => 
        p.name.toLowerCase().includes(search) || 
        (p.description && p.description.toLowerCase().includes(search)) ||
        (p.categoryName && p.categoryName.toLowerCase().includes(search))
      );
    }

    // 4. Sort Filter
    switch (sort) {
      case 'price-asc': filtered.sort((a, b) => a.price - b.price); break;
      case 'price-desc': filtered.sort((a, b) => b.price - a.price); break;
      case 'rating': filtered.sort((a, b) => (b.rating || 0) - (a.rating || 0)); break;
      case 'newest': filtered.sort((a, b) => b.id - a.id); break;
      default: filtered.sort((a, b) => b.id - a.id); break;
    }

    filteredProducts = filtered;
    if (productCount) productCount.textContent = filteredProducts.length;
    currentPage = 1;
    renderProducts();
    renderPagination();
  }

  function renderProducts() {
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const pageItems = filteredProducts.slice(start, end);

    if (pageItems.length === 0) {
      productsGrid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px 0;color:var(--text-muted);">' +
        '<p style="font-size:1.2rem;">No products found</p>' +
        '<p>Try adjusting your search or filters</p>' +
        '</div>';
      return;
    }

    const gradients = [
      'linear-gradient(135deg,#f5d6c6,#f0b8a0)',
      'linear-gradient(135deg,#c9d4d1,#a3b8b3)',
      'linear-gradient(135deg,#d4c9b0,#b8a88c)',
      'linear-gradient(135deg,#d5c9d4,#b8a8b0)',
      'linear-gradient(135deg,#e8d5c4,#d4b8a4)',
      'linear-gradient(135deg,#b5c9d6,#8fb0c4)'
    ];

    var html = '';
    pageItems.forEach(function(p, index) {
      var bg = gradients[index % gradients.length];
      var oldPriceHtml = p.oldPrice ? '<span class="old-price" style="text-decoration:line-through;color:var(--text-muted);font-size:0.85rem;margin-right:6px;">ETB ' + p.oldPrice.toFixed(2) + '</span>' : '';
      var badgeHtml = p.badge ? '<span class="product-badge">' + p.badge + '</span>' : '';
      var ratingHtml = p.rating ? '<div style="font-size:0.8rem;color:var(--gold);margin-bottom:6px;">' + '★'.repeat(Math.round(p.rating)) + ' (' + (p.reviews || 0) + ')</div>' : '';

      var imageHtml;
      if (p.image && p.image.trim() !== '') {
        var clean = p.image.replace(/^\/+/, '');
        imageHtml = '<img src="' + clean + '" alt="' + p.name + '" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';" />' +
                    '<span class="product-placeholder" style="font-size:3.5rem;display:none;">📦</span>';
      } else {
        imageHtml = '<span class="product-placeholder" style="font-size:3.5rem;">📦</span>';
      }

      var stockStatusBadge = '';
      var buttonHtml = '<button class="btn btn-outline btn-sm add-to-cart-btn" data-product-id="' + p.id + '" style="width:100%;">Add to Cart</button>';

      if (p.status === 'outofstock' || !p.inStock) {
        stockStatusBadge = '<span style="display:inline-block;padding:2px 8px;background:#e74c3c;color:#fff;font-size:0.75rem;border-radius:4px;margin-bottom:6px;font-weight:600;">Out of Stock</span>';
        buttonHtml = '<button class="btn btn-outline btn-sm" disabled style="width:100%;opacity:0.6;cursor:not-allowed;">Out of Stock</button>';
      }

      html += '<div class="product-card reveal" data-product-id="' + p.id + '">' +
        '<a href="product.php?id=' + p.id + '" class="product-link">' +
        '<div class="product-img-wrap" style="background:' + bg + ';">' +
        imageHtml +
        badgeHtml +
        '</div>' +
        '<div class="product-body">' +
        '<div class="product-body-top">' +
        '<div class="product-category">' + (p.categoryName || 'General') + '</div>' +
        '<h4>' + p.name + '</h4>' +
        ratingHtml +
        stockStatusBadge +
        '</div>' +
        '<div class="product-body-bottom">' +
        '<div class="product-price">' + oldPriceHtml + ' ETB ' + p.price.toFixed(2) + '</div>' +
        buttonHtml +
        '</div>' +
        '</div>' +
        '</a>' +
        '</div>';
    });
    productsGrid.innerHTML = html;

    // Cart event handlers
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

    var revealElements = document.querySelectorAll('.reveal');
    var observer = new IntersectionObserver(entries => {
      entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    revealElements.forEach(el => observer.observe(el));
  }

  function renderPagination() {
    if (!paginationEl) return;
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
        if (page === 'prev' && currentPage > 1) { currentPage--; }
        else if (page === 'next' && currentPage < totalPages) { currentPage++; }
        else if (!isNaN(page)) { currentPage = parseInt(page); }
        else { return; }
        renderProducts();
        renderPagination();
        productsGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }

  // ─── REAL-TIME DB SYNCHRONIZATION ─────────────
  function syncProductsFromDB() {
    fetch('api/products.php?t=' + Date.now())
      .then(res => res.json())
      .then(data => {
        if (data.success && data.products) {
          var changed = JSON.stringify(data.products) !== JSON.stringify(window.productsData ? window.productsData.all : []);
          if (changed) {
            window.productsData = { all: data.products };
            if (data.categories) {
              window.categoriesData = data.categories;
              populateCategories();
            }
            loadProducts();
          }
        }
      })
      .catch(err => { console.warn('Shop sync warning:', err); });
  }

  // Poll DB every 10 seconds for real-time synchronization
  setInterval(syncProductsFromDB, 10000);

  // ─── INIT ──────────────────────────────────────
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      initShop();
      updateCartBadge();
    });
  } else {
    initShop();
    updateCartBadge();
  }

})();