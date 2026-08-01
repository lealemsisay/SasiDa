<?php
// admin/includes/header.php
// No need to define $user here – it's already defined in index.php
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
      (function() {
        var theme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', theme);
      })();
    </script>
    <title>Admin — SASIDA</title>
    <!-- Paths: Root style.css and admin.css -->
    <link rel="stylesheet" href="/SASIDA/style.css">
    <link rel="stylesheet" href="/SASIDA/admin/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body class="admin-page">
    <div class="admin-layout">
        <!-- Admin Theme Toggle -->
        <div style="position:fixed;top:20px;right:20px;z-index:100;">
            <button id="themeToggle" aria-label="Toggle theme" style="background:var(--surface);border:1px solid var(--border);color:var(--text);width:40px;height:40px;border-radius:50%;cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center;transition:all 0.3s ease;">
                <span class="theme-icon light">☀️</span>
                <span class="theme-icon dark" style="display:none;">🌙</span>
            </button>
        </div>