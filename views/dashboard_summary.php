<?php
/* ═══════════════════════════════════════════════
   SASIDA — dashboard_summary.php
   Dashboard home/welcome summary subview
   ═══════════════════════════════════════════════ */

if (!defined('SASIDA_AUTH_INCLUDED') && !isset($user)) {
    exit;
}

global $pdo;

// Fetch counts
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $total_orders = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND order_status IN ('Pending', 'Confirmed', 'Processing')");
    $stmt->execute([$user['id']]);
    $pending_orders = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND order_status = 'Delivered'");
    $stmt->execute([$user['id']]);
    $delivered_orders = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $wishlist_count = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM customer_addresses WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $addresses_count = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user['id']]);
    $unread_notifs = $stmt->fetchColumn();

    // Fetch recent orders
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 3");
    $stmt->execute([$user['id']]);
    $recent_orders = $stmt->fetchAll();

    // Fetch recently viewed
    $stmt = $pdo->prepare("SELECT rv.id as rv_id, p.* FROM recently_viewed_products rv JOIN products p ON rv.product_id = p.id WHERE rv.user_id = ? ORDER BY rv.viewed_at DESC LIMIT 4");
    $stmt->execute([$user['id']]);
    $recent_viewed = $stmt->fetchAll();
    
    // Fetch recommended (featured products as future-ready placeholder)
    $stmt = $pdo->prepare("SELECT * FROM products WHERE status = 'visible' LIMIT 4");
    $stmt->execute();
    $recommendations = $stmt->fetchAll();
} catch (PDOException $e) {
    $total_orders = $pending_orders = $delivered_orders = $wishlist_count = $addresses_count = $unread_notifs = 0;
    $recent_orders = $recent_viewed = $recommendations = [];
}
?>

<div class="dashboard-welcome">
  <h2>Welcome back, <?php echo sanitize($user['full_name']); ?>!</h2>
  <p style="color: var(--text-muted);">Manage your account, orders, addresses, and wishlist from your dashboard.</p>
</div>

<!-- ====== STATS COUNTERS ====== -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
    </div>
    <div class="stat-info">
      <h3><?php echo $total_orders; ?></h3>
      <p>Total Orders</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="color: #f39c12; background: rgba(243,156,18,0.1);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <div class="stat-info">
      <h3><?php echo $pending_orders; ?></h3>
      <p>Pending Orders</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="color: #2ecc71; background: rgba(46,204,113,0.1);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <div class="stat-info">
      <h3><?php echo $delivered_orders; ?></h3>
      <p>Delivered</p>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="color: #e74c3c; background: rgba(231,76,60,0.1);">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
    </div>
    <div class="stat-info">
      <h3><?php echo $wishlist_count; ?></h3>
      <p>Wishlist Items</p>
    </div>
  </div>
</div>

<!-- ====== QUICK ACTIONS ====== -->
<div class="dashboard-panel" style="padding: 24px;">
  <h3 class="panel-title" style="font-size: 1.4rem; margin-bottom: 16px;">Quick Actions</h3>
  <div style="display: flex; gap: 12px; flex-wrap: wrap;">
    <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
    <a href="dashboard.php?page=orders" class="btn btn-outline">View Orders</a>
    <a href="dashboard.php?page=profile" class="btn btn-outline">Edit Profile</a>
    <a href="dashboard.php?page=addresses" class="btn btn-outline">Manage Addresses</a>
  </div>
</div>

<!-- ====== RECENT ORDERS ====== -->
<div class="dashboard-panel">
  <div class="panel-header">
    <h3 class="panel-title">Recent Orders</h3>
    <a href="dashboard.php?page=orders" style="color: var(--gold); text-decoration: none; font-size: 0.9rem; font-weight: 500;">View All</a>
  </div>
  <?php if (empty($recent_orders)): ?>
    <div class="empty-state">
      <div class="empty-icon">📦</div>
      <h3>No orders yet</h3>
      <p style="margin-bottom: 16px;">You haven't placed any orders with us yet.</p>
      <a href="shop.php" class="btn btn-primary btn-sm">Browse Products</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="dashboard-table">
        <thead>
          <tr>
            <th>Order #</th>
            <th>Date</th>
            <th>Total</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent_orders as $order): ?>
            <tr>
              <td><strong><?php echo sanitize($order['order_number']); ?></strong></td>
              <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
              <td>ETB <?php echo number_format($order['total_amount'], 2); ?></td>
              <td>
                <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                  <?php echo sanitize($order['order_status']); ?>
                </span>
              </td>
              <td>
                <a href="dashboard.php?page=orders&order_id=<?php echo $order['id']; ?>" class="address-btn">View Details</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- ====== RECENTLY VIEWED PRODUCTS ====== -->
<div class="dashboard-panel">
  <h3 class="panel-title" style="margin-bottom: 20px;">Recently Viewed</h3>
  <?php if (empty($recent_viewed)): ?>
    <p style="color: var(--text-muted); font-size: 0.9rem;">You haven't viewed any products recently.</p>
  <?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px;">
      <?php foreach ($recent_viewed as $p): ?>
        <div class="product-card" style="background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px;">
          <a href="product.php?id=<?php echo $p['id']; ?>" style="text-decoration: none; color: inherit;">
            <div style="aspect-ratio: 1/1; background: rgba(255,255,255,0.02); display: flex; align-items: center; justify-content: center; font-size: 3rem; border-radius: var(--radius-sm);">
              <?php echo sanitize($p['image'] ?: '📦'); ?>
            </div>
            <h4 style="font-size: 0.95rem; margin: 8px 0 4px; font-weight: 500; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;"><?php echo sanitize($p['name']); ?></h4>
            <div style="color: var(--gold); font-size: 0.9rem; font-weight: 600;">ETB <?php echo number_format($p['price'], 2); ?></div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
