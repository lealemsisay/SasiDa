<?php
// admin/api/categories.php
require_once __DIR__ . '/../../includes/auth_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_admin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Category ID required.']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch();
    if ($cat) {
        echo json_encode(['success' => true, 'category' => $cat]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Category not found.']);
    }
    exit;
}

try {
    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            $icon = trim($_POST['icon'] ?? '📦');
            $color = trim($_POST['color'] ?? '#d4d9d1');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                throw new Exception('Category name is required.');
            }

            $stmt = $pdo->prepare("INSERT INTO categories (name, icon, color, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $icon, $color, $description]);
            $newId = $pdo->lastInsertId();

            echo json_encode(['success' => true, 'message' => 'Category created successfully.', 'id' => $newId]);
            break;

        case 'edit':
        case 'update':
            $id = intval($_POST['id'] ?? 0);
            if (!$id) throw new Exception('Category ID required.');

            $name = trim($_POST['name'] ?? '');
            $icon = trim($_POST['icon'] ?? '📦');
            $color = trim($_POST['color'] ?? '#d4d9d1');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                throw new Exception('Category name is required.');
            }

            $stmt = $pdo->prepare("UPDATE categories SET name = ?, icon = ?, color = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $icon, $color, $description, $id]);

            echo json_encode(['success' => true, 'message' => 'Category updated successfully.']);
            break;

        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if (!$id) throw new Exception('Category ID required.');

            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(['success' => true, 'message' => 'Category deleted successfully.']);
            break;

        default:
            throw new Exception('Invalid category action.');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
