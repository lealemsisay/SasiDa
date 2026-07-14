<?php
/* ═══════════════════════════════════════════════
   SASIDA — dashboard_reviews.php
   Customer reviews & ratings manager subview
   ═══════════════════════════════════════════════ */

if (!defined('SASIDA_AUTH_INCLUDED') && !isset($user)) {
    exit;
}

global $pdo;

$action = isset($_GET['sub_action']) ? trim($_GET['sub_action']) : 'list';
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$error = '';
$success = '';

// ─── VALIDATE USER CAN REVIEW PRODUCT ─────────
function can_user_review_product($userId, $prodId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status = 'Delivered'");
        $stmt->execute([$userId, $prodId]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// ─── PROCESS REVIEW SUBMISSIONS ──────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'submit' && $productId > 0) {
        $rating = intval($_POST['rating'] ?? 0);
        $reviewText = trim($_POST['reviewText'] ?? '');

        if (!can_user_review_product($user['id'], $productId)) {
            $error = 'You can only review products that you have purchased and have been delivered.';
        } elseif ($rating < 1 || $rating > 5) {
            $error = 'Please select a rating between 1 and 5 stars.';
        } else {
            try {
                // Upsert review (Insert or Update if already exists)
                $stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, rating, review_text, status) VALUES (?, ?, ?, ?, 'Pending') ON DUPLICATE KEY UPDATE rating = ?, review_text = ?, status = 'Pending'");
                $stmt->execute([$user['id'], $productId, $rating, $reviewText, $rating, $reviewText]);
                
                // Recalculate product rating placeholder
                $stmt = $pdo->prepare("SELECT AVG(rating), COUNT(*) FROM reviews WHERE product_id = ? AND status = 'Approved'");
                $stmt->execute([$productId]);
                $stats = $stmt->fetch();
                if ($stats) {
                    $avg = round($stats['AVG(rating)'], 2) ?: 4.0;
                    $cnt = $stats['COUNT(*)'];
                    $stmtUpdate = $pdo->prepare("UPDATE products SET rating = ?, reviews_count = ? WHERE id = ?");
                    $stmtUpdate->execute([$avg, $cnt, $productId]);
                }

                $success = 'Your review has been submitted and is pending admin approval.';
                $action = 'list';
            } catch (PDOException $e) {
                $error = 'Failed to submit review: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $reviewId = intval($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
            $stmt->execute([$reviewId, $user['id']]);
            $success = 'Review deleted successfully.';
            $action = 'list';
        } catch (PDOException $e) {
            $error = 'Failed to delete review.';
        }
    }
}

// Fetch user's purchased & delivered products
try {
    $stmt = $pdo->prepare("SELECT DISTINCT p.*, c.name as category_name FROM order_items oi JOIN orders o ON oi.order_id = o.id JOIN products p ON oi.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE o.user_id = ? AND o.order_status = 'Delivered'");
    $stmt->execute([$user['id']]);
    $purchased_products = $stmt->fetchAll();
} catch (PDOException $e) {
    $purchased_products = [];
}

// Fetch user's reviews
try {
    $stmt = $pdo->prepare("SELECT r.*, p.name as product_name, p.image as product_image FROM reviews r JOIN products p ON r.product_id = p.id WHERE r.user_id = ?");
    $stmt->execute([$user['id']]);
    $my_reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $my_reviews = [];
}

// Index reviews by product_id
$reviewed_product_ids = [];
$reviews_by_product = [];
foreach ($my_reviews as $r) {
    $reviewed_product_ids[] = $r['product_id'];
    $reviews_by_product[$r['product_id']] = $r;
}
?>

<?php if ($action === 'write' && $productId > 0):
    // --- WRITE/EDIT REVIEW VIEW ---
    if (!can_user_review_product($user['id'], $productId)):
        echo '<div class="dashboard-panel"><p style="color:#e74c3c;">Review eligibility check failed. You must purchase this item first.</p></div>';
    else:
        try {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
        } catch (PDOException $e) {
            $product = null;
        }

        $existing_review = isset($reviews_by_product[$productId]) ? $reviews_by_product[$productId] : null;
        $rating_val = $existing_review ? $existing_review['rating'] : 5;
        $text_val = $existing_review ? $existing_review['review_text'] : '';
        ?>
        <div class="panel-header">
          <h3 class="panel-title"><?php echo $existing_review ? 'Edit Review' : 'Write a Review'; ?></h3>
          <a href="dashboard.php?page=reviews" class="address-btn">← Cancel</a>
        </div>

        <form class="dashboard-form" action="dashboard.php?page=reviews&sub_action=submit&product_id=<?php echo $product['id']; ?>" method="POST">
          <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
            <span style="font-size: 3.5rem;"><?php echo sanitize($product['image'] ?: '📦'); ?></span>
            <div>
              <h4 style="font-size: 1.1rem; margin: 0;"><?php echo sanitize($product['name']); ?></h4>
              <p style="color: var(--text-muted); font-size: 0.85rem; margin: 4px 0 0;">Verify your review below.</p>
            </div>
          </div>

          <div class="form-group">
            <label>Rating (1–5 Stars) *</label>
            <select name="rating" required style="max-width: 150px;">
              <option value="5" <?php echo $rating_val === 5 ? 'selected' : ''; ?>>★★★★★ (5 Stars)</option>
              <option value="4" <?php echo $rating_val === 4 ? 'selected' : ''; ?>>★★★★☆ (4 Stars)</option>
              <option value="3" <?php echo $rating_val === 3 ? 'selected' : ''; ?>>★★★☆☆ (3 Stars)</option>
              <option value="2" <?php echo $rating_val === 2 ? 'selected' : ''; ?>>★★☆☆☆ (2 Stars)</option>
              <option value="1" <?php echo $rating_val === 1 ? 'selected' : ''; ?>>★☆☆☆☆ (1 Star)</option>
            </select>
          </div>

          <div class="form-group">
            <label for="reviewText">Review Comments *</label>
            <textarea id="reviewText" name="reviewText" rows="5" required placeholder="Write your feedback about this product here..."><?php echo sanitize($text_val); ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary" style="align-self: flex-start;">Submit Review</button>
        </form>
      <?php endif; ?>

