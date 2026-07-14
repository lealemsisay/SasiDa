<?php
// admin/api/inventory.php
require_once __DIR__ . '/../../includes/auth_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_admin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    $stock = intval($data['stock'] ?? -1);

    if (!$id || $stock < 0) {
        echo json_encode(['error' => 'Valid ID and stock quantity required']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $stmt->execute([$stock, $id]);
    echo json_encode(['message' => 'Stock updated successfully']);
    exit;
}
