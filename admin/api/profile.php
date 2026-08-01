<?php
// admin/api/profile.php
require_once __DIR__ . '/../../includes/auth_helper.php';
require_once __DIR__ . '/../../config/db.php';
require_admin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$user = get_logged_in_user();
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($full_name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Full name and email address are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    global $pdo;

    // Fetch existing user with password_hash
    $stmt = $pdo->prepare("SELECT id, password_hash, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $db_user = $stmt->fetch();

    if (!$db_user) {
        echo json_encode(['success' => false, 'message' => 'User record not found.']);
        exit;
    }

    // Check if email is taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user['id']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email address is already in use by another account.']);
        exit;
    }

    // Handle avatar photo upload
    $profile_picture = $db_user['profile_picture'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                $profile_picture = 'uploads/avatars/' . $filename;
            }
        }
    }

    // Handle password update if specified
    $password_hash = $db_user['password_hash'];
    if (!empty($new_password) || !empty($current_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            echo json_encode(['success' => false, 'message' => 'Current password is required to change password.']);
            exit;
        }
        if (!password_verify($current_password, $db_user['password_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Current password verification failed. Incorrect password.']);
            exit;
        }
        if (empty($new_password)) {
            echo json_encode(['success' => false, 'message' => 'Please enter your new password.']);
            exit;
        }
        if (strlen($new_password) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long.']);
            exit;
        }
        if ($new_password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'New password and confirmation password do not match.']);
            exit;
        }
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    }

    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, profile_picture = ?, password_hash = ? WHERE id = ?");
    $stmt->execute([$full_name, $email, $phone, $profile_picture, $password_hash, $user['id']]);

    $_SESSION['user_name'] = $full_name;
    $_SESSION['user_email'] = $email;

    echo json_encode(['success' => true, 'message' => 'Admin profile updated successfully.']);
} catch (PDOException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
