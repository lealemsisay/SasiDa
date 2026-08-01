<?php
/* Quick verification script to test admin login */

require_once __DIR__ . '/config/db.php';

try {
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT id, email, password_hash, role FROM users WHERE email = 'admin@sasida.com'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "✓ Admin user found!\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Role: " . $admin['role'] . "\n";
        echo "Password Hash: " . substr($admin['password_hash'], 0, 20) . "...\n\n";
        
        // Test password verification
        $test_password = 'admin123';
        if (password_verify($test_password, $admin['password_hash'])) {
            echo "✓ Password 'admin123' is CORRECT!\n";
            echo "\n✓ You can now login with:\n";
            echo "   Email: admin@sasida.com\n";
            echo "   Password: admin123\n";
        } else {
            echo "✗ Password 'admin123' is INCORRECT\n";
            echo "Trying to generate correct hash...\n";
            $correct_hash = password_hash('admin123', PASSWORD_DEFAULT);
            echo "Use password: admin123\n";
            echo "Hash: " . $correct_hash . "\n";
        }
    } else {
        echo "✗ Admin user not found! Please run: php db_setup.php\n";
    }
    
    // Also check customer
    $stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE email = 'customer@sasida.com'");
    $stmt->execute();
    $customer = $stmt->fetch();
    
    if ($customer) {
        echo "\n✓ Customer user also exists!\n";
        echo "   Email: customer@sasida.com\n";
        echo "   Password: customer123\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}
?>
