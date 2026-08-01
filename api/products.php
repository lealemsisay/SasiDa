<?php
/* ═══════════════════════════════════════════════
   SASIDA — api/products.php
   Public REST API for products & real-time sync
   ═══════════════════════════════════════════════ */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../includes/auth_helper.php';

$action = $_GET['action'] ?? 'all';

if ($action === 'get') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Product ID required.']);
        exit;
    }
    
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ? AND p.status IN ('visible', 'outofstock')
        ");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        
        if ($p) {
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

            $product = [
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
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found or inactive.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
    exit;
}

// Default action: return all visible/outofstock products and categories
$products = get_js_products_data();
$categories = get_js_categories_data();

echo json_encode([
    'success' => true,
    'timestamp' => time(),
    'products' => $products,
    'categories' => $categories
]);
