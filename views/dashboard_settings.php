<?php
/* ═══════════════════════════════════════════════
   SASIDA — dashboard_settings.php
   Account Settings and Change Password subview
   ═══════════════════════════════════════════════ */

if (!defined('SASIDA_AUTH_INCLUDED') && !isset($user)) {
    exit;
}

global $pdo;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine whether we are saving preferences or changing password
    $formType = $_POST['formType'] ?? '';

    if ($formType === 'changePassword') {
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All password fields are required.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirm password do not match.';
        } else {
            try {
                // Fetch current password hash from DB
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                $stmt->execute([$user['id']]);
                $hash = $stmt->fetchColumn();

                if ($hash && password_verify($currentPassword, $hash)) {
                    // Update to new securely hashed password
                    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$newHash, $user['id']]);
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'Incorrect current password.';
                }
            } catch (PDOException $e) {
                $error = 'Failed to change password: ' . $e->getMessage();
            }
        }
    } elseif ($formType === 'preferences') {
        // Mock save preferences (future-ready settings placeholders)
        $success = 'Account settings updated successfully!';
    }
}
?>

<div class="panel-header">
  <h3 class="panel-title">Account Settings</h3>
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

<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 40px; align-items: start; margin-top: 10px;">
  
  <!-- Left Column: Password Management -->
  <div class="dashboard-panel" style="padding: 24px;">
    <h4 style="font-size: 1.15rem; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">Security (Change Password)</h4>
    <form class="dashboard-form" action="dashboard.php?page=settings" method="POST">
      <input type="hidden" name="formType" value="changePassword" />

      <div class="form-group">
        <label for="currentPassword">Current Password *</label>
        <input type="password" id="currentPassword" name="currentPassword" required placeholder="••••••••" />
      </div>

      <div class="form-group">
        <label for="newPassword">New Password *</label>
        <input type="password" id="newPassword" name="newPassword" required placeholder="•••••••• (Min 6 chars)" />
      </div>

      <div class="form-group">
        <label for="confirmPassword">Confirm New Password *</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="••••••••" />
      </div>

      <button type="submit" class="btn btn-primary" style="align-self: flex-start; margin-top: 10px;">Change Password</button>
    </form>
  </div>

  <!-- Right Column: Preferences Management -->
  <div class="dashboard-panel" style="padding: 24px;">
    <h4 style="font-size: 1.15rem; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">Preferences & Settings</h4>
    <form class="dashboard-form" action="dashboard.php?page=settings" method="POST">
      <input type="hidden" name="formType" value="preferences" />

      <!-- Language selector placeholder -->
      <div class="form-group">
        <label for="languageSelect">Preferred Language (Future-ready)</label>
        <select id="languageSelect" name="languageSelect">
          <option value="en" selected>English</option>
          <option value="am">አማርኛ (Amharic)</option>
          <option value="om">Afaan Oromoo</option>
          <option value="fr">Français</option>
        </select>
      </div>

      <!-- Theme selector -->
      <div class="form-group">
        <label for="themeSelect">Default Theme Mode</label>
        <select id="themeSelect" name="themeSelect" onchange="if(themeToggle) themeToggle.click();">
          <option value="dark" selected>Dark Mode (Default)</option>
          <option value="light">Light Mode</option>
        </select>
      </div>

      <!-- Notification Preferences -->
      <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
        <label style="font-size: 0.85rem; font-weight: 600; color: var(--text);">Email Subscription Preferences</label>
        
        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem;">
          <input type="checkbox" id="prefOrders" name="prefOrders" value="1" checked />
          <label for="prefOrders" style="cursor: pointer; color: var(--text-muted);">Receive order status updates</label>
        </div>
        
        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem;">
          <input type="checkbox" id="prefPromos" name="prefPromos" value="1" checked />
          <label for="prefPromos" style="cursor: pointer; color: var(--text-muted);">Receive promotional offers & new arrivals</label>
        </div>
        
        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem;">
          <input type="checkbox" id="prefNewsletter" name="prefNewsletter" value="1" />
          <label for="prefNewsletter" style="cursor: pointer; color: var(--text-muted);">Weekly newsletter digests</label>
        </div>
      </div>

      <!-- Privacy settings -->
      <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 12px; border-top: 1px solid var(--border); padding-top: 16px;">
        <label style="font-size: 0.85rem; font-weight: 600; color: var(--text);">Privacy Settings</label>
        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem;">
          <input type="checkbox" id="prefHistory" name="prefHistory" value="1" checked />
          <label for="prefHistory" style="cursor: pointer; color: var(--text-muted);">Allow tracking browsing history (Recently Viewed)</label>
        </div>
      </div>

      <button type="submit" class="btn btn-outline" style="align-self: flex-start; margin-top: 14px;">Save Settings</button>
    </form>
  </div>

</div>
