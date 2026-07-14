<?php
require_once __DIR__ . '/config/db.php';

// Set your admin email and new password
$email = 'admin@sasida.com';
$new_password = 'admin123';

// Hash the password
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

try {
    // First, check if admin exists
    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Update password
        $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $update->execute([$password_hash, $email]);

        echo "✅ Password updated successfully!\n\n";
        echo "📧 Email: admin@sasida.com\n";
        echo "🔑 Password: admin123\n\n";
        echo "Try logging in now at: http://localhost/your-project/login.php\n";
    } else {
        // Create new admin user
        $insert = $pdo->prepare("
            INSERT INTO users (full_name, email, phone, password_hash, role, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $insert->execute([
            'Admin User',
            $email,
            '+251 911 234 567',
            $password_hash,
            'admin'
        ]);

        echo "✅ Admin user created successfully!\n\n";
        echo "📧 Email: admin@sasida.com\n";
        echo "🔑 Password: admin123\n\n";
        echo "Try logging in now!\n";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Make sure your database connection is working.\n";
}
