<?php
/* ═══════════════════════════════════════════════
   SASIDA — Frontend Header
   ═══════════════════════════════════════════════ */
require_once __DIR__ . '/auth_helper.php';
$siteSettings = get_all_settings();
$siteStoreName = sanitize($siteSettings['store_name'] ?? 'SASIDA');
$siteLogo = !empty($siteSettings['store_logo']) ? sanitize($siteSettings['store_logo']) : 'sasida-logo.png';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
      (function() {
        var theme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', theme);
      })();
    </script>
    <title><?php echo $siteStoreName; ?> — Premium Lifestyle Store</title>
    <!-- Core Styles -->
    <link rel="stylesheet" href="/SASIDA/style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- ====== LOADER ====== -->
    <div class="loader" id="loader">
        <div class="loader-inner">
            <span class="loader-logo"><?php echo $siteStoreName; ?></span>
            <div class="loader-bar">
                <div class="loader-fill"></div>
            </div>
        </div>
    </div>

    <!-- ====== HEADER / NAV ====== -->
    <header class="header" id="header">
        <div class="container nav">
            <!-- Logo -->
            <a href="index.php" class="nav-logo" style="text-decoration:none;">
                <div class="logo-wrapper" id="logoWrapper">
                    <img src="<?php echo $siteLogo; ?>" alt="<?php echo $siteStoreName; ?>" class="logo-img" onerror="this.src='sasida-logo.png';" />
                </div>
                <span class="logo-text"><?php echo $siteStoreName; ?></span>
            </a>

            <!-- Desktop Navigation -->
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php#home" class="nav-link active" data-page="home">Home</a></li>
                <li><a href="shop.php" class="nav-link" data-page="shop">Shop</a></li>
                <li><a href="categories.php" class="nav-link" data-page="categories">Categories</a></li>
                <li><a href="index.php#about" class="nav-link" data-page="about">About</a></li>
                <li><a href="index.php#contact" class="nav-link" data-page="contact">Contact</a></li>
                <span class="nav-underline"></span>
            </ul>

            <!-- Controls -->
            <div class="nav-controls">
                <!-- Theme Toggle -->
                <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
                    <span class="theme-icon moon">🌙</span>
                    <span class="theme-icon sun">☀️</span>
                </button>
                <!-- Cart Icon -->
                <a href="cart.php" class="cart-icon-link" aria-label="Cart">
                    🛒
                    <span class="cart-badge" id="cartCount">0</span>
                </a>
                <!-- Account / Login -->
                <?php if (is_logged_in()): ?>
                  <?php if (is_admin()): ?>
                    <a href="admin/index.php" class="btn btn-outline btn-sm" style="font-size:0.8rem;padding:6px 12px;border-color:var(--gold);color:var(--gold);">Admin</a>
                  <?php else: ?>
                    <a href="dashboard.php" class="btn btn-outline btn-sm" style="font-size:0.8rem;padding:6px 12px;">Account</a>
                  <?php endif; ?>
                <?php else: ?>
                  <a href="login.php" class="btn btn-outline btn-sm" style="font-size:0.8rem;padding:6px 12px;">Login</a>
                <?php endif; ?>

                <!-- Mobile Hamburger -->
                <button class="hamburger" id="hamburger" aria-label="Toggle menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- ====== MOBILE MENU ====== -->
    <div class="mobile-menu" id="mobileMenu">
        <ul>
            <li><a href="index.php#home" class="mob-link" data-page="home">Home</a></li>
            <li><a href="shop.php" class="mob-link" data-page="shop">Shop</a></li>
            <li><a href="categories.php" class="mob-link" data-page="categories">Categories</a></li>
            <li><a href="index.php#about" class="mob-link" data-page="about">About</a></li>
            <li><a href="index.php#contact" class="mob-link" data-page="contact">Contact</a></li>
            <?php if (is_logged_in()): ?>
              <?php if (is_admin()): ?>
                <li><a href="admin/index.php" class="mob-link">Admin Dashboard</a></li>
              <?php else: ?>
                <li><a href="dashboard.php" class="mob-link">My Account</a></li>
              <?php endif; ?>
              <li><a href="logout.php" class="mob-link">Logout</a></li>
            <?php else: ?>
              <li><a href="login.php" class="mob-link">Login</a></li>
              <li><a href="register.php" class="mob-link">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Scroll‑to‑Top Button -->
    <button class="scroll-top" id="scrollTop" aria-label="Scroll to top">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
    </button>

    <!-- ====== MAIN CONTENT STARTS HERE ====== -->