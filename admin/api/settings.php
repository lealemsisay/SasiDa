<?php
// admin/api/settings.php
require_once __DIR__ . '/../../includes/auth_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_admin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

try {
    $keys = [
        'store_name',
        'contact_phone',
        'contact_email',
        'contact_whatsapp',
        'contact_instagram',
        'contact_instagram_url',
        'contact_tiktok',
        'contact_tiktok_url',
        'contact_address',
        'business_location',
        'business_hours',
        'google_maps_url',
        'about_title',
        'about_tag',
        'about_desc1',
        'about_desc2',
        'about_story',
        'about_mission',
        'about_vision',
        'about_rating'
    ];

    foreach ($keys as $k) {
        if (isset($_POST[$k])) {
            update_setting($k, trim($_POST[$k]));
        }
    }

    // Handle Store Logo Upload if provided
    if (isset($_FILES['store_logo']) && $_FILES['store_logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/settings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['store_logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
            $filename = 'logo_' . time() . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['store_logo']['tmp_name'], $dest)) {
                update_setting('store_logo', 'uploads/settings/' . $filename);
            }
        }
    }

    // Handle Store Banner Upload if provided
    if (isset($_FILES['store_banner']) && $_FILES['store_banner']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/settings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['store_banner']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $filename = 'banner_' . time() . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['store_banner']['tmp_name'], $dest)) {
                update_setting('store_banner', 'uploads/settings/' . $filename);
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Store settings saved successfully.']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
