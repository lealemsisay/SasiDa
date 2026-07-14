<?php
// admin/includes/functions.php
function get_currency()
{
    global $pdo;
    $stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'default_currency'");
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : 'ETB';
}

function format_price($amount)
{
    $currency = get_currency();
    return $currency . ' ' . number_format((float)$amount, 2);
}

function get_status_badge($status)
{
    $classes = [
        'pending' => 'status-pending',
        'confirmed' => 'status-confirmed',
        'processing' => 'status-processing',
        'ready' => 'status-ready',
        'shipped' => 'status-shipped',
        'delivered' => 'status-delivered',
        'cancelled' => 'status-cancelled',
    ];
    $class = $classes[strtolower($status)] ?? 'status-default';
    return "<span class='status-badge $class'>" . htmlspecialchars($status) . "</span>";
}
