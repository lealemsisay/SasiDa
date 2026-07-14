<?php
/* ═══════════════════════════════════════════════
   SASIDA — dashboard_recently_viewed.php
   Recently viewed browsing history subview
   ═══════════════════════════════════════════════ */

if (!defined('SASIDA_AUTH_INCLUDED') && !isset($user)) {
    exit;
}

global $pdo;

try {
    $stmt = $pdo->prepare("SELECT rv.id as rv_id, rv.viewed_at, p.*, c.name as category_name FROM recently_viewed_products rv JOIN products p ON rv.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE rv.user_id = ? ORDER BY rv.viewed_at DESC");
    $stmt->execute([$user['id']]);
    $history_items = $stmt->fetchAll();
} catch (PDOException $e) {
    $history_items = [];
}
?>

<div class="panel-header">
  <h3 class="panel-title">Recently Viewed</h3>
  <?php if (!empty($history_items)): ?>
    <button id="clearHistory" class="address-btn" style="color: #e74c3c; font-weight: 600;">Clear History</button>
  <?php endif; ?>
</div>

<?php if (empty($history_items)): ?>
  <div class="empty-state">
    <div class="empty-icon">⏳</div>
    <h3>No browsing history</h3>
    <p>Products you browse will automatically appear here so you can find them again easily.</p>
  </div>
<?php else: ?>
  <div class="table-responsive">
    <table class="dashboard-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Category</th>
          <th>Price</th>
          <th>Viewed Date</th>
          <th style="text-align: right;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($history_items as $item): ?>
          <tr>
            <td style="display: flex; align-items: center; gap: 12px;">
              <span style="font-size: 2.2rem;"><?php echo sanitize($item['image'] ?: '📦'); ?></span>
              <a href="product.php?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: inherit; font-weight: 600;">
                <?php echo sanitize($item['name']); ?>
              </a>
            </td>
            <td><?php echo sanitize($item['category_name'] ?: 'General'); ?></td>
            <td style="color: var(--gold); font-weight: 600;">ETB <?php echo number_format($item['price'], 2); ?></td>
            <td><?php echo date('M d, Y H:i', strtotime($item['viewed_at'])); ?></td>
            <td style="text-align: right;">
              <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="product.php?id=<?php echo $item['id']; ?>" class="btn btn-outline btn-sm" style="padding: 6px 12px; font-size: 0.8rem;">View</a>
                <button class="address-btn delete remove-history-btn" data-id="<?php echo $item['rv_id']; ?>">Remove</button>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
