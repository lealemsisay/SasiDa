<?php
// admin/includes/header.php
// No need to define $user here – it's already defined in index.php
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo sanitize($_COOKIE['theme'] ?? 'dark'); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — SASIDA</title>
    <!-- Paths: from admin/includes/ go up two levels to root for style.css,
         and up one level to admin folder for admin.css -->
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="../admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body class="admin-page">
    <div class="admin-layout">