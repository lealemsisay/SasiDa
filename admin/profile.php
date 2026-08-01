<?php
// admin/profile.php
require_once __DIR__ . '/../includes/auth_helper.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

$user = get_logged_in_user();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="admin-main">
    <div class="admin-header">
        <h1>Admin Profile & Account Settings</h1>
    </div>

    <div class="dashboard-panel" style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:30px;max-width:700px;margin-bottom:40px;">
        <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;color:var(--gold);margin-bottom:24px;">👤 Admin Profile Information</h3>

        <form id="profileForm" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:20px;">
            
            <!-- Avatar Display & File Upload -->
            <div style="display:flex;align-items:center;gap:20px;padding-bottom:16px;border-bottom:1px solid var(--border);">
                <div style="width:90px;height:90px;border-radius:50%;overflow:hidden;background:var(--bg);border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <?php $avatarPath = strpos($user['profile_picture'], 'uploads/') === 0 ? '../' . sanitize($user['profile_picture']) : sanitize($user['profile_picture']); ?>
                        <img id="avatarPreview" src="<?php echo $avatarPath; ?>" alt="Profile Photo" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <span id="avatarFallback" style="font-size:2.5rem;">👤</span>
                        <img id="avatarPreview" src="" alt="Profile Photo" style="width:100%;height:100%;object-fit:cover;display:none;">
                    <?php endif; ?>
                </div>
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <label style="font-weight:500;">Profile Photo / Avatar</label>
                    <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp" style="padding:6px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:0.85rem;">
                    <span style="font-size:0.78rem;color:var(--text-muted);">Allowed formats: JPG, PNG, WEBP (Max 2MB)</span>
                </div>
            </div>

            <!-- Profile Info Fields -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div style="display:flex;flex-direction:column;gap:6px;">
                    <label style="font-weight:500;">Full Name *</label>
                    <input type="text" name="full_name" value="<?php echo sanitize($user['full_name'] ?? ''); ?>" required style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                </div>

                <div style="display:flex;flex-direction:column;gap:6px;">
                    <label style="font-weight:500;">Email Address *</label>
                    <input type="email" name="email" value="<?php echo sanitize($user['email'] ?? ''); ?>" required style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:6px;">
                <label style="font-weight:500;">Phone Number</label>
                <input type="text" name="phone" value="<?php echo sanitize($user['phone'] ?? ''); ?>" placeholder="+251 911 ..." style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
            </div>

            <!-- Password Update Section -->
            <div style="border-top:1px solid var(--border);padding-top:20px;margin-top:10px;">
                <h4 style="font-size:1.1rem;font-weight:600;margin-bottom:14px;color:var(--gold);">🔑 Security & Password Update</h4>
                <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:16px;">Leave password fields blank if you do not wish to change your password.</p>
                
                <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:14px;">
                    <label style="font-weight:500;">Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter current password" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">New Password</label>
                        <input type="password" name="new_password" placeholder="Min 6 characters" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Re-enter new password" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;margin-top:16px;">
                <button type="submit" class="btn btn-primary" style="padding:12px 32px;background:var(--gold);color:#12100d;border:none;border-radius:6px;font-weight:600;font-size:1rem;cursor:pointer;">Save Changes</button>
            </div>
        </form>
    </div>
</main>

<script>
    (function() {
        const avatarInput = document.getElementById('avatarInput');
        const avatarPreview = document.getElementById('avatarPreview');
        const avatarFallback = document.getElementById('avatarFallback');

        if (avatarInput) {
            avatarInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                        avatarPreview.style.display = 'block';
                        if (avatarFallback) avatarFallback.style.display = 'none';
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            const newPass = formData.get('new_password');
            const confirmPass = formData.get('confirm_password');
            const currentPass = formData.get('current_password');

            if (newPass || confirmPass || currentPass) {
                if (!currentPass) {
                    showToast('Please enter your current password to update password.', 'error');
                    return;
                }
                if (newPass !== confirmPass) {
                    showToast('New password and confirm password do not match.', 'error');
                    return;
                }
                if (newPass.length < 6) {
                    showToast('New password must be at least 6 characters.', 'error');
                    return;
                }
            }

            fetch('api/profile.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                showToast('Network error: ' + err.message, 'error');
            });
        });

        function showToast(msg, type) {
            if (typeof window.showToast === 'function') {
                window.showToast(msg, type);
            } else {
                alert(msg);
            }
        }
    })();
</script>

<?php include 'includes/footer.php'; ?>