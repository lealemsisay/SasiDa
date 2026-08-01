<?php
/* Test page to verify session after login */
require_once __DIR__ . '/includes/auth_helper.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f0f0f0; }
        .box { background: white; padding: 20px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #007bff; }
        .error { border-left-color: #dc3545; }
        .success { border-left-color: #28a745; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔐 Login Status Test</h1>
    
    <?php if (is_logged_in()): ?>
        <div class="box success">
            <h3>✓ You are logged in!</h3>
            <p><strong>User ID:</strong> <code><?php echo $_SESSION['user_id']; ?></code></p>
            <p><strong>Email:</strong> <code><?php echo $_SESSION['user_email']; ?></code></p>
            <p><strong>Name:</strong> <code><?php echo $_SESSION['user_name']; ?></code></p>
            <p><strong>Role:</strong> <code><?php echo $_SESSION['user_role']; ?></code></p>
            
            <?php if (is_admin()): ?>
                <p style="color: green; font-weight: bold;">✓ You are an ADMIN</p>
                <p><a href="admin/index.php">→ Go to Admin Dashboard</a></p>
            <?php else: ?>
                <p style="color: blue; font-weight: bold;">✓ You are a CUSTOMER</p>
                <p><a href="dashboard.php">→ Go to Customer Dashboard</a></p>
            <?php endif; ?>
            
            <hr>
            <p><a href="logout.php">← Logout</a></p>
        </div>
    <?php else: ?>
        <div class="box error">
            <h3>✗ You are NOT logged in</h3>
            <p>Session data: <code><?php echo json_encode($_SESSION); ?></code></p>
            <hr>
            <p><a href="login.php">← Go to Login</a></p>
        </div>
    <?php endif; ?>
    
    <div class="box">
        <h3>📋 Session Information</h3>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
</body>
</html>
