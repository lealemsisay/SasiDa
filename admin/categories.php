<?php
// admin/categories.php
require_once __DIR__ . '/../includes/auth_helper.php';
require_once __DIR__ . '/../config/db.php';
require_admin();
require_once 'includes/functions.php';

global $pdo;

// Fetch all categories with active product count
$categories = [];
try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) AS product_count 
        FROM categories c 
        LEFT JOIN products p ON p.category_id = c.id AND p.status = 'visible'
        GROUP BY c.id 
        ORDER BY c.name ASC
    ");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching categories: ' . $e->getMessage();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="admin-main">
    <div class="admin-header">
        <h1>Category Management</h1>
        <button id="addCategoryBtn" class="btn btn-primary" style="cursor:pointer;">+ Add Category</button>
    </div>

    <div class="dashboard-panel" style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:30px;">
        <h3 style="margin-bottom:20px;">All Categories</h3>
        
        <?php if (empty($categories)): ?>
            <p style="color:var(--text-muted);text-align:center;padding:40px;">No categories found. Create one to get started.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="admin-table" style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border);">
                            <th style="padding:12px;text-align:left;">ID</th>
                            <th style="padding:12px;text-align:left;">Icon</th>
                            <th style="padding:12px;text-align:left;">Name</th>
                            <th style="padding:12px;text-align:left;">Active Products</th>
                            <th style="padding:12px;text-align:left;">Visibility Status</th>
                            <th style="padding:12px;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr style="border-bottom:1px solid var(--border);" data-id="<?php echo $cat['id']; ?>">
                                <td style="padding:12px;"><?php echo sanitize($cat['id']); ?></td>
                                <td style="padding:12px;">
                                    <span style="display:inline-block;padding:6px 10px;border-radius:6px;background:<?php echo sanitize($cat['color'] ?: '#d4d9d1'); ?>;font-size:1.2rem;">
                                        <?php echo sanitize($cat['icon'] ?: '📦'); ?>
                                    </span>
                                </td>
                                <td style="padding:12px;font-weight:500;"><?php echo sanitize($cat['name']); ?></td>
                                <td style="padding:12px;"><?php echo intval($cat['product_count']); ?> product(s)</td>
                                <td style="padding:12px;">
                                    <?php if (intval($cat['product_count']) > 0): ?>
                                        <span style="color:#52c48b;font-weight:600;padding:2px 8px;background:rgba(82,196,139,0.1);border-radius:4px;font-size:0.8rem;">Visible on Website</span>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);padding:2px 8px;background:var(--bg);border:1px solid var(--border);border-radius:4px;font-size:0.8rem;">Hidden (0 products)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px;text-align:center;">
                                    <button class="edit-cat-btn btn btn-sm btn-primary" data-id="<?php echo $cat['id']; ?>" style="padding:4px 12px;background:var(--gold);color:#12100d;border:none;border-radius:4px;cursor:pointer;">Edit</button>
                                    <button class="delete-cat-btn btn btn-sm btn-danger" data-id="<?php echo $cat['id']; ?>" style="padding:4px 12px;background:#e74c3c;color:white;border:none;border-radius:4px;cursor:pointer;">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Category Add / Edit Modal -->
<div id="categoryModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:1000;justify-content:center;align-items:center;">
    <div style="background:var(--surface);padding:30px;border-radius:12px;max-width:500px;width:90%;box-shadow:0 10px 40px rgba(0,0,0,0.3);">
        <h2 id="modalTitle" style="margin-bottom:20px;">Add New Category</h2>
        <form id="categoryForm" style="display:flex;flex-direction:column;gap:15px;">
            <input type="hidden" id="catId" name="id" value="0">
            <input type="hidden" id="formAction" name="action" value="add">
            
            <div style="display:flex;flex-direction:column;gap:5px;">
                <label for="catName" style="font-weight:500;">Category Name *</label>
                <input type="text" id="catName" name="name" placeholder="e.g. Cosmetics, Shoes" required style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label for="catIcon" style="font-weight:500;">Icon (Emoji)</label>
                    <input type="text" id="catIcon" name="icon" placeholder="💄" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                </div>
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label for="catColor" style="font-weight:500;">Theme Color</label>
                    <input type="text" id="catColor" name="color" placeholder="#f7e1d7" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:5px;">
                <label for="catDesc" style="font-weight:500;">Description</label>
                <textarea id="catDesc" name="description" placeholder="Category description..." style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);resize:vertical;height:80px;"></textarea>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button type="button" id="cancelCategoryBtn" class="btn btn-secondary" style="padding:10px 20px;background:var(--text-muted);color:white;border:none;border-radius:6px;cursor:pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:10px 20px;background:var(--gold);color:#12100d;border:none;border-radius:6px;cursor:pointer;font-weight:500;">Save Category</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        'use strict';
        const modal = document.getElementById('categoryModal');
        const form = document.getElementById('categoryForm');
        const modalTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('formAction');
        const catId = document.getElementById('catId');
        const addBtn = document.getElementById('addCategoryBtn');
        const cancelBtn = document.getElementById('cancelCategoryBtn');

        if (addBtn) {
            addBtn.addEventListener('click', function() {
                form.reset();
                formAction.value = 'add';
                catId.value = '0';
                modalTitle.textContent = 'Add New Category';
                modal.style.display = 'flex';
            });
        }

        document.querySelectorAll('.edit-cat-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                fetch('api/categories.php?action=get&id=' + id)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const c = data.category;
                            document.getElementById('catName').value = c.name;
                            document.getElementById('catIcon').value = c.icon || '📦';
                            document.getElementById('catColor').value = c.color || '#d4d9d1';
                            document.getElementById('catDesc').value = c.description || '';
                            catId.value = c.id;
                            formAction.value = 'edit';
                            modalTitle.textContent = 'Edit Category';
                            modal.style.display = 'flex';
                        } else {
                            showToast('Error: ' + data.message, 'error');
                        }
                    });
            });
        });

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('api/categories.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    modal.style.display = 'none';
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            });
        });

        document.querySelectorAll('.delete-cat-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (!confirm('Are you sure you want to delete this category?')) return;
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('api/categories.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        const row = document.querySelector('tr[data-id="' + id + '"]');
                        if (row) row.remove();
                    } else {
                        showToast('Error: ' + data.message, 'error');
                    }
                });
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
