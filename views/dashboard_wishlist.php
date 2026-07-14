<?php
/* ═══════════════════════════════════════════════
   SASIDA — dashboard_wishlist.php
   Wishlist items subview
   ═══════════════════════════════════════════════ */

if (!defined('SASIDA_AUTH_INCLUDED') && !isset($user)) {
    exit;
}

global $pdo;

try {
    $stmt = $pdo->prepare("SELECT w.id as wish_id, p.*, c.name as category_name FROM wishlists w JOIN products p ON w.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE w.user_id = ?");
    $stmt->execute([$user['id']]);
    $wishlist_items = $stmt->fetchAll();
} catch (PDOException $e) {
    $wishlist_items = [];
}
?>

<div class="panel-header">
  <h3 class="panel-title">My Wishlist</h3>
</div>

<?php if (empty($wishlist_items)): ?>
  <div class="empty-state">
    <div class="empty-icon">🖤</div>
    <h3>Your wishlist is empty</h3>
    <p style="margin-bottom: 24px;">Explore our catalog and save your favorite items to buy them later!</p>
    <a href="shop.php" class="btn btn-primary">Discover Styles</a>
  </div>
<?php else: ?>
  <div class="table-responsive">
    <table class="dashboard-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Category</th>
          <th>Price</th>
          <th>Rating</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($wishlist_items as $item): ?>
          <tr>
            <td style="display: flex; align-items: center; gap: 12px;">
              <span style="font-size: 2.2rem;"><?php echo sanitize($item['image'] ?: '📦'); ?></span>
              <a href="product.php?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: inherit; font-weight: 600;">
                <?php echo sanitize($item['name']); ?>
              </a>
            </td>
            <td><?php echo sanitize($item['category_name'] ?: 'General'); ?></td>
            <td style="color: var(--gold); font-weight: 600;">ETB <?php echo number_format($item['price'], 2); ?></td>
            <td>
              <span style="color: var(--gold);">★</span> <?php echo number_format($item['rating'], 1); ?>
            </td>
            <td style="text-align: right;">
              <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="product.php?id=<?php echo $item['id']; ?>" class="btn btn-primary btn-sm" style="padding: 6px 12px; font-size: 0.8rem;">View Item</a>
                <button class="btn btn-outline btn-sm remove-wishlist-btn" data-id="<?php echo $item['wish_id']; ?>" style="padding: 6px 12px; font-size: 0.8rem; border-color: #e74c3c; color: #e74c3c;">Remove</button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
