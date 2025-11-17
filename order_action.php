<?php
require_once __DIR__ . '/init.php';
require_login();
$user = current_user();
if (!in_array($user['role'], ['barista', 'admin'])) {
    header('Location: dashboard.php');
    exit;
}

$action = $_GET['action'] ?? '';
$order_id = intval($_GET['order_id'] ?? 0);
if ($order_id <= 0) die('Invalid order');

$mysqli = db_connect();

if ($action === 'start') {
    $stmt = $mysqli->prepare("UPDATE orders SET status = 'in_progress' WHERE id = ? AND status = 'pending'");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
} elseif ($action === 'complete') {
    $stmt = $mysqli->prepare("UPDATE orders SET status = 'completed' WHERE id = ? AND status != 'completed'");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();

    // Redirect to receipt after completing order
    header('Location: receipt.php?order_id=' . $order_id);
    exit;
}

header('Location: barista_orders.php');
exit;
