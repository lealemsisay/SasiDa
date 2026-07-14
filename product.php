<?php
/* ═══════════════════════════════════════════════
   SASIDA — product.php
   Product details page
   ═══════════════════════════════════════════════ */

require_once __DIR__ . '/includes/auth_helper.php';

// Record product view dynamically in MySQL if logged in
global $pdo;
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id > 0 && is_logged_in()) {
    try {
        // First verify the product exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        if ($stmt->fetch()) {
            $user_id = $_SESSION['user_id'];
            // Log recently viewed product (insert or update viewed_at)
            $stmt = $pdo->prepare("INSERT INTO recently_viewed_products (user_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP");
            $stmt->execute([$user_id, $product_id]);
        }
    } catch (PDOException $e) {
        // Fail silently
    }
}

include __DIR__ . '/includes/header.php';
?>

  <main>

    <!-- ====== BREADCRUMB ====== -->
    <div class="breadcrumb" style="padding:100px 0 20px;background:var(--bg-alt);">
      <div class="container">
        <p style="font-size:0.85rem;color:var(--text-muted);">
          <a href="index.php" style="color:var(--gold);">Home</a> 
          / <a href="shop.php" style="color:var(--gold);">Shop</a> 
          / <span id="breadcrumbProduct">Product</span>
        </p>
      </div>
    </div>

    <!-- ====== PRODUCT DETAILS ====== -->
    <section class="product-details section" style="padding-top:20px;background:var(--bg-alt);">
      <div class="container">
        <div class="product-details-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:48px;">

          <!-- Product Images -->
          <div class="product-images">
            <div class="main-image" id="mainImage" style="background:var(--surface);border-radius:var(--radius-md);aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;font-size:8rem;border:1px solid var(--border);">
              📦
            </div>
            <div class="thumbnails" id="thumbnails" style="display:flex;gap:12px;margin-top:16px;overflow-x:auto;padding-bottom:8px;">
              <!-- Rendered by JS -->
            </div>
          </div>

          <!-- Product Info -->
          <div class="product-info">
            <div class="product-meta" style="margin-bottom:8px;">
              <span class="product-badge" id="productBadge" style="display:inline-block;margin-bottom:8px;"></span>
              <h1 id="productName" style="font-size:2.2rem;margin-bottom:8px;">Product Name</h1>
              <div id="productRating" style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <span style="color:var(--gold);">★★★★★</span>
                <span style="color:var(--text-muted);font-size:0.85rem;">(<span id="reviewCount">0</span> reviews)</span>
              </div>
              <div class="product-price" id="productPrice" style="font-size:1.6rem;font-weight:500;color:var(--gold);">
                ETB 0.00
              </div>
              <div id="oldPrice" style="font-size:1rem;color:var(--text-muted);text-decoration:line-through;margin-top:-4px;"></div>
            </div>

            <div class="product-description" id="productDescription" style="margin:20px 0;color:var(--text-muted);line-height:1.8;">
              Description goes here.
            </div>

            <!-- Variants -->
            <div class="product-variants" style="margin:20px 0;">
              <div style="margin-bottom:12px;">
                <label style="font-weight:500;display:block;margin-bottom:4px;font-size:0.85rem;">Size</label>
                <select id="variantSize" style="padding:10px 16px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-family:'DM Sans',sans-serif;width:100%;max-width:200px;">
                  <option value="S">Small</option>
                  <option value="M">Medium</option>
                  <option value="L">Large</option>
                </select>
              </div>
              <div>
                <label style="font-weight:500;display:block;margin-bottom:4px;font-size:0.85rem;">Color</label>
                <select id="variantColor" style="padding:10px 16px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text);font-family:'DM Sans',sans-serif;width:100%;max-width:200px;">
                  <option value="Black">Black</option>
                  <option value="White">White</option>
                  <option value="Navy">Navy</option>
                </select>
              </div>
            </div>

            <!-- Add to Cart (Simulated showcase interaction, or wishlist helper) -->
            <div class="product-actions" style="display:flex;align-items:center;gap:16px;margin-top:24px;flex-wrap:wrap;">
              <?php if (is_logged_in()): ?>
                <button id="addToWishlistBtn" class="btn btn-outline" style="flex:1;justify-content:center;min-width:160px;border-color:var(--gold);color:var(--gold);">Add to Wishlist</button>
              <?php else: ?>
                <a href="login.php?return=product.php?id=<?php echo $product_id; ?>" class="btn btn-outline" style="flex:1;justify-content:center;min-width:160px;text-align:center;text-decoration:none;">Login to Wishlist</a>
              <?php endif; ?>
            </div>

            <!-- Stock status -->
            <div id="stockStatus" style="margin-top:16px;font-size:0.9rem;color:var(--text-muted);">
              <span style="color:#52c48b;">✓ In Stock</span>
            </div>
          </div>
        </div>
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

    <!-- ====== REVIEWS SECTION ====== -->
    <section class="reviews section" style="background:var(--bg-alt);padding-top:60px;">
      <div class="container">
        <div class="section-header reveal">
          <span class="section-tag">Customer Feedback</span>
          <h2 class="section-title">Reviews</h2>
        </div>
        <div id="reviewsContainer" style="max-width:760px;margin-inline:auto;">
          <!-- Dynamically populated by product.js or php -->
        </div>
      </div>
    </section>

  </main>

  <!-- Wishlist quick actions handler for product page -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var wishBtn = document.getElementById('addToWishlistBtn');
      if (wishBtn) {
        wishBtn.addEventListener('click', function() {
          var urlParams = new URLSearchParams(window.location.search);
          var prodId = urlParams.get('id');
          if (!prodId) return;

          fetch('dashboard.php?page=wishlist&action=add&id=' + prodId, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            }
          })
          .then(function(res) {
            return res.json();
          })
          .then(function(data) {
            if (data.success) {
              wishBtn.textContent = '✓ In Wishlist';
              wishBtn.style.borderColor = '#52c48b';
              wishBtn.style.color = '#52c48b';
            } else {
              alert(data.message || 'Error adding to wishlist.');
            }
          })
          .catch(function() {
            alert('Failed to update wishlist.');
          });
        });
      }
    });
  </script>

<?php include __DIR__ . '/includes/footer.php'; ?>
