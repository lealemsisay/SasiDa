<?php
/* ═══════════════════════════════════════════════
   SASIDA — Frontend Footer
   ═══════════════════════════════════════════════ */
$footerSettings = get_all_settings();
$footerStoreName = sanitize($footerSettings['store_name'] ?? 'SASIDA');
$footerLogo = !empty($footerSettings['store_logo']) ? sanitize($footerSettings['store_logo']) : 'sasida-logo.png';
$instaUrl = !empty($footerSettings['contact_instagram_url']) ? sanitize($footerSettings['contact_instagram_url']) : 'https://instagram.com';
$tiktokUrl = !empty($footerSettings['contact_tiktok_url']) ? sanitize($footerSettings['contact_tiktok_url']) : 'https://tiktok.com';
?>

    <!-- ====== FOOTER ====== -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="nav-logo" style="text-decoration:none;">
                        <div class="logo-wrapper" style="width:48px;height:48px;">
                            <img src="<?php echo $footerLogo; ?>" alt="<?php echo $footerStoreName; ?>" class="logo-img" onerror="this.src='sasida-logo.png';" />
                        </div>
                        <span class="logo-text"><?php echo $footerStoreName; ?></span>
                    </a>
                    <p>Premium lifestyle essentials — curated for you with authentic quality and fast delivery.</p>
                    <div class="social-links">
                        <a href="<?php echo $instaUrl; ?>" class="social-link" target="_blank" rel="noopener" aria-label="Instagram">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17" cy="7" r="1" fill="currentColor"/></svg>
                        </a>
                        <a href="<?php echo $tiktokUrl; ?>" class="social-link" target="_blank" rel="noopener" aria-label="TikTok">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 2v15a4 4 0 1 0 4 4V2h4v4h-4v11a2 2 0 1 1-4 0V6H9V2z"/></svg>
                        </a>
                    </div>
                </div>
                <div class="footer-col">
                    <h5>Shop</h5>
                    <ul>
                        <li><a href="shop.php">All Products</a></li>
                        <li><a href="categories.php">Categories</a></li>
                        <li><a href="cart.php">Cart</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h5>Account</h5>
                    <ul>
                        <?php if (is_logged_in()): ?>
                            <?php if (is_admin()): ?>
                                <li><a href="admin/index.php">Admin Dashboard</a></li>
                            <?php else: ?>
                                <li><a href="dashboard.php">My Dashboard</a></li>
                            <?php endif; ?>
                            <li><a href="logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="footer-col footer-newsletter">
                    <h5>Contact Us</h5>
                    <p style="margin-bottom:6px;font-size:0.85rem;">📞 <?php echo sanitize($footerSettings['contact_phone'] ?? ''); ?></p>
                    <p style="margin-bottom:6px;font-size:0.85rem;">✉️ <?php echo sanitize($footerSettings['contact_email'] ?? ''); ?></p>
                    <p style="font-size:0.85rem;">📍 <?php echo sanitize($footerSettings['contact_address'] ?? ''); ?></p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo $footerStoreName; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- ====== SCRIPTS ====== -->
    <script src="auth.js"></script>
    <!-- Cache‑bust data.php so new products always appear -->
    <script src="data.php?t=<?php echo time(); ?>"></script>
    <script src="script.js"></script>
    <?php
    // Page-specific scripts with cache‑busting
    if (isset($page_script)) {
        echo '<script src="' . $page_script . '?t=' . time() . '"></script>';
    }
    ?>
</body>
</html>