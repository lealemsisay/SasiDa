<?php
/* ═══════════════════════════════════════════════
   SASIDA — product.php
   Dedicated Product Details Page & Gallery
   ═══════════════════════════════════════════════ */

$page_script = 'product.js'; // Ensures product.js is loaded by footer

require_once __DIR__ . '/includes/auth_helper.php';

global $pdo;
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;

if ($product_id > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ? AND p.status IN ('visible', 'outofstock')
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        // Record view if logged in
        if ($product && is_logged_in()) {
            $user_id = $_SESSION['user_id'];
            $stmtView = $pdo->prepare("INSERT INTO recently_viewed_products (user_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP");
            $stmtView->execute([$user_id, $product_id]);
        }
    } catch (PDOException $e) {
        $product = null;
    }
}

// Extract images
$imgList = [];
if ($product) {
    if (!empty($product['images'])) {
        $decoded = json_decode($product['images'], true);
        if (is_array($decoded)) {
            $imgList = array_values(array_filter($decoded));
        }
    }
    if (empty($imgList) && !empty($product['image'])) {
        $imgList = [$product['image']];
    }
}
$primaryImage = !empty($imgList) ? $imgList[0] : null;

include __DIR__ . '/includes/header.php';
?>

  <main>

    <!-- ====== BREADCRUMB ====== -->
    <div class="breadcrumb" style="padding:100px 0 20px;background:var(--bg-alt);">
      <div class="container">
        <p style="font-size:0.85rem;color:var(--text-muted);">
          <a href="index.php" style="color:var(--gold);">Home</a> 
          / <a href="shop.php" style="color:var(--gold);">Shop</a> 
          / <span id="breadcrumbProduct"><?php echo $product ? sanitize($product['name']) : 'Product Details'; ?></span>
        </p>
      </div>
    </div>

    <!-- ====== PRODUCT DETAILS ====== -->
    <section class="product-details section" style="padding-top:20px;background:var(--bg-alt);">
      <div class="container">
        <?php if (!$product): ?>
          <div style="text-align:center;padding:80px 20px;">
            <h2 style="font-family:'Cormorant Garamond',serif;font-size:2.4rem;margin-bottom:16px;">Product Not Found</h2>
            <p style="color:var(--text-muted);margin-bottom:24px;">The product you are looking for does not exist or is currently unavailable.</p>
            <a href="shop.php" class="btn btn-primary">Browse Shop</a>
          </div>
        <?php else: ?>
          <div class="product-details-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start;">

            <!-- Product Images Gallery -->
            <div class="product-images">
              <div class="main-image" id="mainImage" style="background:var(--surface);border-radius:var(--radius-md);aspect-ratio:1/1;max-height:480px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border);overflow:hidden;position:relative;">
                <?php if ($primaryImage): ?>
                  <?php $cleanPrimary = ltrim($primaryImage, '/'); ?>
                  <img src="<?php echo sanitize($cleanPrimary); ?>" alt="<?php echo sanitize($product['name']); ?>" style="width:100%;height:100%;object-fit:cover;" id="mainDisplayImg" onerror="this.style.display='none';this.parentElement.textContent='📦';">
                <?php else: ?>
                  <span style="font-size:8rem;">📦</span>
                <?php endif; ?>
              </div>

              <!-- Additional Image Gallery Thumbnails -->
              <div class="thumbnails" id="thumbnails" style="display:flex;gap:12px;margin-top:16px;overflow-x:auto;padding-bottom:8px;<?php echo count($imgList) <= 1 ? 'display:none;' : ''; ?>">
                <?php foreach ($imgList as $idx => $img): ?>
                  <?php $cleanImg = ltrim($img, '/'); ?>
                  <div class="thumbnail <?php echo $idx === 0 ? 'active' : ''; ?>" data-src="<?php echo sanitize($cleanImg); ?>" style="width:80px;height:80px;background:var(--surface);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;border:2px solid <?php echo $idx === 0 ? 'var(--gold)' : 'var(--border)'; ?>;cursor:pointer;flex-shrink:0;overflow:hidden;transition:border-color 0.2s;">
                    <img src="<?php echo sanitize($cleanImg); ?>" style="width:100%;height:100%;object-fit:cover;" alt="Thumbnail <?php echo $idx+1; ?>">
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Product Info -->
            <div class="product-info">
              <div class="product-meta" style="margin-bottom:16px;">
                <div id="productCategory" style="font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;color:var(--gold);margin-bottom:8px;">
                  Category: <?php echo sanitize($product['category_name'] ?? 'General'); ?>
                </div>

                <?php if (!empty($product['badge'])): ?>
                  <span class="product-badge" id="productBadge" style="display:inline-block;margin-bottom:12px;background:var(--gold);color:#12100d;font-size:0.75rem;font-weight:600;padding:4px 12px;border-radius:100px;text-transform:uppercase;">
                    <?php echo sanitize($product['badge']); ?>
                  </span>
                <?php endif; ?>

                <h1 id="productName" style="font-size:2.4rem;margin-bottom:12px;line-height:1.2;font-family:'Cormorant Garamond',serif;">
                  <?php echo sanitize($product['name']); ?>
                </h1>

                <div id="productRating" style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                  <span style="color:var(--gold);font-size:1.1rem;">
                    <?php 
                      $ratingVal = round(floatval($product['rating'])); 
                      echo str_repeat('★', $ratingVal) . str_repeat('☆', 5 - $ratingVal);
                    ?>
                  </span>
                  <span style="color:var(--text-muted);font-size:0.85rem;">(<span id="reviewCount"><?php echo intval($product['reviews_count']); ?></span> customer reviews)</span>
                </div>

                <div style="display:flex;align-items:baseline;gap:12px;margin-bottom:20px;">
                  <div class="product-price" id="productPrice" style="font-size:2rem;font-weight:600;color:var(--gold);">
                    ETB <?php echo number_format(floatval($product['price']), 2); ?>
                  </div>
                  <?php if (!empty($product['old_price']) && floatval($product['old_price']) > floatval($product['price'])): ?>
                    <div id="oldPrice" style="font-size:1.2rem;color:var(--text-muted);text-decoration:line-through;">
                      ETB <?php echo number_format(floatval($product['old_price']), 2); ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Stock & Availability Status -->
              <div id="stockStatus" style="margin-bottom:24px;font-size:0.95rem;">
                <?php if ($product['status'] === 'outofstock' || intval($product['stock']) <= 0): ?>
                  <span style="color:#e05757;font-weight:600;padding:6px 14px;background:rgba(224,87,87,0.1);border:1px solid #e05757;border-radius:6px;display:inline-block;">✗ Out of Stock</span>
                <?php else: ?>
                  <span style="color:#52c48b;font-weight:600;padding:6px 14px;background:rgba(82,196,139,0.1);border:1px solid #52c48b;border-radius:6px;display:inline-block;">✓ In Stock (<?php echo intval($product['stock']); ?> available)</span>
                <?php endif; ?>
              </div>

              <!-- Full Product Description -->
              <div style="margin-bottom:32px;">
                <h3 style="font-size:1.1rem;margin-bottom:8px;border-bottom:1px solid var(--border);padding-bottom:6px;">Product Description</h3>
                <div class="product-description" id="productDescription" style="color:var(--text-muted);line-height:1.8;white-space:pre-line;font-size:0.95rem;">
                  <?php echo sanitize($product['description'] ?: 'No detailed description available for this item.'); ?>
                </div>
              </div>

              <!-- Product Actions -->
              <div class="product-actions" style="display:flex;align-items:center;gap:16px;margin-top:24px;flex-wrap:wrap;">
                <?php if ($product['status'] === 'visible' && intval($product['stock']) > 0): ?>
                  <button id="addToCartBtn" class="btn btn-primary" data-product-id="<?php echo $product['id']; ?>" style="flex:1;justify-content:center;min-width:180px;padding:14px 28px;">Add to Cart</button>
                <?php else: ?>
                  <button class="btn btn-outline" disabled style="flex:1;justify-content:center;min-width:180px;opacity:0.6;cursor:not-allowed;">Out of Stock</button>
                <?php endif; ?>

                <?php if (is_logged_in()): ?>
                  <button id="addToWishlistBtn" class="btn btn-outline" style="min-width:160px;justify-content:center;border-color:var(--gold);color:var(--gold);">Add to Wishlist</button>
                <?php else: ?>
                  <a href="login.php?return=product.php?id=<?php echo $product_id; ?>" class="btn btn-outline" style="min-width:160px;justify-content:center;text-align:center;text-decoration:none;">Login to Wishlist</a>
                <?php endif; ?>
              </div>

            </div>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- ====== RELATED PRODUCTS ====== -->
    <section class="related-products section" style="background:var(--bg);padding-top:60px;">
      <div class="container">
        <div class="section-header reveal">
          <span class="section-tag">You May Also Like</span>
          <h2 class="section-title">Related Products</h2>
        </div>
        <div class="products-grid" id="relatedProducts">
          <!-- Rendered by JavaScript -->
        </div>
      </div>
    </section>

  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Wishlist handler
      var wishBtn = document.getElementById('addToWishlistBtn');
      if (wishBtn) {
        wishBtn.addEventListener('click', function() {
          var prodId = <?php echo $product_id; ?>;
          if (!prodId) return;

          fetch('dashboard.php?page=wishlist&action=add&id=' + prodId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              wishBtn.textContent = '✓ In Wishlist';
              wishBtn.style.borderColor = '#52c48b';
              wishBtn.style.color = '#52c48b';
            } else {
              alert(data.message || 'Error adding to wishlist.');
            }
          })
          .catch(() => alert('Failed to update wishlist.'));
        });
      }
    });
  </script>

<?php include __DIR__ . '/includes/footer.php'; ?>
