<?php
require_once __DIR__ . '/init.php';
require_login();
$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    header('Location: cart.php');
    exit;
}

$method = in_array($_POST['method'] ?? '', ['cash', 'card', 'other']) ? $_POST['method'] : 'cash';
$total = floatval($_POST['total'] ?? 0);

// customer fields from form
$customer_name = trim($_POST['customer_name'] ?? '');
$customer_phone = trim($_POST['customer_phone'] ?? '');
$customer_note = trim($_POST['customer_note'] ?? '');
// send_to_barista removed — orders are completed immediately

$mysqli = db_connect();
$mysqli->begin_transaction();
try {
    $user_id = $_SESSION['user_id'];
    // Check if orders table has customer columns
    $has_customer_cols = false;
    $cols = $mysqli->query("SHOW COLUMNS FROM orders");
    $cols_arr = [];
    while ($c = $cols->fetch_assoc()) $cols_arr[] = $c['Field'];
    if (in_array('customer_name', $cols_arr) && in_array('customer_phone', $cols_arr) && in_array('customer_note', $cols_arr)) {
        $has_customer_cols = true;
    }

    // create orders as 'pending' so baristas can see them in the queue
    $status = 'pending';
    if ($has_customer_cols) {
        $stmt = $mysqli->prepare('INSERT INTO orders (user_id, total, status, customer_name, customer_phone, customer_note) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('idssss', $user_id, $total, $status, $customer_name, $customer_phone, $customer_note);
        $stmt->execute();
    } else {
        $stmt = $mysqli->prepare('INSERT INTO orders (user_id, total, status) VALUES (?, ?, ?)');
        $stmt->bind_param('ids', $user_id, $total, $status);
        $stmt->execute();
    }
    $order_id = $mysqli->insert_id;

    // insert items and decrement stock
    $stmt_item = $mysqli->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
    $stmt_update_stock = $mysqli->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
    foreach ($cart as $pid => $it) {
        $pid_i = intval($pid);
        $qty = intval($it['qty']);
        $price = floatval($it['price']);
        $stmt_item->bind_param('iiid', $order_id, $pid_i, $qty, $price);
        $stmt_item->execute();
        $stmt_update_stock->bind_param('ii', $qty, $pid_i);
        $stmt_update_stock->execute();
    }

    // payment
    $stmt_pay = $mysqli->prepare('INSERT INTO payments (order_id, method, amount) VALUES (?, ?, ?)');
    $stmt_pay->bind_param('isd', $order_id, $method, $total);
    $stmt_pay->execute();

    $mysqli->commit();
    // clear cart
    unset($_SESSION['cart']);
    header('Location: receipt.php?order_id=' . intval($order_id));
    exit;
} catch (Exception $e) {
    $mysqli->rollback();
    die('Checkout failed: ' . $e->getMessage());
}
