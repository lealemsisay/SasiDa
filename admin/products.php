<?php
// admin/products.php
require_once __DIR__ . '/../includes/auth_helper.php';
require_once __DIR__ . '/../config/db.php';
require_admin();
require_once 'includes/functions.php';

global $pdo;
$user = get_logged_in_user();

$message = '';
$error = '';

// Fetch all products with categories
$products = [];
try {
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC
    ");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching products: ' . $e->getMessage();
}

// Fetch categories for dropdowns
$categories = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching categories: ' . $e->getMessage();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<main class="admin-main">
    <div class="admin-header">
        <h1>Products Management</h1>
        <button id="addProductBtn" class="btn btn-primary" style="cursor:pointer;">+ Add Product</button>
    </div>

    <?php if (!empty($message)): ?>
        <div class="admin-alert success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="admin-alert error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <div class="dashboard-panel" style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:30px;">
        <h3 style="margin-bottom:20px;">All Products</h3>
        <?php if (empty($products)): ?>
            <p style="color:var(--text-muted);text-align:center;padding:40px;">No products found. Create one to get started.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="admin-table" style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border);">
                            <th style="padding:12px;">ID</th>
                            <th style="padding:12px;">Primary Image</th>
                            <th style="padding:12px;">Name</th>
                            <th style="padding:12px;">Category</th>
                            <th style="padding:12px;">Price</th>
                            <th style="padding:12px;">Stock</th>
                            <th style="padding:12px;">Status</th>
                            <th style="padding:12px;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $prod): ?>
                            <?php 
                              $imgSrc = !empty($prod['image']) ? $prod['image'] : null;
                              if (!$imgSrc && !empty($prod['images'])) {
                                  $dec = json_decode($prod['images'], true);
                                  if (is_array($dec) && !empty($dec)) $imgSrc = $dec[0];
                              }
                            ?>
                            <tr style="border-bottom:1px solid var(--border);" data-id="<?php echo $prod['id']; ?>">
                                <td style="padding:12px;"><?php echo sanitize($prod['id']); ?></td>
                                <td style="padding:12px;">
                                    <?php if (!empty($imgSrc)): ?>
                                        <?php $imgPath = (strpos($imgSrc, 'uploads/') === 0) ? '../' . sanitize($imgSrc) : sanitize($imgSrc); ?>
                                        <img src="<?php echo $imgPath; ?>" alt="Product" style="width:50px;height:50px;object-fit:cover;border-radius:4px;" onerror="this.style.display='none';this.nextElementSibling.style.display='inline';">
                                        <span style="font-size:1.5rem;display:none;">📦</span>
                                    <?php else: ?>
                                        <span style="font-size:1.5rem;">📦</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px;font-weight:500;"><?php echo sanitize($prod['name']); ?></td>
                                <td style="padding:12px;"><?php echo sanitize($prod['category_name'] ?? 'N/A'); ?></td>
                                <td style="padding:12px;"><?php echo format_price($prod['price']); ?></td>
                                <td style="padding:12px;"><?php echo intval($prod['stock']); ?></td>
                                <td style="padding:12px;">
                                    <select class="status-select" data-id="<?php echo $prod['id']; ?>" style="padding:4px 8px;border-radius:4px;border:1px solid var(--border);background:var(--bg);color:var(--text);font-size:0.85rem;">
                                        <option value="visible" <?php echo $prod['status'] === 'visible' ? 'selected' : ''; ?>>Visible</option>
                                        <option value="hidden" <?php echo $prod['status'] === 'hidden' ? 'selected' : ''; ?>>Hidden</option>
                                        <option value="outofstock" <?php echo $prod['status'] === 'outofstock' ? 'selected' : ''; ?>>Out of Stock</option>
                                        <option value="archived" <?php echo $prod['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                    </select>
                                </td>
                                <td style="padding:12px;text-align:center;">
                                    <button class="edit-product-btn btn btn-sm btn-primary" data-id="<?php echo $prod['id']; ?>" style="padding:4px 10px;background:var(--gold);color:#12100d;border:none;border-radius:4px;cursor:pointer;">Edit</button>
                                    <button class="delete-product-btn btn btn-sm btn-danger" data-id="<?php echo $prod['id']; ?>" style="padding:4px 10px;background:#e74c3c;color:white;border:none;border-radius:4px;cursor:pointer;">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- ========== ADD / EDIT MODAL ========== -->
<div id="productModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:1000;justify-content:center;align-items:center;overflow-y:auto;padding:20px;">
    <div style="background:var(--surface);padding:30px;border-radius:12px;max-width:720px;width:100%;box-shadow:0 10px 40px rgba(0,0,0,0.3);margin:auto;max-height:90vh;overflow-y:auto;">
        <h2 id="modalTitle" style="margin-bottom:20px;">Add New Product</h2>
        <form id="productForm" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:15px;">
            <input type="hidden" id="productId" name="id" value="0">
            <input type="hidden" id="formAction" name="action" value="add">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label for="prodName" style="font-weight:500;">Product Name *</label>
                    <input type="text" id="prodName" name="name" placeholder="Product name" required style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                </div>
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label for="prodPrice" style="font-weight:500;">Price (ETB) *</label>
                    <input type="number" id="prodPrice" name="price" placeholder="0.00" step="0.01" min="0" required style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label for="prodOldPrice" style="font-weight:500;">Old Price (optional)</label>
                    <input type="number" id="prodOldPrice" name="old_price" placeholder="0.00" step="0.01" min="0" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                </div>
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label for="prodBadge" style="font-weight:500;">Badge (e.g., "Sale", "New")</label>
                    <input type="text" id="prodBadge" name="badge" placeholder="Badge text" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:15px;">
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label for="prodCategory" style="font-weight:500;">Category</label>
                    <select id="prodCategory" name="category_id" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                        <option value="0">-- Select --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label for="prodStock" style="font-weight:500;">Stock Quantity</label>
                    <input type="number" id="prodStock" name="stock" placeholder="0" min="0" value="0" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                </div>
                <div style="display:flex;flex-direction:column;gap:5px;">
                    <label for="prodStatus" style="font-weight:500;">Status</label>
                    <select id="prodStatus" name="status" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);">
                        <option value="visible">Visible</option>
                        <option value="hidden">Hidden</option>
                        <option value="outofstock">Out of Stock</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:5px;">
                <label for="prodDesc" style="font-weight:500;">Full Product Description</label>
                <textarea id="prodDesc" name="description" placeholder="Write complete product description..." rows="4" style="padding:10px;background:var(--bg);border:1px solid var(--border);border-radius:6px;color:var(--text);resize:vertical;"></textarea>
            </div>

            <!-- MULTIPLE IMAGE UPLOAD (UP TO 10 PHOTOS) -->
            <div style="display:flex;flex-direction:column;gap:8px;border:1px solid var(--border);padding:16px;border-radius:8px;background:var(--bg);">
                <label style="font-weight:500;">Product Images (Up to 10 photos)</label>
                <p style="font-size:0.8rem;color:var(--text-muted);margin:0;">The first photo will automatically serve as the primary featured image on the storefront.</p>
                <input type="file" id="prodImages" name="images[]" multiple accept="image/jpeg,image/png,image/webp" style="padding:8px;background:var(--surface);border:1px solid var(--border);border-radius:6px;color:var(--text);">

                <!-- Existing Images Grid & Manager -->
                <div id="existingImagesSection" style="margin-top:10px;display:none;">
                    <label style="font-size:0.85rem;font-weight:500;display:block;margin-bottom:6px;">Current Product Photos:</label>
                    <div id="existingImagesList" style="display:flex;flex-wrap:wrap;gap:10px;"></div>
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:10px;">
                <button type="button" id="cancelModalBtn" class="btn btn-secondary" style="padding:10px 20px;background:var(--text-muted);color:white;border:none;border-radius:6px;cursor:pointer;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding:10px 20px;background:var(--gold);color:var(--bg);border:none;border-radius:6px;cursor:pointer;font-weight:500;">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
        'use strict';

        const modal = document.getElementById('productModal');
        const form = document.getElementById('productForm');
        const modalTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('formAction');
        const productId = document.getElementById('productId');
        const cancelBtn = document.getElementById('cancelModalBtn');
        const existingImagesSection = document.getElementById('existingImagesSection');
        const existingImagesList = document.getElementById('existingImagesList');

        let currentImagePaths = [];

        function renderExistingImagesManager() {
            existingImagesList.innerHTML = '';
            if (currentImagePaths.length === 0) {
                existingImagesSection.style.display = 'none';
                return;
            }

            existingImagesSection.style.display = 'block';
            currentImagePaths.forEach(function(path, idx) {
                const wrap = document.createElement('div');
                wrap.style.cssText = 'position:relative;width:70px;height:70px;border-radius:6px;overflow:hidden;border:2px solid ' + (idx === 0 ? 'var(--gold)' : 'var(--border)');

                const src = path.startsWith('uploads/') ? '../' + path : path;
                const img = document.createElement('img');
                img.src = src;
                img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
                wrap.appendChild(img);

                // Badge for primary image
                if (idx === 0) {
                    const badge = document.createElement('span');
                    badge.textContent = 'Main';
                    badge.style.cssText = 'position:absolute;bottom:0;left:0;right:0;background:var(--gold);color:#000;font-size:0.6rem;text-align:center;font-weight:bold;padding:1px 0;';
                    wrap.appendChild(badge);
                }

                // Delete button
                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.textContent = '✕';
                delBtn.style.cssText = 'position:absolute;top:2px;right:2px;background:rgba(231,76,60,0.85);color:#fff;border:none;border-radius:50%;width:18px;height:18px;font-size:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;';
                delBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    currentImagePaths.splice(idx, 1);
                    renderExistingImagesManager();
                });
                wrap.appendChild(delBtn);

                existingImagesList.appendChild(wrap);
            });
        }

        // --- Open Add Modal ---
        document.getElementById('addProductBtn').addEventListener('click', function() {
            form.reset();
            formAction.value = 'add';
            productId.value = '0';
            modalTitle.textContent = 'Add New Product';
            currentImagePaths = [];
            renderExistingImagesManager();
            modal.style.display = 'flex';
        });

        // --- Open Edit Modal (populate fields) ---
        document.querySelectorAll('.edit-product-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                fetch('api/products.php?action=get&id=' + id)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const p = data.product;
                            document.getElementById('prodName').value = p.name;
                            document.getElementById('prodPrice').value = p.price;
                            document.getElementById('prodOldPrice').value = p.old_price || p.oldPrice || '';
                            document.getElementById('prodBadge').value = p.badge || '';
                            document.getElementById('prodCategory').value = p.category_id || p.categoryId || '0';
                            document.getElementById('prodStock').value = p.stock;
                            document.getElementById('prodStatus').value = p.status;
                            document.getElementById('prodDesc').value = p.description || '';
                            productId.value = p.id;
                            formAction.value = 'edit';
                            modalTitle.textContent = 'Edit Product';

                            currentImagePaths = Array.isArray(p.images) ? [...p.images] : (p.image ? [p.image] : []);
                            renderExistingImagesManager();
                            modal.style.display = 'flex';
                        } else {
                            showToast('Error loading product: ' + data.message, 'error');
                        }
                    })
                    .catch(err => {
                        showToast('Network error: ' + err.message, 'error');
                    });
            });
        });

        // --- Cancel / Close modal ---
        function closeModal() {
            modal.style.display = 'none';
        }
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });

        // --- Form submission (AJAX) ---
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // Append existing_images array
            currentImagePaths.forEach(function(path) {
                formData.append('existing_images[]', path);
            });

            if (!formData.get('name') || !formData.get('price') || parseFloat(formData.get('price')) <= 0) {
                showToast('Name and price are required.', 'error');
                return;
            }

            fetch('api/products.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    closeModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(err => {
                showToast('Network error: ' + err.message, 'error');
            });
        });

        // --- Delete product ---
        document.querySelectorAll('.delete-product-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                if (!confirm('Are you sure you want to delete this product?')) return;
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                fetch('api/products.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast('Product deleted.', 'success');
                        const row = document.querySelector('tr[data-id="' + id + '"]');
                        if (row) row.remove();
                    } else {
                        showToast('Error: ' + data.message, 'error');
                    }
                });
            });
        });

        // --- Status change via dropdown ---
        document.querySelectorAll('.status-select').forEach(function(select) {
            select.addEventListener('change', function() {
                const id = this.dataset.id;
                const status = this.value;
                const formData = new FormData();
                formData.append('action', 'status');
                formData.append('id', id);
                formData.append('status', status);
                fetch('api/products.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast('Status updated.', 'success');
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