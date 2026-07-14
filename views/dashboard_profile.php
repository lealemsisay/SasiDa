<?php
/* ═══════════════════════════════════════════════
   SASIDA — dashboard_profile.php
   Profile Details Management Subview
   ═══════════════════════════════════════════════ */

if (!defined('SASIDA_AUTH_INCLUDED') && !isset($user)) {
    exit;
}

global $pdo;

$error = '';
$success = '';

// Split full name into first and last name for editing
$name_parts = explode(' ', $user['full_name'], 2);
$first_name = $name_parts[0] ?? '';
$last_name = $name_parts[1] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['firstName'] ?? '');
    $last_name = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $profile_picture = $user['profile_picture']; // Default to current

    // Basic Validations
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error = 'First Name, Last Name, and Email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check email uniqueness against other users
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetch()) {
                $error = 'This email address is already in use by another account.';
            } else {
                // Handle Profile Picture upload if present
                if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['profilePic']['tmp_name'];
                    $fileName = $_FILES['profilePic']['name'];
                    $fileSize = $_FILES['profilePic']['size'];
                    $fileType = $_FILES['profilePic']['type'];
                    $fileNameCmps = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));

                    // Allowed file extensions
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (!in_array($fileExtension, $allowedExtensions)) {
                        $error = 'Invalid file extension. Only JPG, JPEG, PNG, GIF, and WEBP are allowed.';
                    } elseif ($fileSize > 2097152) { // 2MB
                        $error = 'File size is too large. Maximum size is 2MB.';
                    } else {
                        // Create upload directory if not exists
                        $uploadFileDir = __DIR__ . '/../uploads/avatars/';
                        if (!file_exists($uploadFileDir)) {
                            mkdir($uploadFileDir, 0777, true);
                        }
                        
                        // Clean filename
                        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                        $dest_path = $uploadFileDir . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $profile_picture = 'uploads/avatars/' . $newFileName;
                        } else {
                            $error = 'Error moving the uploaded file. Check directory permissions.';
                        }
                    }
                }

                if (empty($error)) {
                    $fullName = $first_name . ' ' . $last_name;
                    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, gender = ?, dob = ?, profile_picture = ? WHERE id = ?");
                    $stmt->execute([$fullName, $email, $phone, $gender, empty($dob) ? null : $dob, $profile_picture, $user['id']]);

                    // Sync updated user data into the active session
                    $_SESSION['user_name'] = $fullName;
                    $_SESSION['user_email'] = $email;
                    
                    $success = 'Profile details updated successfully!';
                    
                    // Refresh $user data
                    $user = get_logged_in_user();
                    $name_parts = explode(' ', $user['full_name'], 2);
                    $first_name = $name_parts[0] ?? '';
                    $last_name = $name_parts[1] ?? '';
                }
            }
        } catch (PDOException $e) {
            $error = 'Database update failed: ' . $e->getMessage();
        }
    }
}
?>

<div class="panel-header">
  <h3 class="panel-title">Profile Settings</h3>
</div>

<?php if (!empty($success)): ?>
  <div style="background: #2ecc71; color: #fff; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.95rem;">
    <?php echo sanitize($success); ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div style="background: #e74c3c; color: #fff; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.95rem;">
    <?php echo sanitize($error); ?>
  </div>
<?php endif; ?>

<form class="dashboard-form" action="dashboard.php?page=profile" method="POST" enctype="multipart/form-data">
  <!-- Avatar Upload Area -->
  <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 12px; flex-wrap: wrap;">
    <div class="user-avatar-wrap" style="width: 80px; height: 80px; font-size: 2.2rem;">
      <?php if (!empty($user['profile_picture'])): ?>
        <img src="<?php echo sanitize($user['profile_picture']); ?>" alt="Avatar" class="user-avatar-img" />
      <?php else: ?>
        👤
      <?php endif; ?>
    </div>
    <div>
      <label for="profilePic" style="font-weight: 600; cursor: pointer; color: var(--gold); font-size: 0.95rem; text-decoration: underline;">
        Upload New Profile Picture
      </label>
      <input type="file" id="profilePic" name="profilePic" accept="image/*" style="display: none;" onchange="document.getElementById('fileNameSpan').textContent = this.files[0].name;" />
      <p id="fileNameSpan" style="font-size: 0.8rem; color: var(--text-muted); margin: 4px 0 0;">Max size: 2MB (JPG, PNG, WEBP)</p>
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="firstName">First Name *</label>
      <input type="text" id="firstName" name="firstName" value="<?php echo sanitize($first_name); ?>" required />
    </div>
    <div class="form-group">
      <label for="lastName">Last Name *</label>
      <input type="text" id="lastName" name="lastName" value="<?php echo sanitize($last_name); ?>" required />
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="email">Email Address *</label>
      <input type="email" id="email" name="email" value="<?php echo sanitize($user['email']); ?>" required />
    </div>
    <div class="form-group">
      <label for="phone">Phone Number</label>
      <input type="text" id="phone" name="phone" value="<?php echo sanitize($user['phone']); ?>" placeholder="e.g. +251 911 234 567" />
    </div>
  </div>

  <div class="form-row">
    <div class="form-group">
      <label for="gender">Gender</label>
      <select id="gender" name="gender">
        <option value="" <?php echo empty($user['gender']) ? 'selected' : ''; ?>>Select Gender</option>
        <option value="Male" <?php echo $user['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
        <option value="Female" <?php echo $user['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
        <option value="Other" <?php echo $user['gender'] === 'Other' ? 'selected' : ''; ?>>Other / Prefer not to say</option>
      </select>
    </div>
    <div class="form-group">
      <label for="dob">Date of Birth</label>
      <input type="date" id="dob" name="dob" value="<?php echo $user['dob'] ? sanitize($user['dob']) : ''; ?>" />
    </div>
  </div>

  <button type="submit" class="btn btn-primary" style="align-self: flex-start; margin-top: 10px;">Save Profile Changes</button>
</form>
