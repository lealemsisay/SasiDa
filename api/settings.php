<?php
/* ═══════════════════════════════════════════════
   SASIDA — api/settings.php
   Public REST API endpoint for store settings
   ═══════════════════════════════════════════════ */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../includes/auth_helper.php';

$settings = get_all_settings();
echo json_encode([
    'success' => true,
    'settings' => $settings
]);
