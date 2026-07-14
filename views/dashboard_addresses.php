<?php
/* ═══════════════════════════════════════════════
   SASIDA — dashboard_addresses.php
   Multi-address management CRUD subview
   ═══════════════════════════════════════════════ */

if (!defined('SASIDA_AUTH_INCLUDED') && !isset($user)) {
  exit;
}

global $pdo;

$action = isset($_GET['sub_action']) ? trim($_GET['sub_action']) : 'list';
$error = '';
$success = '';

// Helper to reset other default addresses
function reset_default_addresses($user_id)
{
  global $pdo;
  $stmt = $pdo->prepare("UPDATE customer_addresses SET is_default = 0 WHERE user_id = ?");
  $stmt->execute([$user_id]);
}

// ─── PROCESS FORM SUBMISSIONS ─────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($action === 'add') {
    $fullName = trim($_POST['fullName'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $subCity = trim($_POST['subCity'] ?? '');
    $woreda = trim($_POST['woreda'] ?? '');
    $houseNumber = trim($_POST['houseNumber'] ?? '');
    $landmark = trim($_POST['landmark'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $isDefault = isset($_POST['isDefault']) ? 1 : 0;

    if (empty($fullName) || empty($phone) || empty($region) || empty($city) || empty($subCity) || empty($woreda) || empty($houseNumber)) {
      $error = 'All fields except Landmark and Notes are required.';
    } else {
      try {
        if ($isDefault) {
          reset_default_addresses($user['id']);
        }

        $stmt = $pdo->prepare("INSERT INTO customer_addresses (user_id, full_name, phone, region, city, sub_city, woreda, house_number, landmark, additional_notes, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user['id'], $fullName, $phone, $region, $city, $subCity, $woreda, $houseNumber, $landmark, $notes, $isDefault]);

        $success = 'Address added successfully!';
        $action = 'list';
      } catch (PDOException $e) {
        $error = 'Failed to add address: ' . $e->getMessage();
      }
    }
  } elseif ($action === 'edit') {
    $addrId = intval($_GET['id'] ?? 0);
    $fullName = trim($_POST['fullName'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $subCity = trim($_POST['subCity'] ?? '');
    $woreda = trim($_POST['woreda'] ?? '');
    $houseNumber = trim($_POST['houseNumber'] ?? '');
    $landmark = trim($_POST['landmark'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $isDefault = isset($_POST['isDefault']) ? 1 : 0;

    if (empty($fullName) || empty($phone) || empty($region) || empty($city) || empty($subCity) || empty($woreda) || empty($houseNumber)) {
      $error = 'All fields except Landmark and Notes are required.';
    } else {
      try {
        if ($isDefault) {
          reset_default_addresses($user['id']);
        }

        $stmt = $pdo->prepare("UPDATE customer_addresses SET full_name = ?, phone = ?, region = ?, city = ?, sub_city = ?, woreda = ?, house_number = ?, landmark = ?, additional_notes = ?, is_default = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$fullName, $phone, $region, $city, $subCity, $woreda, $houseNumber, $landmark, $notes, $isDefault, $addrId, $user['id']]);

        $success = 'Address updated successfully!';
        $action = 'list';
      } catch (PDOException $e) {
        $error = 'Failed to update address: ' . $e->getMessage();
      }
    }
  }
}

// Handle Set Default request
if ($action === 'set_default') {
  $addrId = intval($_GET['id'] ?? 0);
  if ($addrId > 0) {
    try {
      reset_default_addresses($user['id']);
      $stmt = $pdo->prepare("UPDATE customer_addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
      $stmt->execute([$addrId, $user['id']]);
      $success = 'Default address updated!';
    } catch (PDOException $e) {
      $error = 'Failed to set default address.';
    }
  }
  $action = 'list';
}

// ─── RENDER SUBVIEWS ──────────────────────────
if ($action === 'add'):
  // --- ADD ADDRESS SUBVIEW ---
?>
  <div class="panel-header">
    <h3 class="panel-title">Add New Address</h3>
    <a href="dashboard.php?page=addresses" class="address-btn">← Cancel</a>
  </div>

  <?php if (!empty($error)): ?>
    <div style="background: #e74c3c; color: #fff; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px;">
      <?php echo sanitize($error); ?>
    </div>
  <?php endif; ?>

  <form class="dashboard-form" action="dashboard.php?page=addresses&sub_action=add" method="POST">
    <div class="form-row">
      <div class="form-group">
        <label for="fullName">Receiver Full Name *</label>
        <input type="text" id="fullName" name="fullName" required placeholder="e.g. John Doe" />
      </div>
      <div class="form-group">
        <label for="phone">Phone Number *</label>
        <input type="text" id="phone" name="phone" required placeholder="e.g. +251 911 234 567" />
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="region">Region *</label>
        <input type="text" id="region" name="region" required placeholder="e.g. Addis Ababa" />
      </div>
      <div class="form-group">
        <label for="city">City *</label>
        <input type="text" id="city" name="city" required placeholder="e.g. Addis Ababa" />
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="subCity">Sub City *</label>
        <input type="text" id="subCity" name="subCity" required placeholder="e.g. Bole" />
      </div>
      <div class="form-group">
        <label for="woreda">Woreda *</label>
        <input type="text" id="woreda" name="woreda" required placeholder="e.g. 03" />
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="houseNumber">House Number *</label>
        <input type="text" id="houseNumber" name="houseNumber" required placeholder="e.g. 1024" />
      </div>
      <div class="form-group">
        <label for="landmark">Landmark / Nearest Place</label>
        <input type="text" id="landmark" name="landmark" placeholder="e.g. Near Edna Mall" />
      </div>
    </div>

    <div class="form-group">
      <label for="notes">Additional Delivery Instructions</label>
      <textarea id="notes" name="notes" rows="3" placeholder="e.g. Ring bell or call on arrival"></textarea>
    </div>

    <div class="form-group" style="flex-direction: row; align-items: center; gap: 8px;">
      <input type="checkbox" id="isDefault" name="isDefault" value="1" />
      <label for="isDefault" style="cursor:pointer;">Set as default delivery address</label>
    </div>

    <button type="submit" class="btn btn-primary" style="align-self: flex-start;">Save Address</button>
  </form>

  <?php elseif ($action === 'edit'):
  // --- EDIT ADDRESS SUBVIEW ---
  $addrId = intval($_GET['id'] ?? 0);
  try {
    $stmt = $pdo->prepare("SELECT * FROM customer_addresses WHERE id = ? AND user_id = ?");
    $stmt->execute([$addrId, $user['id']]);
    $address = $stmt->fetch();
  } catch (PDOException $e) {
    $address = null;
  }

  if (!$address):
    echo '<div class="dashboard-panel"><p style="color: #e74c3c;">Address not found.</p><a href="dashboard.php?page=addresses" class="btn btn-outline btn-sm">Back</a></div>';
  else:
  ?>
    <div class="panel-header">
      <h3 class="panel-title">Edit Address</h3>
      <a href="dashboard.php?page=addresses" class="address-btn">← Cancel</a>
    </div>

    <?php if (!empty($error)): ?>
      <div style="background: #e74c3c; color: #fff; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <?php echo sanitize($error); ?>
      </div>
    <?php endif; ?>

    <form class="dashboard-form" action="dashboard.php?page=addresses&sub_action=edit&id=<?php echo $address['id']; ?>" method="POST">
      <div class="form-row">
        <div class="form-group">
          <label for="fullName">Receiver Full Name *</label>
          <input type="text" id="fullName" name="fullName" value="<?php echo sanitize($address['full_name']); ?>" required />
        </div>
        <div class="form-group">
          <label for="phone">Phone Number *</label>
          <input type="text" id="phone" name="phone" value="<?php echo sanitize($address['phone']); ?>" required />
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="region">Region *</label>
          <input type="text" id="region" name="region" value="<?php echo sanitize($address['region']); ?>" required />
        </div>
        <div class="form-group">
          <label for="city">City *</label>
          <input type="text" id="city" name="city" value="<?php echo sanitize($address['city']); ?>" required />
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="subCity">Sub City *</label>
          <input type="text" id="subCity" name="subCity" value="<?php echo sanitize($address['sub_city']); ?>" required />
        </div>
        <div class="form-group">
          <label for="woreda">Woreda *</label>
          <input type="text" id="woreda" name="woreda" value="<?php echo sanitize($address['woreda']); ?>" required />
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="houseNumber">House Number *</label>
          <input type="text" id="houseNumber" name="houseNumber" value="<?php echo sanitize($address['house_number']); ?>" required />
        </div>
        <div class="form-group">
          <label for="landmark">Landmark / Nearest Place</label>
          <input type="text" id="landmark" name="landmark" value="<?php echo sanitize($address['landmark']); ?>" />
        </div>
      </div>

      <div class="form-group">
        <label for="notes">Additional Delivery Instructions</label>
        <textarea id="notes" name="notes" rows="3"><?php echo sanitize($address['additional_notes']); ?></textarea>
      </div>

      <div class="form-group" style="flex-direction: row; align-items: center; gap: 8px;">
        <input type="checkbox" id="isDefault" name="isDefault" value="1" <?php echo $address['is_default'] ? 'checked' : ''; ?> />
        <label for="isDefault" style="cursor:pointer;">Set as default delivery address</label>
      </div>

      <button type="submit" class="btn btn-primary" style="align-self: flex-start;">Update Address</button>
    </form>
  <?php endif; ?>

<?php else: ?>
  <!-- --- LIST ADDRESSES SUBVIEW --- -->
  <div class="panel-header">
    <h3 class="panel-title">My Saved Addresses</h3>
    <a href="dashboard.php?page=addresses&sub_action=add" class="btn btn-primary btn-sm">+ Add Address</a>
  </div>

  <?php if (!empty($success)): ?>
    <div style="background: #2ecc71; color: #fff; padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 0.95rem;">
      <?php echo sanitize($success); ?>
    </div>
  <?php endif; ?>

  <?php
  try {
    $stmt = $pdo->prepare("SELECT * FROM customer_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
    $stmt->execute([$user['id']]);
    $addresses = $stmt->fetchAll();
  } catch (PDOException $e) {
    $addresses = [];
  }
  ?>

  <?php if (empty($addresses)): ?>
    <div class="empty-state">
      <div class="empty-icon">📍</div>
      <h3>No addresses saved yet</h3>
      <p style="margin-bottom: 24px;">Add a delivery address to enable faster checkouts and order shipping.</p>
      <a href="dashboard.php?page=addresses&sub_action=add" class="btn btn-primary">+ Add New Address</a>
    </div>
  <?php else: ?>
    <div class="addresses-grid">
      <?php foreach ($addresses as $addr): ?>
        <div class="address-card <?php echo $addr['is_default'] ? 'default' : ''; ?>">
          <h4><?php echo sanitize($addr['full_name']); ?></h4>
          <p><strong>Phone:</strong> <?php echo sanitize($addr['phone']); ?></p>
          <p>
            <?php echo sanitize($addr['region']); ?>, <?php echo sanitize($addr['city']); ?><br>
            <?php echo sanitize($addr['sub_city']); ?>, Woreda <?php echo sanitize($addr['woreda']); ?><br>
            House Number: <?php echo sanitize($addr['house_number']); ?>
            <?php if ($addr['landmark']): ?><br>Landmark: <?php echo sanitize($addr['landmark']); ?><?php endif; ?>
          </p>
          <?php if ($addr['additional_notes']): ?>
            <p style="border-top: 1px dashed var(--border); padding-top: 8px; font-style: italic;">
              "<?php echo sanitize($addr['additional_notes']); ?>"
            </p>
          <?php endif; ?>

          <div class="address-actions">
            <a href="dashboard.php?page=addresses&sub_action=edit&id=<?php echo $addr['id']; ?>" class="address-btn">Edit</a>
            <button class="address-btn delete delete-address-btn" data-id="<?php echo $addr['id']; ?>">Delete</button>
            <?php if (!$addr['is_default']): ?>
              <a href="dashboard.php?page=addresses&sub_action=set_default&id=<?php echo $addr['id']; ?>" class="address-btn" style="margin-left: auto;">Set as Default</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>