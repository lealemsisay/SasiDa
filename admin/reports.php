<?php
// admin/reports.php
require_once __DIR__ . '/../includes/auth_helper.php';
require_admin();
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="admin-main">
    <div class="admin-header">
        <h1>Reports & Analytics</h1>
    </div>

    <div class="dashboard-panel" style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:60px 20px;text-align:center;max-width:600px;margin:40px auto;">
        <div style="font-size:3.5rem;margin-bottom:16px;">📈</div>
        <h2 style="font-family:'Cormorant Garamond',serif;font-size:2rem;margin-bottom:12px;">Reports Module</h2>
        <p style="color:var(--text-muted);font-size:1.05rem;line-height:1.6;margin-bottom:24px;">This module is reserved for a future update.</p>
        <a href="index.php" class="btn btn-primary" style="padding:10px 24px;background:var(--gold);color:#12100d;text-decoration:none;border-radius:6px;font-weight:500;">Back to Dashboard</a>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
