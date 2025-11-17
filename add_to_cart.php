<?php
require_once __DIR__ . '/init.php';
require_login();
$product_id = intval($_POST['product_id'] ?? 0);
$qty = intval($_POST['qty'] ?? 1);
if ($product_id <= 0 || $qty <= 0) {
    $return_to = $_POST['return_to'] ?? $_GET['return_to'] ?? 'dashboard.php?page=cashier';
    header('Location: ' . $return_to);
    exit;
}

$mysqli = db_connect();
$stmt = $mysqli->prepare('SELECT id, name, price, stock FROM products WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $product_id);
$stmt->execute();
$res = $stmt->get_result();
$p = $res->fetch_assoc();
if (!$p) {
    $return_to = $_POST['return_to'] ?? $_GET['return_to'] ?? 'dashboard.php?page=cashier';
    header('Location: ' . $return_to);
    exit;
}

// simple stock check
// check stock accounting for existing cart quantity
$stock = is_null($p['stock']) ? null : intval($p['stock']);
$currentInCart = $_SESSION['cart'][$product_id]['qty'] ?? 0;
if (!is_null($stock)) {
    $return_to = $_POST['return_to'] ?? $_GET['return_to'] ?? 'dashboard.php?page=cashier';
    $sep = (strpos($return_to, '?') === false) ? '?' : '&';
    if ($stock <= 0) {
        set_flash('error', 'Product "' . $p['name'] . '" is out of stock.');
        header('Location: ' . $return_to);
        exit;
    }
    if ($currentInCart + $qty > $stock) {
        set_flash('error', 'Not enough stock for "' . $p['name'] . '". Available: ' . $stock . ', in cart: ' . $currentInCart);
        header('Location: ' . $return_to);
        exit;
    }
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (!isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] = ['qty' => 0, 'name' => $p['name'], 'price' => $p['price']];
}
$_SESSION['cart'][$product_id]['qty'] += $qty;
set_flash('success', 'Added ' . intval($qty) . ' x "' . $p['name'] . '" to cart.');

$return_to = $_POST['return_to'] ?? $_GET['return_to'] ?? 'dashboard.php?page=cashier';
header('Location: ' . $return_to);
exit;
