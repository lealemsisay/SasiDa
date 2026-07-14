<?php
/* ═══════════════════════════════════════════════
   SASIDA — logout.php
   User sign out endpoint
   ═══════════════════════════════════════════════ */

require_once __DIR__ . '/includes/auth_helper.php';
logout_user();
header("Location: index.php");
exit;
?>
