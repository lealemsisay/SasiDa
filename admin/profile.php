<?php require_once 'includes/auth.php'; ?>
<?php include 'includes/header.php';
include 'includes/sidebar.php'; ?>
<main class="admin-main">
    <div class="admin-header">
        <h1>My Profile</h1>
    </div>
    <div class="dashboard-panel">
        <p>Profile editing coming soon.</p>
        <p><strong>Name:</strong> <?php echo sanitize($user['full_name']); ?></p>
        <p><strong>Email:</strong> <?php echo sanitize($user['email']); ?></p>
    </div>
</main>
<?php include 'includes/footer.php'; ?>