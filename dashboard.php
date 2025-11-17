<?php
require_once __DIR__ . '/init.php';
require_login();
$user = current_user();

// Simple router based on role
if (
    $user['role'] === 'admin'
) {
    include __DIR__ . '/dashboard_admin.php';
    exit;
} elseif ($user['role'] === 'barista') {
    header('Location: barista_orders.php');
    exit;
} else {
    include __DIR__ . '/dashboard_cashier.php';
    exit;
}
