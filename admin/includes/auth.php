<?php
// admin/includes/auth.php
// Admin authentication check

// Verify user is logged in
if (!is_logged_in()) {
    header("Location: /SASIDA/login.php?return=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Verify user is admin
if (!is_admin()) {
    header("Location: /SASIDA/index.php");
    exit;
}