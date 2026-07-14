/* ═══════════════════════════════════════════════
   SASIDA — product.js
   Product detail page logic (no cart)
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

  // ─── GET DATA FROM LOCALSTORAGE ──────────────
  function getProducts() {
    return JSON.parse(localStorage.getItem('sasida_products') || '[]');
  }

  function getCategories() {
    return JSON.parse(localStorage.getItem('sasida_categories') || '[]');
  }

  function getCategoryById(id) {
    var categories = getCategories();
    for (var i = 0; i < categories.length; i++) {
      if (categories[i].id === parseInt(id)) {
        return categories[i];
      }
    }
    return null;
  }

  function getProductById(id) {
    var products = getProducts();
    for (var i = 0; i < products.length; i++) {
      if (products[i].id === parseInt(id)) {
        return products[i];
      }
    }
    return null;
  }

  function getProductsByCategory(categoryId) {
    var products = getProducts();
    return products.filter(function(p) { return p.categoryId === parseInt(categoryId); });
  }

  // ─── LOADER ────────────────────────────────────
  function hideLoader() {
    if (loader) loader.classList.add('hidden');
  }

  window.addEventListener('load', function() {
    setTimeout(hideLoader, 800);
    setTimeout(hideLoader, 100);
    setTimeout(function() {
      initProductPage();
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

  function updateHeader() {
    if (window.SASIDA && window.SASIDA.auth && window.SASIDA.auth.updateHeader) {
      window.SASIDA.auth.updateHeader();
    }
  }

  // ─── PRODUCT PAGE LOGIC ──────────────────────

  function initProductPage() {
    var urlParams = new URLSearchParams(window.location.search);
    var productId = urlParams.get('id');

    if (!productId) {
      window.location.href = 'shop.php';
      return;
    }

    var product = getProductById(productId);
    if (!product) {
      window.location.href = 'shop.php';
      return;
    }

    renderProduct(product);
    renderRelatedProducts(product);
    renderReviews(product);
  }

  function renderProduct(product) {
    var breadcrumbEl = document.getElementById('breadcrumbProduct');
    if (breadcrumbEl) breadcrumbEl.textContent = product.name;

    var mainImage = document.getElementById('mainImage');
    if (mainImage) mainImage.textContent = product.image || '📦';

    var thumbnails = document.getElementById('thumbnails');
    if (thumbnails && product.images && product.images.length > 0) {
      var html = '';
      product.images.forEach(function(img, index) {
        html += '<div class="thumbnail" data-index="' + index + '" style="width:80px;height:80px;background:var(--surface);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:2.5rem;border:2px solid ' + (index === 0 ? 'var(--gold)' : 'var(--border)') + ';cursor:pointer;transition:border-color 0.25s;">' + img + '</div>';
      });
      thumbnails.innerHTML = html;
      thumbnails.querySelectorAll('.thumbnail').forEach(function(el) {
        el.addEventListener('click', function() {
          var img = product.images[this.dataset.index];
          if (mainImage) mainImage.textContent = img;
          thumbnails.querySelectorAll('.thumbnail').forEach(function(t) {
            t.style.borderColor = 'var(--border)';
          });
          this.style.borderColor = 'var(--gold)';
        });
      });
    }

    var badgeEl = document.getElementById('productBadge');
    if (badgeEl) {
      if (product.badge) {
        badgeEl.textContent = product.badge;
        badgeEl.style.display = 'inline-block';
      } else {
        badgeEl.style.display = 'none';
      }
    }

    var nameEl = document.getElementById('productName');
    if (nameEl) nameEl.textContent = product.name;

    var ratingEl = document.getElementById('productRating');
    if (ratingEl) {
      var stars = '★'.repeat(Math.round(product.rating || 0)) + '☆'.repeat(5 - Math.round(product.rating || 0));
      ratingEl.querySelector('span:first-child').textContent = stars;
      var reviewCount = document.getElementById('reviewCount');
      if (reviewCount) reviewCount.textContent = product.reviews || 0;
    }

    var priceEl = document.getElementById('productPrice');
    if (priceEl) priceEl.textContent = 'ETB ' + product.price.toFixed(2);

    var oldPriceEl = document.getElementById('oldPrice');
    if (oldPriceEl) {
      if (product.oldPrice) {
        oldPriceEl.textContent = 'ETB ' + product.oldPrice.toFixed(2);
        oldPriceEl.style.display = 'block';
      } else {
        oldPriceEl.style.display = 'none';
      }
    }

    var descEl = document.getElementById('productDescription');
    if (descEl) descEl.textContent = product.description || 'No description available.';

    var stockEl = document.getElementById('stockStatus');
    if (stockEl) {
      if (product.status === 'outofstock' || product.inStock === false) {
        stockEl.innerHTML = '<span style="color:#e05757;">✗ Out of Stock</span>';
      } else {
        stockEl.innerHTML = '<span style="color:#52c48b;">✓ In Stock</span>';
      }
    }
  }

  function renderRelatedProducts(product) {
    var container = document.getElementById('relatedProducts');
    if (!container) return;

    var related = getProductsByCategory(product.categoryId)
      .filter(function(p) { return p.id !== product.id && p.status !== 'hidden' && p.status !== 'archived'; })
      .slice(0, 4);

    if (related.length === 0) {
      container.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:40px 0;">No related products found.</div>';
      return;
    }

    var gradients = [
      'linear-gradient(135deg,#f5d6c6,#f0b8a0)',
      'linear-gradient(135deg,#c9d4d1,#a3b8b3)',
      'linear-gradient(135deg,#d4c9b0,#b8a88c)',
      'linear-gradient(135deg,#d5c9d4,#b8a8b0)'
    ];

    var html = '';
    related.forEach(function(p, index) {
      var bg = gradients[index % gradients.length];
      var oldPriceHtml = p.oldPrice ? '<span class="old-price">ETB ' + p.oldPrice.toFixed(2) + '</span>' : '';
      var badgeHtml = p.badge ? '<span class="product-badge">' + p.badge + '</span>' : '';
      var imageHtml = p.image && p.image.startsWith('http')
        ? '<img src="' + p.image + '" alt="' + p.name + '" style="width:100%;height:100%;object-fit:cover;" />'
        : '<span class="product-placeholder" style="font-size:3.5rem;">' + (p.image || '📦') + '</span>';
      html += '<div class="product-card reveal">' +
        '<a href="product.php?id=' + p.id + '" style="text-decoration:none;color:inherit;display:block;">' +
        '<div class="product-img-wrap" style="background:' + bg + ';">' +
        imageHtml +
        badgeHtml +
        '</div>' +
        '<div class="product-body">' +
        '<h4>' + p.name + '</h4>' +
        '<div class="product-price">' + oldPriceHtml + ' ETB ' + p.price.toFixed(2) + '</div>' +
        '</div>' +
        '</a>' +
        '</div>';
    });
    container.innerHTML = html;
  }

  function renderReviews(product) {
    var container = document.getElementById('reviewsContainer');
    if (!container) return;

    var reviewCount = product.reviews || 0;
    if (reviewCount === 0) {
      container.innerHTML = '<p style="text-align:center;color:var(--text-muted);padding:20px 0;">No reviews yet. Be the first to review!</p>';
      return;
    }

    var sampleReviews = [
      { name: 'Customer 1', rating: 5, text: 'Excellent product! Highly recommend.', date: '2025-01-10' },
      { name: 'Customer 2', rating: 4, text: 'Good quality, fast shipping.', date: '2025-01-08' },
      { name: 'Customer 3', rating: 5, text: 'Absolutely love it! Will buy again.', date: '2025-01-05' }
    ];

    var reviewsToShow = sampleReviews.slice(0, Math.min(3, reviewCount));
    var html = '';
    reviewsToShow.forEach(function(r) {
      var stars = '★'.repeat(r.rating) + '☆'.repeat(5 - r.rating);
      html += '<div class="review-card" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:24px;margin-bottom:16px;">' +
        '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">' +
        '<strong style="font-size:1rem;">' + r.name + '</strong>' +
        '<span style="color:var(--gold);">' + stars + '</span>' +
        '</div>' +
        '<p style="color:var(--text-muted);font-size:0.9rem;">' + r.text + '</p>' +
        '<span style="font-size:0.75rem;color:var(--text-muted);">Posted on ' + r.date + '</span>' +
        '</div>';
    });
    if (reviewCount > 3) {
      html += '<p style="text-align:center;color:var(--text-muted);font-size:0.85rem;">+ ' + (reviewCount - 3) + ' more reviews</p>';
    }
    container.innerHTML = html;
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