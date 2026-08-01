<?php
// admin/settings.php
require_once __DIR__ . '/../includes/auth_helper.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

$settings = get_all_settings();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="admin-main">
    <div class="admin-header">
        <h1>Store & Business Settings</h1>
    </div>

    <div class="dashboard-panel" style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:30px;max-width:900px;margin-bottom:40px;">
        <form id="settingsForm" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:28px;">
            
            <!-- SECTION 1: BUSINESS INFORMATION & BRANDING -->
            <div style="border-bottom:1px solid var(--border);padding-bottom:20px;">
                <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;color:var(--gold);margin-bottom:16px;">🏢 Business & Branding Information</h3>
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">Store Name *</label>
                        <input type="text" name="store_name" value="<?php echo sanitize($settings['store_name'] ?? 'SASIDA'); ?>" required style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">Business Location / City</label>
                        <input type="text" name="business_location" value="<?php echo sanitize($settings['business_location'] ?? ''); ?>" placeholder="e.g. Addis Ababa, Ethiopia" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">Store Logo</label>
                        <input type="file" name="store_logo" accept="image/*" style="padding:8px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                        <?php if (!empty($settings['store_logo'])): ?>
                            <p style="font-size:0.8rem;color:var(--text-muted);margin-top:4px;">Current Logo: <code><?php echo sanitize($settings['store_logo']); ?></code></p>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">Store Banner (Optional)</label>
                        <input type="file" name="store_banner" accept="image/*" style="padding:8px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                </div>
            </div>

            <!-- SECTION 2: CONTACT INFORMATION -->
            <div style="border-bottom:1px solid var(--border);padding-bottom:20px;">
                <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;color:var(--gold);margin-bottom:16px;">📞 Contact & Social Media Information</h3>
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">Contact Phone Number</label>
                        <input type="text" name="contact_phone" value="<?php echo sanitize($settings['contact_phone'] ?? ''); ?>" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">Contact Email Address</label>
                        <input type="email" name="contact_email" value="<?php echo sanitize($settings['contact_email'] ?? ''); ?>" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">WhatsApp Number</label>
                        <input type="text" name="contact_whatsapp" value="<?php echo sanitize($settings['contact_whatsapp'] ?? ''); ?>" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">Business Hours</label>
                        <input type="text" name="business_hours" value="<?php echo sanitize($settings['business_hours'] ?? ''); ?>" placeholder="e.g. Mon–Sat 9am – 9pm" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">Instagram Handle (@handle)</label>
                        <input type="text" name="contact_instagram" value="<?php echo sanitize($settings['contact_instagram'] ?? ''); ?>" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">Instagram Profile URL</label>
                        <input type="url" name="contact_instagram_url" value="<?php echo sanitize($settings['contact_instagram_url'] ?? ''); ?>" placeholder="https://instagram.com/yourhandle" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">TikTok Handle (@handle)</label>
                        <input type="text" name="contact_tiktok" value="<?php echo sanitize($settings['contact_tiktok'] ?? ''); ?>" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">TikTok Profile URL</label>
                        <input type="url" name="contact_tiktok_url" value="<?php echo sanitize($settings['contact_tiktok_url'] ?? ''); ?>" placeholder="https://tiktok.com/@yourhandle" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:16px;">
                    <label style="font-weight:500;">Physical Address</label>
                    <input type="text" name="contact_address" value="<?php echo sanitize($settings['contact_address'] ?? ''); ?>" placeholder="e.g. Bole Road, Addis Ababa, Ethiopia" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                </div>

                <div style="display:flex;flex-direction:column;gap:6px;">
                    <label style="font-weight:500;">Google Maps URL (Optional)</label>
                    <input type="url" name="google_maps_url" value="<?php echo sanitize($settings['google_maps_url'] ?? ''); ?>" placeholder="https://maps.google.com/..." style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                </div>
            </div>

            <!-- SECTION 3: ABOUT US PAGE CONTENT -->
            <div style="border-bottom:1px solid var(--border);padding-bottom:20px;">
                <h3 style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;color:var(--gold);margin-bottom:16px;">📖 About Us Page Content</h3>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">About Tagline / Badge</label>
                        <input type="text" name="about_tag" value="<?php echo sanitize($settings['about_tag'] ?? 'Our Story'); ?>" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">About Main Title</label>
                        <input type="text" name="about_title" value="<?php echo sanitize($settings['about_title'] ?? ''); ?>" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:16px;">
                    <label style="font-weight:500;">About Us Paragraph 1</label>
                    <textarea name="about_desc1" rows="3" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);resize:vertical;"><?php echo sanitize($settings['about_desc1'] ?? ''); ?></textarea>
                </div>

                <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:16px;">
                    <label style="font-weight:500;">About Us Paragraph 2</label>
                    <textarea name="about_desc2" rows="3" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);resize:vertical;"><?php echo sanitize($settings['about_desc2'] ?? ''); ?></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">Company Mission</label>
                        <textarea name="about_mission" rows="3" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);resize:vertical;"><?php echo sanitize($settings['about_mission'] ?? ''); ?></textarea>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        <label style="font-weight:500;">Company Vision</label>
                        <textarea name="about_vision" rows="3" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);resize:vertical;"><?php echo sanitize($settings['about_vision'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- SAVE BUTTON -->
            <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn btn-primary" style="padding:14px 36px;background:var(--gold);color:#12100d;border:none;border-radius:6px;font-weight:600;font-size:1rem;cursor:pointer;">Save All Settings</button>
            </div>
        </form>
    </div>
</main>

<script>
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('api/settings.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof window.showToast === 'function') {
                    window.showToast(data.message, 'success');
                } else {
                    alert(data.message);
                }
                setTimeout(() => location.reload(), 1000);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => alert('Network error: ' + err.message));
    });
</script>

<?php include 'includes/footer.php'; ?>
