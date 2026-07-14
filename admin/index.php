<?php
// admin/index.php
// Direct includes for safety
require_once __DIR__ . '/../includes/auth_helper.php';
require_once __DIR__ . '/../config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

global $pdo;
$user = get_logged_in_user();

$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('-7 days'));
$month_start = date('Y-m-d', strtotime('-30 days'));

// Sales
$stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE DATE(order_date) = ? AND order_status != 'Cancelled'");
$stmt->execute([$today]);
$today_sales = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE order_date >= ? AND order_status != 'Cancelled'");
$stmt->execute([$week_start]);
$week_sales = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE order_date >= ? AND order_status != 'Cancelled'");
$stmt->execute([$month_start]);
$month_sales = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE order_status != 'Cancelled'");
$total_revenue = $stmt->fetchColumn() ?: 0;

$statuses = ['Pending', 'Confirmed', 'Processing', 'Ready for Delivery', 'Shipped', 'Delivered', 'Cancelled'];
$order_counts = [];
foreach ($statuses as $status) {
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_status = ?");
  $stmt->execute([$status]);
  $order_counts[$status] = $stmt->fetchColumn();
}

$total_orders = array_sum($order_counts);
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$total_customers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$total_products = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'visible'");
$active_products = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'hidden'");
$hidden_products = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'outofstock' OR stock = 0");
$outofstock_products = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'archived'");
$archived_products = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM categories");
$total_categories = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE stock <= low_stock_threshold AND stock > 0");
$stmt->execute();
$low_stock = $stmt->fetchColumn();

$recent_orders = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 5")->fetchAll();
$recent_customers = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recent_reviews = $pdo->query("SELECT r.*, u.full_name, p.name as product_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN products p ON r.product_id = p.id ORDER BY r.created_at DESC LIMIT 5")->fetchAll();

$sales_data = [];
$dates = [];
for ($i = 6; $i >= 0; $i--) {
  $date = date('Y-m-d', strtotime("-$i days"));
  $dates[] = date('M d', strtotime($date));
  $stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE DATE(order_date) = ? AND order_status != 'Cancelled'");
  $stmt->execute([$date]);
  $sales_data[] = $stmt->fetchColumn() ?: 0;
}

$status_labels = ['Pending', 'Confirmed', 'Processing', 'Ready', 'Shipped', 'Delivered', 'Cancelled'];
$status_counts = [];
foreach ($status_labels as $s) {
  $status_counts[] = $order_counts[$s] ?? 0;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="admin-main">
  <div class="admin-header">
    <h1>Dashboard</h1>
    <div class="admin-user">
      <span><?php echo sanitize($user['full_name']); ?></span>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon">💰</div>
      <div>
        <div class="stat-value"><?php echo format_price($today_sales); ?></div>
        <div class="stat-label">Today's Sales</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📅</div>
      <div>
        <div class="stat-value"><?php echo format_price($week_sales); ?></div>
        <div class="stat-label">This Week</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📆</div>
      <div>
        <div class="stat-value"><?php echo format_price($month_sales); ?></div>
        <div class="stat-label">This Month</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">🏷️</div>
      <div>
        <div class="stat-value"><?php echo format_price($total_revenue); ?></div>
        <div class="stat-label">Total Revenue</div>
      </div>
    </div>
  </div>

  <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));">
    <?php foreach ($statuses as $status): ?>
      <div class="stat-card small">
        <div class="stat-value"><?php echo $order_counts[$status]; ?></div>
        <div class="stat-label"><?php echo $status; ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
    <div class="stat-card small">
      <div class="stat-value"><?php echo $total_customers; ?></div>
      <div class="stat-label">Customers</div>
    </div>
    <div class="stat-card small">
      <div class="stat-value"><?php echo $total_products; ?></div>
      <div class="stat-label">Total Products</div>
    </div>
    <div class="stat-card small">
      <div class="stat-value"><?php echo $active_products; ?></div>
      <div class="stat-label">Active</div>
    </div>
    <div class="stat-card small">
      <div class="stat-value"><?php echo $hidden_products; ?></div>
      <div class="stat-label">Hidden</div>
    </div>
    <div class="stat-card small">
      <div class="stat-value"><?php echo $outofstock_products; ?></div>
      <div class="stat-label">Out of Stock</div>
    </div>
    <div class="stat-card small">
      <div class="stat-value"><?php echo $archived_products; ?></div>
      <div class="stat-label">Archived</div>
    </div>
    <div class="stat-card small">
      <div class="stat-value"><?php echo $total_categories; ?></div>
      <div class="stat-label">Categories</div>
    </div>
    <div class="stat-card small">
      <div class="stat-value"><?php echo $low_stock; ?></div>
      <div class="stat-label">Low Stock</div>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">
    <div class="dashboard-panel">
      <h3>Last 7 Days Sales</h3><canvas id="salesChart" height="150"></canvas>
    </div>
    <div class="dashboard-panel">
      <h3>Order Status Distribution</h3><canvas id="statusChart" height="150"></canvas>
    </div>
  </div>

  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px;">
    <div class="dashboard-panel">
      <h3>Recent Orders</h3>
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent_orders as $order): ?>
            <tr>
              <td><?php echo sanitize($order['order_number']); ?></td>
              <td><?php echo format_price($order['total_amount']); ?></td>
              <td><?php echo get_status_badge($order['order_status']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="dashboard-panel">
      <h3>Recent Customers</h3>
      <table class="admin-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Joined</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent_customers as $cust): ?>
            <tr>
              <td><?php echo sanitize($cust['full_name']); ?></td>
              <td><?php echo sanitize($cust['email']); ?></td>
              <td><?php echo date('M d', strtotime($cust['created_at'])); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="dashboard-panel" style="margin-top:20px;">
    <h3>Recent Reviews</h3>
    <table class="admin-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Customer</th>
          <th>Rating</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent_reviews as $review): ?>
          <tr>
            <td><?php echo sanitize($review['product_name']); ?></td>
            <td><?php echo sanitize($review['full_name']); ?></td>
            <td><?php echo $review['rating']; ?> ★</td>
            <td><?php echo $review['status']; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const ctx1 = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx1, {
      type: 'line',
      data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [{
          label: 'Sales (ETB)',
          data: <?php echo json_encode($sales_data); ?>,
          borderColor: '#c9a84c',
          tension: 0.1,
          fill: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });

    const ctx2 = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx2, {
      type: 'pie',
      data: {
        labels: <?php echo json_encode($status_labels); ?>,
        datasets: [{
          data: <?php echo json_encode($status_counts); ?>,
          backgroundColor: ['#f39c12', '#3498db', '#9b59b6', '#1abc9c', '#2ecc71', '#2ecc71', '#e74c3c']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
  });
</script>

<?php include 'includes/footer.php'; ?>