<?php else: ?>
    <!-- --- REVIEWS DASHBOARD LIST VIEW --- -->
    <div class="panel-header">
      <h3 class="panel-title">Reviews & Ratings</h3>
    </div>

    <?php if (!empty($success)): ?>
      <div style="background: #2ecc71; color: #fff; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.95rem;">
        <?php echo sanitize($success); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div style="background: #e74c3c; color: #fff; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.95rem;">
        <?php echo sanitize($error); ?>
      </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: start; margin-top: 10px;">
      <!-- Left column: Products eligible to review -->
      <div class="dashboard-panel" style="padding: 24px;">
        <h4 style="font-size: 1.1rem; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">Eligible to Review</h4>
        <?php if (empty($purchased_products)): ?>
          <p style="color: var(--text-muted); font-size: 0.9rem;">You haven't purchased or received any products yet. Review option becomes available once your order status is set to Delivered.</p>
        <?php else: ?>
          <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($purchased_products as $p): 
              $has_reviewed = in_array($p['id'], $reviewed_product_ids);
            ?>
              <li style="display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                  <span style="font-size: 2rem;"><?php echo sanitize($p['image'] ?: '📦'); ?></span>
                  <div>
                    <strong style="font-size: 0.9rem; display: block;"><?php echo sanitize($p['name']); ?></strong>
                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo sanitize($p['category_name'] ?: 'General'); ?></span>
                  </div>
                </div>
                <div>
                  <a href="dashboard.php?page=reviews&sub_action=write&product_id=<?php echo $p['id']; ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem; padding: 6px 12px;">
                    <?php echo $has_reviewed ? 'Edit Review' : 'Write Review'; ?>
                  </a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>

      <!-- Right column: My Submitted Reviews -->
      <div class="dashboard-panel" style="padding: 24px;">
        <h4 style="font-size: 1.1rem; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">My Submitted Reviews</h4>
        <?php if (empty($my_reviews)): ?>
          <p style="color: var(--text-muted); font-size: 0.9rem;">You haven't submitted any reviews yet.</p>
        <?php else: ?>
          <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($my_reviews as $r): 
              $stars = str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']);
              $status_color = '#f39c12'; // pending
              if ($r['status'] === 'Approved') $status_color = '#2ecc71';
              if ($r['status'] === 'Rejected') $status_color = '#e74c3c';
            ?>
              <div style="border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                  <span style="font-size: 1.5rem;"><?php echo sanitize($r['product_image'] ?: '📦'); ?></span>
                  <div>
                    <strong style="font-size: 0.9rem;"><?php echo sanitize($r['product_name']); ?></strong>
                    <div style="color: var(--gold); font-size: 0.8rem;"><?php echo $stars; ?></div>
                  </div>
                  <span style="margin-left: auto; font-size: 0.75rem; font-weight: 600; color: <?php echo $status_color; ?>; text-transform: uppercase;">
                    <?php echo sanitize($r['status']); ?>
                  </span>
                </div>
                <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; margin: 0 0 8px;">
                  "<?php echo sanitize($r['review_text']); ?>"
                </p>
                <div style="display: flex; gap: 12px;">
                  <a href="dashboard.php?page=reviews&sub_action=write&product_id=<?php echo $r['product_id']; ?>" style="color: var(--gold); font-size: 0.8rem; text-decoration: none; font-weight: 500;">Edit</a>
                  <form action="dashboard.php?page=reviews&sub_action=delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete this review?');">
                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>" />
                    <button type="submit" style="background:none; border:none; color:#e74c3c; font-size:0.8rem; cursor:pointer; padding:0; font-weight:500; font-family:inherit;">Delete</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
<?php endif; ?>
