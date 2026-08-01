<?php
// admin/api/products.php
require_once __DIR__ . '/../../includes/auth_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_admin();

// Ensure images column exists in products table
try {
    $pdo->exec("ALTER TABLE products ADD COLUMN images TEXT DEFAULT NULL AFTER image");
} catch (PDOException $e) {
    // Already exists
}

// Handle GET request for fetching a single product
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Product ID required.']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if ($product) {
        $imgList = [];
        if (!empty($product['images'])) {
            $decoded = json_decode($product['images'], true);
            if (is_array($decoded)) {
                $imgList = array_values(array_filter($decoded));
            }
        }
        if (empty($imgList) && !empty($product['image'])) {
            $imgList = [$product['image']];
        }
        $product['images'] = $imgList;
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
    }
    exit;
}

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Helper: upload single image file array from $_FILES
function uploadSingleProductImage($fileItem) {
    $uploadDir = __DIR__ . '/../../uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if ($fileItem['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload failed with error code: ' . $fileItem['error']];
    }
    $ext = strtolower(pathinfo($fileItem['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Invalid file type. Allowed: JPG, JPEG, PNG, WEBP'];
    }
    if ($fileItem['size'] > $maxSize) {
        return ['error' => 'File too large. Max 5MB.'];
    }
    $filename = uniqid('prod_') . '.' . $ext;
    $dest = $uploadDir . $filename;
    if (!move_uploaded_file($fileItem['tmp_name'], $dest)) {
        return ['error' => 'Failed to save uploaded file.'];
    }
    return ['success' => true, 'path' => 'uploads/products/' . $filename];
}

try {
    $pdo->beginTransaction();

    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $category_id = intval($_POST['category_id'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $status = trim($_POST['status'] ?? 'visible');
            $description = trim($_POST['description'] ?? '');
            $old_price = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;
            $badge = trim($_POST['badge'] ?? '');
            $in_stock = ($stock > 0 && $status === 'visible') ? 1 : 0;

            if (empty($name) || $price <= 0) {
                throw new Exception('Name and price are required.');
            }

            // Handle multiple image uploads (up to 10 images)
            $uploadedPaths = [];

            // Single 'image' file input check
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $up = uploadSingleProductImage($_FILES['image']);
                if (isset($up['success'])) {
                    $uploadedPaths[] = $up['path'];
                }
            }

            // Multiple 'images' file input check
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                $count = count($_FILES['images']['name']);
                for ($i = 0; $i < $count; $i++) {
                    if (count($uploadedPaths) >= 10) break;
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $item = [
                            'name' => $_FILES['images']['name'][$i],
                            'type' => $_FILES['images']['type'][$i],
                            'tmp_name' => $_FILES['images']['tmp_name'][$i],
                            'error' => $_FILES['images']['error'][$i],
                            'size' => $_FILES['images']['size'][$i]
                        ];
                        $up = uploadSingleProductImage($item);
                        if (isset($up['success'])) {
                            $uploadedPaths[] = $up['path'];
                        }
                    }
                }
            }

            $primaryImage = !empty($uploadedPaths) ? $uploadedPaths[0] : null;
            $imagesJson = !empty($uploadedPaths) ? json_encode($uploadedPaths) : null;

            $stmt = $pdo->prepare("
                INSERT INTO products 
                (name, description, price, old_price, category_id, stock, low_stock_threshold, image, images, badge, status, in_stock) 
                VALUES (?, ?, ?, ?, ?, ?, 5, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $description, $price, $old_price, $category_id, $stock, $primaryImage, $imagesJson, $badge, $status, $in_stock]);
            $newId = $pdo->lastInsertId();

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Product created with ' . count($uploadedPaths) . ' image(s).', 'id' => $newId]);
            break;

        case 'edit':
            $id = intval($_POST['id'] ?? 0);
            if (!$id) throw new Exception('Product ID required.');

            $name = trim($_POST['name'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $category_id = intval($_POST['category_id'] ?? 0);
            $stock = intval($_POST['stock'] ?? 0);
            $status = trim($_POST['status'] ?? 'visible');
            $description = trim($_POST['description'] ?? '');
            $old_price = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;
            $badge = trim($_POST['badge'] ?? '');
            $in_stock = ($stock > 0 && $status === 'visible') ? 1 : 0;

            if (empty($name) || $price <= 0) {
                throw new Exception('Name and price are required.');
            }

            // Existing images array preserved by admin
            $existingImages = [];
            if (isset($_POST['existing_images'])) {
                if (is_array($_POST['existing_images'])) {
                    $existingImages = array_values(array_filter($_POST['existing_images']));
                } else if (is_string($_POST['existing_images'])) {
                    $decoded = json_decode($_POST['existing_images'], true);
                    if (is_array($decoded)) {
                        $existingImages = array_values(array_filter($decoded));
                    }
                }
            }

            $newPaths = [];
            // Upload new single image if provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $up = uploadSingleProductImage($_FILES['image']);
                if (isset($up['success'])) {
                    $newPaths[] = $up['path'];
                }
            }

            // Upload new multiple images if provided
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                $count = count($_FILES['images']['name']);
                for ($i = 0; $i < $count; $i++) {
                    if (count($existingImages) + count($newPaths) >= 10) break;
                    if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                        $item = [
                            'name' => $_FILES['images']['name'][$i],
                            'type' => $_FILES['images']['type'][$i],
                            'tmp_name' => $_FILES['images']['tmp_name'][$i],
                            'error' => $_FILES['images']['error'][$i],
                            'size' => $_FILES['images']['size'][$i]
                        ];
                        $up = uploadSingleProductImage($item);
                        if (isset($up['success'])) {
                            $newPaths[] = $up['path'];
                        }
                    }
                }
            }

            $allImages = array_values(array_unique(array_merge($existingImages, $newPaths)));
            $allImages = array_slice($allImages, 0, 10); // Cap at 10 images

            $primaryImage = !empty($allImages) ? $allImages[0] : null;
            $imagesJson = !empty($allImages) ? json_encode($allImages) : null;

            $stmt = $pdo->prepare("
                UPDATE products SET 
                    name = ?, description = ?, price = ?, old_price = ?, 
                    category_id = ?, stock = ?, status = ?, badge = ?, in_stock = ?,
                    image = ?, images = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $price, $old_price, $category_id, $stock, $status, $badge, $in_stock, $primaryImage, $imagesJson, $id]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Product updated. Total images: ' . count($allImages)]);
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if (!$id) throw new Exception('Product ID required.');
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Product deleted.']);
            break;

        case 'status':
            $id = intval($_POST['id'] ?? 0);
            $status = trim($_POST['status'] ?? '');
            if (!$id || !in_array($status, ['visible', 'hidden', 'outofstock', 'archived'])) {
                throw new Exception('Invalid status or ID.');
            }
            $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $currentProd = $stmt->fetch();
            $stockVal = $currentProd ? intval($currentProd['stock']) : 0;
            $in_stock = ($status === 'visible' && $stockVal > 0) ? 1 : 0;

            $stmt = $pdo->prepare("UPDATE products SET status = ?, in_stock = ? WHERE id = ?");
            $stmt->execute([$status, $in_stock, $id]);
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Status updated.']);
            break;

        default:
            throw new Exception('Invalid action.');
    }
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}