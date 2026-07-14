<?php
/* ═══════════════════════════════════════════════
   SASIDA — shop.php
   Product catalog page
   ═══════════════════════════════════════════════ */

require_once __DIR__ . '/includes/auth_helper.php';
include __DIR__ . '/includes/header.php';
?>

  <main>

    <!-- ====== BREADCRUMB ====== -->
    <div class="breadcrumb" style="padding:100px 0 10px;background:var(--bg-alt);">
      <div class="container">
        <p style="font-size:0.85rem;color:var(--text-muted);margin:0;">
          <a href="index.php" style="color:var(--gold);text-decoration:none;">Home</a> / <span style="color:var(--text);">Shop</span>
        </p>
      </div>
    </div>

    <!-- ====== SHOP HEADER ====== -->
    <section class="page-header" style="padding:20px 0 40px;background:var(--bg-alt);">
      <div class="container">
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:3rem;margin-bottom:8px;">Shop All Products</h1>
        <p style="color:var(--text-muted);">Discover our complete collection</p>
      </div>
    </section>

    <!-- ====== FILTERS & TOOLBAR ====== -->
    <section class="shop-toolbar section" style="padding-block:40px;background:var(--bg);">
      <div class="container">
        <div class="shop-toolbar-inner" style="display:flex;flex-wrap:wrap;gap:16px;justify-content:space-between;align-items:center;">
          <div class="shop-filters" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
            <!-- Search Bar -->
            <input type="text" id="shopSearch" placeholder="Search products..." class="shop-filter" style="width:200px;padding:8px 14px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-family:inherit;font-size:0.9rem;" />
            
            <select id="categoryFilter" class="shop-filter">
              <option value="all">All Categories</option>
            </select>

            <!-- Price Filter -->
            <select id="priceFilter" class="shop-filter">
              <option value="all">All Prices</option>
              <option value="0-2000">Under 2,000 ETB</option>
              <option value="2000-5000">2,000 - 5,000 ETB</option>
              <option value="5000-10000">5,000 - 10,000 ETB</option>
              <option value="10000+">Above 10,000 ETB</option>
            </select>

            <select id="sortFilter" class="shop-filter">
              <option value="default">Sort by: Default</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="rating">Rating</option>
              <option value="newest">Newest</option>
            </select>
          </div>
          <div class="shop-results" style="font-size:0.85rem;color:var(--text-muted);">
            <span id="productCount">0</span> products found
          </div>
        </div>
      </div>
    </section>

    <!-- ====== PRODUCT GRID ====== -->
    <section class="products section" style="padding-top:0;background:var(--bg);">
      <div class="container">
        <div class="products-grid" id="shopProductsGrid">
          <!-- Products will be rendered here -->
        </div>
        <div class="pagination" id="pagination"></div>
      </div>
    </section>

  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
