<?php
/* ═══════════════════════════════════════════════
   SASIDA — categories.php
   Browse all product categories
   ═══════════════════════════════════════════════ */

require_once __DIR__ . '/includes/auth_helper.php';

$categories = get_visible_categories();

include __DIR__ . '/includes/header.php';
?>

  <main>

    <!-- ====== BREADCRUMB ====== -->
    <div class="breadcrumb" style="padding:100px 0 10px;background:var(--bg-alt);">
      <div class="container">
        <p style="font-size:0.85rem;color:var(--text-muted);margin:0;">
          <a href="index.php" style="color:var(--gold);text-decoration:none;">Home</a> /
          <span style="color:var(--text);">Categories</span>
        </p>
      </div>
    </div>

    <!-- ====== PAGE HEADER ====== -->
    <section class="page-header" style="padding:20px 0 40px;background:var(--bg-alt);">
      <div class="container">
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:3rem;margin-bottom:8px;">Shop by Category</h1>
        <p style="color:var(--text-muted);">Explore our curated collections and find exactly what you need.</p>
      </div>
    </section>

    <!-- ====== CATEGORIES GRID ====== -->
    <section class="categories section" style="padding-top:0;background:var(--bg);">
      <div class="container">
        <?php if (empty($categories)): ?>
          <div class="empty-state" style="text-align:center;padding:80px 20px;">
            <div style="font-size:3rem;margin-bottom:16px;opacity:0.5;">📂</div>
            <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;margin-bottom:8px;">No Categories Available</h3>
            <p style="color:var(--text-muted);margin-bottom:24px;">Categories will appear here once products are added.</p>
            <a href="shop.php" class="btn btn-primary">Browse Shop</a>
          </div>
        <?php else: ?>
          <div class="categories-page-grid">
            <?php foreach ($categories as $cat): ?>
              <div class="category-page-card reveal">
                <div class="category-page-image" style="background:<?php echo sanitize($cat['color'] ?: '#d4d9d1'); ?>;">
                  <span class="category-icon"><?php echo sanitize($cat['icon'] ?: '📦'); ?></span>
                </div>
                <div class="category-page-body">
                  <h3><?php echo sanitize($cat['name']); ?></h3>
                  <p class="category-product-count">
                    <?php echo intval($cat['product_count']); ?>
                    <?php echo intval($cat['product_count']) === 1 ? 'Product' : 'Products'; ?>
                  </p>
                  <a href="shop.php?category=<?php echo intval($cat['id']); ?>" class="btn btn-outline btn-sm">View Category</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>

  </main>

<?php include __DIR__ . '/includes/footer.php'; ?>
