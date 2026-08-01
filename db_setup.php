<?php
/* ═══════════════════════════════════════════════
   SASIDA — db_setup.php
   Database setup, schema migration, and seeding script
   ═══════════════════════════════════════════════ */

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (php_sapi_name() !== 'cli') {
    die('Unauthorized: This script can only be run from the command line.');
}

$host = 'localhost';
$username = 'root';
$password = '';
$db_name = 'sasida';

try {
    // 1. Connect without database to create it if not exists
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database `$db_name` checked/created.<br>";

    // 2. Connect to the specific database
    $pdo->exec("USE `$db_name`");
    echo "Using database `$db_name`.<br>";

    // 3. Drop existing tables to prevent foreign key errors
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $tables = [
        'reviews', 'recently_viewed_products', 'notifications', 'wishlists',
        'order_items', 'orders', 'customer_addresses', 'users',
        'products', 'categories', 'brands', 'settings'
    ];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "Dropped any existing tables.<br>";

    // 4. Create Tables

    // Categories
    $pdo->exec("CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) UNIQUE NOT NULL,
        description TEXT DEFAULT NULL,
        icon VARCHAR(50) DEFAULT NULL,
        color VARCHAR(20) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `categories` created.<br>";

    // Brands
    $pdo->exec("CREATE TABLE brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) UNIQUE NOT NULL,
        description TEXT DEFAULT NULL,
        image VARCHAR(255) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `brands` created.<br>";

    // Products (now with stock & low_stock_threshold)
    $pdo->exec("CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        old_price DECIMAL(10,2) DEFAULT NULL,
        image VARCHAR(255) DEFAULT NULL,
        badge VARCHAR(50) DEFAULT NULL,
        category_id INT DEFAULT NULL,
        rating DECIMAL(3,2) DEFAULT 0.00,
        reviews_count INT DEFAULT 0,
        description TEXT DEFAULT NULL,
        variants TEXT DEFAULT NULL,
        in_stock BOOLEAN DEFAULT TRUE,
        stock INT(11) DEFAULT 0,
        low_stock_threshold INT(11) DEFAULT 5,
        status VARCHAR(50) DEFAULT 'visible',
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `products` created.<br>";

    // Users
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        phone VARCHAR(50) DEFAULT NULL,
        role VARCHAR(50) DEFAULT 'customer',
        profile_picture VARCHAR(255) DEFAULT NULL,
        gender VARCHAR(20) DEFAULT NULL,
        dob DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `users` created.<br>";

    // Customer Addresses
    $pdo->exec("CREATE TABLE customer_addresses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        region VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        sub_city VARCHAR(100) NOT NULL,
        woreda VARCHAR(100) NOT NULL,
        house_number VARCHAR(50) NOT NULL,
        landmark VARCHAR(255) DEFAULT NULL,
        additional_notes TEXT DEFAULT NULL,
        is_default BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `customer_addresses` created.<br>";

    // Orders
    $pdo->exec("CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        user_id INT NOT NULL,
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        total_amount DECIMAL(10,2) NOT NULL,
        order_status VARCHAR(50) DEFAULT 'Pending',
        payment_status VARCHAR(50) DEFAULT 'Pending',
        address_id INT DEFAULT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (address_id) REFERENCES customer_addresses(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `orders` created.<br>";

    // Order Items
    $pdo->exec("CREATE TABLE order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT DEFAULT NULL,
        variant VARCHAR(255) DEFAULT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `order_items` created.<br>";

    // Wishlists
    $pdo->exec("CREATE TABLE wishlists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_product (user_id, product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `wishlists` created.<br>";

    // Notifications
    $pdo->exec("CREATE TABLE notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(50) NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `notifications` created.<br>";

    // Recently Viewed
    $pdo->exec("CREATE TABLE recently_viewed_products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_viewed (user_id, product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `recently_viewed_products` created.<br>";

    // Reviews
    $pdo->exec("CREATE TABLE reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
        review_text TEXT DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_review (user_id, product_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `reviews` created.<br>";

    // Settings
    $pdo->exec("CREATE TABLE settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Table `settings` created.<br>";

    // 5. Seed Data

    // Default currency
    $pdo->exec("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES ('default_currency', 'ETB')");

    // Categories (same as your SQL dump)
    $categories = [
        ['id' => 1, 'name' => 'Cosmetics', 'icon' => '💄', 'color' => '#f7e1d7'],
        ['id' => 2, 'name' => 'Shoes', 'icon' => '👟', 'color' => '#d9e2e8'],
        ['id' => 3, 'name' => "Men's Clothing", 'icon' => '👔', 'color' => '#d4d9d1'],
        ['id' => 4, 'name' => "Women's Clothing", 'icon' => '👗', 'color' => '#f2d7d5'],
        ['id' => 5, 'name' => 'Kids Clothing', 'icon' => '🧸', 'color' => '#d5e3d6'],
        ['id' => 6, 'name' => 'Bags', 'icon' => '👜', 'color' => '#e6d9c8'],
        ['id' => 7, 'name' => 'Perfumes', 'icon' => '🧴', 'color' => '#f0d8d4'],
        ['id' => 8, 'name' => 'Vitamins', 'icon' => '💊', 'color' => '#d1d9d9'],
    ];
    $catStmt = $pdo->prepare("INSERT INTO categories (id, name, icon, color) VALUES (:id, :name, :icon, :color)");
    foreach ($categories as $cat) {
        $catStmt->execute($cat);
    }
    echo "Seeded categories successfully.<br>";

    // Products (now include stock values)
    $products = [
        ['id'=>1,'name'=>'Sample Product 1','price'=>2850,'old_price'=>null,'image'=>'📦','badge'=>null,'category_id'=>1,'rating'=>4.5,'reviews_count'=>12,'description'=>'This is a premium sample product.','variants'=>json_encode(['Size: S, M, L','Color: Black, White']),'in_stock'=>1,'stock'=>25,'low_stock_threshold'=>5,'status'=>'visible'],
        ['id'=>2,'name'=>'Sample Product 2','price'=>4200,'old_price'=>null,'image'=>'📦','badge'=>null,'category_id'=>2,'rating'=>4.2,'reviews_count'=>8,'description'=>'Another great sample product.','variants'=>json_encode(['Size: 38-44','Color: Brown, Black']),'in_stock'=>1,'stock'=>10,'low_stock_threshold'=>5,'status'=>'visible'],
        ['id'=>3,'name'=>'Sample Product 3','price'=>3850,'old_price'=>5250,'image'=>'📦','badge'=>null,'category_id'=>3,'rating'=>4.8,'reviews_count'=>20,'description'=>'Premium quality with a classic design.','variants'=>json_encode(['Size: S, M, L, XL','Color: Navy, Grey']),'in_stock'=>1,'stock'=>15,'low_stock_threshold'=>5,'status'=>'visible'],
        ['id'=>4,'name'=>'Sample Product 4','price'=>7350,'old_price'=>null,'image'=>'📦','badge'=>null,'category_id'=>4,'rating'=>4.0,'reviews_count'=>15,'description'=>'Elegant and timeless.','variants'=>json_encode(['One Size','Color: Beige, Black']),'in_stock'=>1,'stock'=>8,'low_stock_threshold'=>5,'status'=>'visible'],
        ['id'=>5,'name'=>'Sample Best Seller 1','price'=>1450,'old_price'=>null,'image'=>'⭐','badge'=>'Best Seller','category_id'=>1,'rating'=>4.9,'reviews_count'=>45,'description'=>'Our most popular product!','variants'=>json_encode(['Size: One Size','Color: Gold, Silver']),'in_stock'=>1,'stock'=>30,'low_stock_threshold'=>5,'status'=>'visible'],
        ['id'=>6,'name'=>'Sample Best Seller 2','price'=>6200,'old_price'=>null,'image'=>'⭐','badge'=>'Best Seller','category_id'=>2,'rating'=>4.7,'reviews_count'=>32,'description'=>'Highly rated by our customers.','variants'=>json_encode(['Size: 39-45','Color: Black, White']),'in_stock'=>1,'stock'=>12,'low_stock_threshold'=>5,'status'=>'visible'],
        ['id'=>7,'name'=>'Sample Best Seller 3','price'=>3990,'old_price'=>null,'image'=>'⭐','badge'=>'Best Seller','category_id'=>5,'rating'=>4.6,'reviews_count'=>28,'description'=>'A customer favorite.','variants'=>json_encode(['Size: S, M, L','Color: Pink, Blue']),'in_stock'=>1,'stock'=>20,'low_stock_threshold'=>5,'status'=>'visible'],
        ['id'=>8,'name'=>'Sample Best Seller 4','price'=>2300,'old_price'=>null,'image'=>'⭐','badge'=>'Best Seller','category_id'=>6,'rating'=>4.3,'reviews_count'=>19,'description'=>'Great value for money.','variants'=>json_encode(['One Size','Color: Red, Black']),'in_stock'=>1,'stock'=>18,'low_stock_threshold'=>5,'status'=>'visible'],
        ['id'=>9,'name'=>'Sample New Arrival 1','price'=>1450,'old_price'=>null,'image'=>'✨','badge'=>'New','category_id'=>7,'rating'=>4.4,'reviews_count'=>10,'description'=>'The latest addition to our collection.','variants'=>json_encode(['Size: One Size','Color: Clear, Amber']),'in_stock'=>1,'stock'=>22,'low_stock_threshold'=>5,'status'=>'visible'],
        ['id'=>10,'name'=>'Sample New Arrival 2','price'=>6200,'old_price'=>null,'image'=>'✨','badge'=>'New','category_id'=>8,'rating'=>4.1,'reviews_count'=>7,'description'=>'Fresh off the shelf.','variants'=>json_encode(['Size: 39-44','Color: White, Grey']),'in_stock'=>1,'stock'=>6,'low_stock_threshold'=>5,'status'=>'visible'],
        ['id'=>11,'name'=>'Sample New Arrival 3','price'=>3990,'old_price'=>null,'image'=>'✨','badge'=>'New','category_id'=>3,'rating'=>4.5,'reviews_count'=>14,'description'=>'Trendy and stylish.','variants'=>json_encode(['Size: S, M, L','Color: Black, White']),'in_stock'=>1,'stock'=>9,'low_stock_threshold'=>5,'status'=>'visible'],
        ['id'=>12,'name'=>'Sample New Arrival 4','price'=>2300,'old_price'=>null,'image'=>'✨','badge'=>'New','category_id'=>4,'rating'=>4.2,'reviews_count'=>9,'description'=>'Just arrived!','variants'=>json_encode(['One Size','Color: Gold, Silver']),'in_stock'=>1,'stock'=>14,'low_stock_threshold'=>5,'status'=>'visible'],
    ];
    $prodStmt = $pdo->prepare("INSERT INTO products (id, name, price, old_price, image, badge, category_id, rating, reviews_count, description, variants, in_stock, stock, low_stock_threshold, status) 
                               VALUES (:id, :name, :price, :old_price, :image, :badge, :category_id, :rating, :reviews_count, :description, :variants, :in_stock, :stock, :low_stock_threshold, :status)");
    foreach ($products as $prod) {
        $prodStmt->execute($prod);
    }
    echo "Seeded products successfully.<br>";

    // Users (admin and customer with correct hashes for 'admin123' and 'customer123')
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $customerHash = password_hash('customer123', PASSWORD_DEFAULT);
    $userStmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, phone, role) VALUES (:full_name, :email, :password_hash, :phone, :role)");
    $userStmt->execute([
        'full_name' => 'SASIDA Admin',
        'email' => 'admin@sasida.com',
        'password_hash' => $adminHash,
        'phone' => '+251911000000',
        'role' => 'admin'
    ]);
    $userStmt->execute([
        'full_name' => 'John Doe',
        'email' => 'customer@sasida.com',
        'password_hash' => $customerHash,
        'phone' => '+251911123456',
        'role' => 'customer'
    ]);
    echo "Seeded default users successfully.<br>";

    echo "<strong>Database configuration completed successfully!</strong>";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}