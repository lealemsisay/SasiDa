<?php
/* ═══════════════════════════════════════════════
   SASIDA — dashboard_notifications.php
   Notifications view panel
   ═══════════════════════════════════════════════ */

if (!defined('SASIDA_AUTH_INCLUDED') && !isset($user)) {
    exit;
}

global $pdo;

try {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user['id']]);
    $notifications = $stmt->fetchAll();
} catch (PDOException $e) {
    $notifications = [];
}
?>

<div class="panel-header">
  <h3 class="panel-title">Notifications</h3>
  <?php if (!empty($notifications)): ?>
    <button id="markAllRead" class="address-btn" style="font-weight: 600;">Mark All as Read</button>
  <?php endif; ?>
</div>

<?php if (empty($notifications)): ?>
  <div class="empty-state">
    <div class="empty-icon">🔔</div>
    <h3>No notifications yet</h3>
    <p>We'll notify you when your orders ship, stock updates, or new promotions are active.</p>
  </div>
<?php else: ?>
  <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
    <?php foreach ($notifications as $n): 
      $unread_class = !$n['is_read'] ? 'unread' : '';
      $icon = '📢';
      if ($n['type'] === 'order_status') $icon = '📦';
      if ($n['type'] === 'promotion') $icon = '🔥';
      if ($n['type'] === 'stock') $icon = '⚡';
    ?>
      <div class="notification-item <?php echo $unread_class; ?>" style="background: var(--surface); border: 1px solid var(--border); padding: 18px 24px; border-radius: var(--radius-sm); display: flex; align-items: start; gap: 16px; position: relative; transition: all 0.3s ease;">
        <span style="font-size: 1.8rem; margin-top: 4px;"><?php echo $icon; ?></span>
        <div style="flex: 1; padding-right: 60px;">
          <h4 style="margin: 0 0 6px; font-size: 1rem; font-weight: 600; color: var(--text);"><?php echo sanitize($n['title']); ?></h4>
          <p style="margin: 0 0 8px; color: var(--text-muted); font-size: 0.85rem; line-height: 1.5;"><?php echo sanitize($n['message']); ?></p>
          <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('M d, Y H:i', strtotime($n['created_at'])); ?></span>
        </div>

        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 12px; position: absolute; right: 24px; top: 18px;">
          <?php if (!$n['is_read']): ?>
            <button class="mark-read-btn" data-id="<?php echo $n['id']; ?>" style="background: var(--gold); border: none; color: #12100d; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 20px; cursor: pointer; text-transform: uppercase;">Mark Read</button>
          <?php endif; ?>
          <button class="delete-notif-btn" data-id="<?php echo $n['id']; ?>" style="background: none; border: none; color: #e74c3c; font-size: 0.8rem; cursor: pointer; font-weight: 500;">Delete</button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <style>
    .notification-item.unread {
      border-left: 4px solid var(--gold) !important;
      background: rgba(197, 160, 89, 0.02) !important;
    }
  </style>
<?php endif; ?>
