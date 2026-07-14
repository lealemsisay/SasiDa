<?php
/* ═══════════════════════════════════════════════
   SASIDA — dashboard_orders.php
   Order list and order details subview
   ═══════════════════════════════════════════════ */

if (!defined('SASIDA_AUTH_INCLUDED') && !isset($user)) {
    exit;
}

global $pdo;

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id > 0):
    // --- ORDER DETAILS VIEW ---
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $user['id']]);
        $order = $stmt->fetch();
    } catch (PDOException $e) {
        $order = null;
    }

    if (!$order):
        echo '<div class="dashboard-panel"><p style="color: #e74c3c;">Order not found.</p><a href="dashboard.php?page=orders" class="btn btn-outline btn-sm">Back to Orders</a></div>';
    else:
        // Fetch order items
        try {
            $stmt = $pdo->prepare("SELECT oi.*, p.name, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            $stmt->execute([$order['id']]);
            $items = $stmt->fetchAll();
        } catch (PDOException $e) {
            $items = [];
        }

        // Fetch address details
        try {
            $address = null;
            if ($order['address_id']) {
                $stmt = $pdo->prepare("SELECT * FROM customer_addresses WHERE id = ?");
                $stmt->execute([$order['address_id']]);
                $address = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $address = null;
        }

        // Order Status Steps definition
        $steps = ['Pending', 'Confirmed', 'Processing', 'Ready for Delivery', 'Shipped', 'Delivered'];
        $current_status = $order['order_status'];
        if ($current_status === 'Cancelled') {
            $steps = ['Pending', 'Cancelled'];
        }
        $current_index = array_search($current_status, $steps);
        if ($current_index === false) $current_index = 0;
        ?>
        <div class="panel-header">
          <h3 class="panel-title">Order #<?php echo sanitize($order['order_number']); ?> Details</h3>
          <a href="dashboard.php?page=orders" class="address-btn">← Back to List</a>
        </div>

        <!-- ====== ORDER STATUS TIMELINE ====== -->
        <div class="dashboard-panel" style="padding: 24px; margin-bottom: 30px;">
          <h4 style="font-size: 1.1rem; margin-bottom: 24px;">Order Status Timeline</h4>
          <div style="display: flex; justify-content: space-between; position: relative; padding-bottom: 12px; flex-wrap: wrap; gap: 16px;">
            <?php 
            // Draw horizontal bar on desktop
            if ($current_status !== 'Cancelled'):
            ?>
              <div style="position: absolute; top: 18px; left: 10%; right: 10%; height: 4px; background: var(--border); z-index: 1;">
                <div style="height: 100%; width: <?php echo ($current_index / (count($steps) - 1)) * 100; ?>%; background: var(--gold); transition: width 0.5s ease;"></div>
              </div>
            <?php endif; ?>

            <?php foreach ($steps as $idx => $step): 
              $is_active = $idx <= $current_index;
              $is_current = $idx === $current_index;
              $step_color = $is_active ? 'var(--gold)' : 'var(--text-muted)';
              $dot_bg = $is_active ? 'var(--gold)' : 'var(--surface)';
              $dot_color = $is_active ? '#12100d' : 'var(--text-muted)';
            ?>
              <div style="display: flex; flex-direction: column; align-items: center; text-align: center; width: 14%; min-width: 90px; z-index: 2;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: <?php echo $dot_bg; ?>; color: <?php echo $dot_color; ?>; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid <?php echo $is_active ? 'var(--gold)' : 'var(--border)'; ?>; font-size: 0.9rem; margin-bottom: 8px;">
                  <?php echo $idx + 1; ?>
                </div>
                <span style="font-size: 0.8rem; font-weight: <?php echo $is_current ? 'bold' : 'normal'; ?>; color: <?php echo $step_color; ?>;">
                  <?php echo $step; ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start;">
          <!-- Left: Order Items list -->
          <div class="dashboard-panel" style="padding: 24px;">
            <h4 style="font-size: 1.1rem; margin-bottom: 20px;">Items in Order</h4>
            <div class="table-responsive">
              <table class="dashboard-table">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Variant</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($items as $item): ?>
                    <tr>
                      <td style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 2rem;"><?php echo sanitize($item['image'] ?: '📦'); ?></span>
                        <strong><?php echo sanitize($item['name']); ?></strong>
                      </td>
                      <td><?php echo sanitize($item['variant'] ?: 'None'); ?></td>
                      <td>ETB <?php echo number_format($item['unit_price'], 2); ?></td>
                      <td><?php echo $item['quantity']; ?></td>
                      <td><strong>ETB <?php echo number_format($item['subtotal'], 2); ?></strong></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px; margin-top: 24px; border-top: 1px solid var(--border); padding-top: 16px;">
              <p style="font-size: 0.95rem; color: var(--text-muted);">Subtotal: <span style="color: var(--text); font-weight: 500;">ETB <?php echo number_format($order['total_amount'], 2); ?></span></p>
              <p style="font-size: 1.2rem; font-weight: bold; color: var(--gold);">Total: ETB <?php echo number_format($order['total_amount'], 2); ?></p>
            </div>
          </div>

          <!-- Right: Shipping / Customer Details -->
          <div class="dashboard-panel" style="padding: 24px; display: flex; flex-direction: column; gap: 20px;">
            <div>
              <h4 style="font-size: 1.1rem; margin-bottom: 12px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">Order Details</h4>
              <p style="font-size: 0.85rem; margin-bottom: 6px;"><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></p>
              <p style="font-size: 0.85rem; margin-bottom: 6px;"><strong>Payment Status:</strong> <?php echo sanitize($order['payment_status']); ?></p>
              <p style="font-size: 0.85rem; margin-bottom: 6px;"><strong>Shipment Status:</strong> <?php echo sanitize($order['order_status']); ?></p>
            </div>

            <div>
              <h4 style="font-size: 1.1rem; margin-bottom: 12px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">Delivery Address</h4>
              <?php if ($address): ?>
                <p style="font-size: 0.85rem; font-weight: bold; margin-bottom: 4px;"><?php echo sanitize($address['full_name']); ?></p>
                <p style="font-size: 0.85rem; margin-bottom: 4px;"><?php echo sanitize($address['phone']); ?></p>
                <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">
                  <?php echo sanitize($address['region']); ?>, <?php echo sanitize($address['city']); ?><br>
                  <?php echo sanitize($address['sub_city']); ?>, Woreda <?php echo sanitize($address['woreda']); ?><br>
                  House: <?php echo sanitize($address['house_number']); ?>
                  <?php if ($address['landmark']): ?><br>Landmark: <?php echo sanitize($address['landmark']); ?><?php endif; ?>
                </p>
              <?php else: ?>
                <p style="font-size: 0.85rem; color: var(--text-muted);">No shipping address details linked to order.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

<?php else: ?>
    <!-- --- ORDERS LIST VIEW --- -->
    <div class="panel-header">
      <h3 class="panel-title">My Orders</h3>
    </div>

    <?php
    try {
        $stmt = $pdo->prepare("SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count FROM orders o WHERE o.user_id = ? ORDER BY o.order_date DESC");
        $stmt->execute([$user['id']]);
        $orders = $stmt->fetchAll();
    } catch (PDOException $e) {
        $orders = [];
    }
    ?>

    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <div class="empty-icon">📦</div>
        <h3>No orders placed yet</h3>
        <p style="margin-bottom: 24px;">Your order history is empty. Start shopping to create your first order!</p>
        <a href="shop.php" class="btn btn-primary">Browse Products</a>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="dashboard-table">
          <thead>
            <tr>
              <th>Order Number</th>
              <th>Order Date</th>
              <th>Total (ETB)</th>
              <th>Items Count</th>
              <th>Order Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td><strong><?php echo sanitize($order['order_number']); ?></strong></td>
                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                <td>ETB <?php echo number_format($order['total_amount'], 2); ?></td>
                <td><?php echo $order['items_count']; ?></td>
                <td>
                  <span class="status-badge status-<?php echo strtolower($order['order_status']); ?>">
                    <?php echo sanitize($order['order_status']); ?>
                  </span>
                </td>
                <td>
                  <a href="dashboard.php?page=orders&order_id=<?php echo $order['id']; ?>" class="btn btn-outline btn-sm" style="padding: 6px 12px; font-size: 0.8rem;">Track & View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
<?php endif; ?>
