<?php
/* ═══════════════════════════════════════════════
   SASIDA — dashboard.php
   Customer account portal router and shell layout
   ═══════════════════════════════════════════════ */

require_once __DIR__ . '/includes/auth_helper.php';
require_login(); // Ensure user is logged in

$user = get_logged_in_user();

// Retrieve selected tab/page
$page = isset($_GET['page']) ? trim($_GET['page']) : 'summary';
$allowed_pages = ['summary', 'orders', 'wishlist', 'addresses', 'reviews', 'notifications', 'recently_viewed', 'profile', 'settings'];

if (!in_array($page, $allowed_pages)) {
    $page = 'summary';
}

// Handle dynamic AJAX POST endpoints if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];
    $response = ['success' => false, 'message' => 'Invalid action'];

    if ($page === 'wishlist') {
        if ($action === 'add') {
            $prodId = intval($_GET['id'] ?? 0);
            if ($prodId > 0) {
                try {
                    $stmt = $pdo->prepare("INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (?, ?)");
                    $stmt->execute([$user['id'], $prodId]);
                    $response = ['success' => true, 'message' => 'Product added to wishlist'];
                } catch (PDOException $e) {
                    $response = ['success' => false, 'message' => $e->getMessage()];
                }
            }
        } elseif ($action === 'remove') {
            $wishId = intval($_POST['id'] ?? 0);
            if ($wishId > 0) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM wishlists WHERE id = ? AND user_id = ?");
                    $stmt->execute([$wishId, $user['id']]);
                    $response = ['success' => true, 'message' => 'Item removed'];
                } catch (PDOException $e) {
                    $response = ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }
    } elseif ($page === 'addresses') {
        if ($action === 'delete') {
            $addrId = intval($_POST['id'] ?? 0);
            try {
                $stmt = $pdo->prepare("DELETE FROM customer_addresses WHERE id = ? AND user_id = ?");
                $stmt->execute([$addrId, $user['id']]);
                $response = ['success' => true, 'message' => 'Address deleted'];
            } catch (PDOException $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
            }
        }
    } elseif ($page === 'notifications') {
        if ($action === 'read') {
            $notifId = intval($_POST['id'] ?? 0);
            try {
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
                $stmt->execute([$notifId, $user['id']]);
                $response = ['success' => true, 'message' => 'Notification read'];
            } catch (PDOException $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
            }
        } elseif ($action === 'read_all') {
            try {
                $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $response = ['success' => true, 'message' => 'All marked as read'];
            } catch (PDOException $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
            }
        } elseif ($action === 'delete') {
            $notifId = intval($_POST['id'] ?? 0);
            try {
                $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
                $stmt->execute([$notifId, $user['id']]);
                $response = ['success' => true, 'message' => 'Notification deleted'];
            } catch (PDOException $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
            }
        }
    } elseif ($page === 'recently_viewed') {
        if ($action === 'remove') {
            $rvId = intval($_POST['id'] ?? 0);
            try {
                $stmt = $pdo->prepare("DELETE FROM recently_viewed_products WHERE id = ? AND user_id = ?");
                $stmt->execute([$rvId, $user['id']]);
                $response = ['success' => true, 'message' => 'History item removed'];
            } catch (PDOException $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
            }
        } elseif ($action === 'clear') {
            try {
                $stmt = $pdo->prepare("DELETE FROM recently_viewed_products WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $response = ['success' => true, 'message' => 'Browsing history cleared'];
            } catch (PDOException $e) {
                $response = ['success' => false, 'message' => $e->getMessage()];
            }
        }
    }

    echo json_encode($response);
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<!-- ====== MOBILE TOGGLE BAR ====== -->
<div class="mobile-sidebar-toggle" id="sidebarToggle">
  <button aria-label="Toggle Dashboard Sidebar">
    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
  </button>
  <span style="font-weight: 500; font-size: 0.95rem; text-transform: capitalize;"><?php echo str_replace('_', ' ', $page); ?></span>
</div>

<div class="dashboard-container container">
  <!-- ====== SIDEBAR ====== -->
  <aside class="dashboard-sidebar" id="dashboardSidebar">
    <div class="sidebar-user">
      <div class="user-avatar-wrap">
        <?php if (!empty($user['profile_picture'])): ?>
          <img src="<?php echo sanitize($user['profile_picture']); ?>" alt="Avatar" class="user-avatar-img" />
        <?php else: ?>
          👤
        <?php endif; ?>
      </div>
      <div class="user-meta">
        <h4><?php echo sanitize($user['full_name']); ?></h4>
        <span><?php echo sanitize($user['email']); ?></span>
      </div>
    </div>

    <ul class="sidebar-menu">
      <li>
        <a href="dashboard.php?page=summary" class="sidebar-link <?php echo $page === 'summary' ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          Dashboard
        </a>
      </li>
      <li>
        <a href="dashboard.php?page=orders" class="sidebar-link <?php echo $page === 'orders' ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          My Orders
        </a>
      </li>
      <li>
        <a href="dashboard.php?page=wishlist" class="sidebar-link <?php echo $page === 'wishlist' ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          Wishlist
        </a>
      </li>
      <li>
        <a href="dashboard.php?page=addresses" class="sidebar-link <?php echo $page === 'addresses' ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          Saved Addresses
        </a>
      </li>
      <li>
        <a href="dashboard.php?page=reviews" class="sidebar-link <?php echo $page === 'reviews' ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          Reviews & Ratings
        </a>
      </li>
      <li>
        <a href="dashboard.php?page=notifications" class="sidebar-link <?php echo $page === 'notifications' ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          Notifications
        </a>
      </li>
      <li>
        <a href="dashboard.php?page=recently_viewed" class="sidebar-link <?php echo $page === 'recently_viewed' ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Recently Viewed
        </a>
      </li>
      <li>
        <a href="dashboard.php?page=profile" class="sidebar-link <?php echo $page === 'profile' ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Profile
        </a>
      </li>
      <li>
        <a href="dashboard.php?page=settings" class="sidebar-link <?php echo $page === 'settings' ? 'active' : ''; ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          Account Settings
        </a>
      </li>
      <li style="margin-top: 12px; border-top: 1px solid var(--border); padding-top: 12px;">
        <a href="logout.php" class="sidebar-link" style="color: #e74c3c;">
          <svg viewBox="0 0 24 24" fill="none" stroke="#e74c3c"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Logout
        </a>
      </li>
    </ul>
  </aside>

  <!-- ====== MAIN CONTENT PANE ====== -->
  <main class="dashboard-content">
    <?php include __DIR__ . "/views/dashboard_$page.php"; ?>
  </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
