<?php
// admin/includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
if (!defined('BASE_URL')) {
    define('BASE_URL', '/SASIDA');
}
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">SASIDA</div>
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">☰</button>
    </div>
    <nav class="sidebar-nav">
        <a href="index.php" class="<?php echo $current_page === 'index' ? 'active' : ''; ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>
        <a href="products.php" class="<?php echo $current_page === 'products' ? 'active' : ''; ?>">
            <span class="nav-icon">📦</span> Products
        </a>
        <a href="categories.php" class="<?php echo $current_page === 'categories' ? 'active' : ''; ?>">
            <span class="nav-icon">📂</span> Categories
        </a>
        <a href="orders.php" class="<?php echo $current_page === 'orders' ? 'active' : ''; ?>">
            <span class="nav-icon">🛒</span> Orders
        </a>
        <a href="customers.php" class="<?php echo $current_page === 'customers' ? 'active' : ''; ?>">
            <span class="nav-icon">👥</span> Customers
        </a>
        <a href="reports.php" class="<?php echo $current_page === 'reports' ? 'active' : ''; ?>">
            <span class="nav-icon">📈</span> Reports
        </a>
        <a href="settings.php" class="<?php echo $current_page === 'settings' ? 'active' : ''; ?>">
            <span class="nav-icon">⚙️</span> Store Settings
        </a>
        <a href="profile.php" class="<?php echo $current_page === 'profile' ? 'active' : ''; ?>">
            <span class="nav-icon">👤</span> Profile
        </a>
        <a href="<?php echo BASE_URL; ?>/logout.php" class="logout-link">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </nav>
</aside>