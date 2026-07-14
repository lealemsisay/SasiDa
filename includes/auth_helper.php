<?php
/* ═══════════════════════════════════════════════
   SASIDA — auth_helper.php
   Authentication, session management, and security helper functions
   ═══════════════════════════════════════════════ */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

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

function get_js_products_data()
{
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM products WHERE status = 'visible'");
        $products = $stmt->fetchAll();
        $formatted = [];
        foreach ($products as $p) {
            $formatted[] = [
                'id' => intval($p['id']),
                'name' => $p['name'],
                'price' => floatval($p['price']),
                'oldPrice' => $p['old_price'] ? floatval($p['old_price']) : null,
                'image' => $p['image'],
                'badge' => $p['badge'],
                'categoryId' => intval($p['category_id']),
                'rating' => floatval($p['rating']),
                'reviews' => intval($p['reviews_count']),
                'description' => $p['description'],
                'images' => [$p['image']],
                'variants' => $p['variants'] ? json_decode($p['variants']) : [],
                'inStock' => $p['in_stock'] ? true : false
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
            INNER JOIN products p ON p.category_id = c.id AND p.status = 'visible'
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

function get_safe_return_url($default = 'dashboard.php')
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
    return $return;
}
