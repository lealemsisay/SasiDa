<?php
/* ═══════════════════════════════════════════════
   SASIDA — auth_helper.php
   Authentication, session management, and security helper functions
   ═══════════════════════════════════════════════ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

function ensure_default_admin_account()
{
    global $pdo;
    try {
        if (!isset($pdo)) return;
        $stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE email = ?");
        $stmt->execute(['admin@sasida.com']);
        $admin = $stmt->fetch();

        if (!$admin) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, ?)");
            $ins->execute(['SASIDA Admin', 'admin@sasida.com', $hash, '+251911000000', 'admin']);
        } else if (empty($admin['password_hash']) || $admin['role'] !== 'admin') {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $up = $pdo->prepare("UPDATE users SET password_hash = ?, role = 'admin' WHERE id = ?");
            $up->execute([$hash, $admin['id']]);
        }
    } catch (PDOException $e) {
        // Table not ready yet
    }
}
ensure_default_admin_account();

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function get_logged_in_user()
{
    global $pdo;
    if (!is_logged_in()) {
        return null;
    }
    try {
        $stmt = $pdo->prepare("SELECT id, full_name, email, phone, role, profile_picture, gender, dob, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

function is_admin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function login_user($email, $password)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $user;
        }
    } catch (PDOException $e) {
        return null;
    }
    return null;
}

function logout_user()
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}

function require_login()
{
    if (!is_logged_in()) {
        $return_url = urlencode($_SERVER['REQUEST_URI']);
        header("Location: login.php?return=" . $return_url);
        exit;
    }
}

function require_admin()
{
    require_login();
    if (!is_admin()) {
        header("Location: index.php");
        exit;
    }
}

function get_csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize($data)
{
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

define('SASIDA_AUTH_INCLUDED', true);

// Ensure images column exists in products table
try {
    if (isset($pdo)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN images TEXT DEFAULT NULL AFTER image");
    }
} catch (PDOException $e) {
    // Column already exists
}

function get_js_products_data()
{
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status IN ('visible', 'outofstock')
            ORDER BY p.id DESC
        ");
        $products = $stmt->fetchAll();
        $formatted = [];
        foreach ($products as $p) {
            $inStockBool = ($p['status'] === 'visible' && intval($p['stock']) > 0 && intval($p['in_stock']) === 1);
            
            $imgList = [];
            if (!empty($p['images'])) {
                $decoded = json_decode($p['images'], true);
                if (is_array($decoded)) {
                    $imgList = array_values(array_filter($decoded));
                }
            }
            if (empty($imgList) && !empty($p['image'])) {
                $imgList = [$p['image']];
            }
            $primaryImage = !empty($imgList) ? $imgList[0] : null;

            $formatted[] = [
                'id' => intval($p['id']),
                'name' => $p['name'],
                'price' => floatval($p['price']),
                'oldPrice' => !empty($p['old_price']) ? floatval($p['old_price']) : null,
                'image' => $primaryImage,
                'badge' => $p['badge'] ?? null,
                'categoryId' => intval($p['category_id']),
                'categoryName' => $p['category_name'] ?? 'Uncategorized',
                'rating' => floatval($p['rating']),
                'reviews' => intval($p['reviews_count']),
                'description' => $p['description'] ?? '',
                'images' => $imgList,
                'inStock' => $inStockBool,
                'stock' => intval($p['stock']),
                'status' => $p['status']
            ];
        }
        return $formatted;
    } catch (PDOException $e) {
        return [];
    }
}

function get_js_categories_data()
{
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT c.id, c.name, c.icon, c.color, COUNT(p.id) AS product_count
            FROM categories c
            INNER JOIN products p ON p.category_id = c.id AND p.status IN ('visible', 'outofstock')
            GROUP BY c.id, c.name, c.icon, c.color
            HAVING product_count > 0
            ORDER BY c.name ASC
        ");
        $categories = $stmt->fetchAll();
        $formatted = [];
        foreach ($categories as $c) {
            $formatted[] = [
                'id' => intval($c['id']),
                'name' => $c['name'],
                'icon' => $c['icon'],
                'color' => $c['color'],
                'productCount' => intval($c['product_count'])
            ];
        }
        return $formatted;
    } catch (PDOException $e) {
        return [];
    }
}

function get_visible_categories()
{
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT c.id, c.name, c.icon, c.color, COUNT(p.id) AS product_count
            FROM categories c
            INNER JOIN products p ON p.category_id = c.id AND p.status = 'visible'
            GROUP BY c.id, c.name, c.icon, c.color
            HAVING product_count > 0
            ORDER BY c.name ASC
        ");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function get_safe_return_url($default = 'shop.php')
{
    if (!isset($_GET['return']) || $_GET['return'] === '') {
        return $default;
    }
    $return = $_GET['return'];
    if (strpos($return, '://') !== false || strpos($return, '//') === 0) {
        return $default;
    }
    if (!preg_match('/^[a-zA-Z0-9_\-\.\/?=&%]+$/', $return)) {
        return $default;
    }
    // Prevent redirect loop to login or admin dashboard for normal users
    if (strpos($return, 'login.php') !== false || strpos($return, 'register.php') !== false || strpos($return, 'admin/') !== false) {
        return $default;
    }
    return $return;
}

function get_all_settings()
{
    global $pdo;
    $defaults = [
        'store_name' => 'SASIDA',
        'store_logo' => 'sasida-logo.png',
        'store_banner' => '',
        'contact_phone' => '+251 911 234 567',
        'contact_email' => 'info@sasida.com',
        'contact_whatsapp' => '+251 911 234 567',
        'contact_instagram' => '@sasida_shop',
        'contact_instagram_url' => 'https://instagram.com',
        'contact_tiktok' => '@sasida_shop',
        'contact_tiktok_url' => 'https://tiktok.com',
        'contact_address' => 'Bole Road, Addis Ababa, Ethiopia',
        'business_location' => 'Addis Ababa, Ethiopia',
        'business_hours' => 'Mon–Sat 9:00 AM – 9:00 PM',
        'google_maps_url' => '',
        'about_title' => 'Elevating Everyday Lifestyle',
        'about_tag' => 'Our Story',
        'about_desc1' => 'Founded in 2025, SASIDA was born from a passion for curating the finest products across beauty, fashion, and wellness. We believe that everyone deserves access to premium quality at fair prices.',
        'about_desc2' => 'Our team travels the world to bring you the best – from artisan perfumes to sustainable fashion. Every product is handpicked to ensure it meets our high standards.',
        'about_story' => 'SASIDA is a modern e-commerce brand committed to delivering authentic lifestyle products across East Africa and beyond.',
        'about_mission' => 'To empower everyday lives with premium lifestyle products, authentic quality, and effortless shopping.',
        'about_vision' => 'To become Africa\'s premier online destination for beauty, fashion, and wellness.',
        'about_rating' => '4.9'
    ];

    try {
        if (isset($pdo)) {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            $rows = $stmt->fetchAll();
            foreach ($rows as $r) {
                if ($r['setting_value'] !== null && $r['setting_value'] !== '') {
                    $defaults[$r['setting_key']] = $r['setting_value'];
                }
            }
        }
    } catch (PDOException $e) {
        // Fallback to defaults if table query fails
    }
    return $defaults;
}

function get_setting($key, $default = '')
{
    $settings = get_all_settings();
    return $settings[$key] ?? $default;
}

function update_setting($key, $value)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        return $stmt->execute([$key, $value, $value]);
    } catch (PDOException $e) {
        return false;
    }
}
