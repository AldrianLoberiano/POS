<?php
require_once __DIR__ . '/init.php';
require_login();
$order_id = intval($_GET['order_id'] ?? 0);
if ($order_id <= 0) {
    die('Invalid order id');
}

$mysqli = db_connect();
$stmt = $mysqli->prepare('SELECT o.id, o.total, o.status, o.created_at, u.username, o.customer_name, o.customer_phone, o.customer_note FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.id = ? LIMIT 1');
$stmt->bind_param('i', $order_id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();
if (!$order) die('Order not found');

$items = [];
$res2 = $mysqli->query('SELECT oi.product_id, oi.quantity, oi.unit_price, p.name FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ' . intval($order_id));
while ($r = $res2->fetch_assoc()) $items[] = $r;

$res3 = $mysqli->query('SELECT method, amount FROM payments WHERE order_id = ' . intval($order_id) . ' LIMIT 1');
$payment = $res3->fetch_assoc();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Receipt #<?= h($order['id']) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f6f6f6;
            margin: 0;
            padding: 20px;
        }

        .receipt-container {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 24px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #d33;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        h1 {
            color: #d33;
            margin: 0;
            font-size: 24px;
        }

        .receipt-info {
            margin-bottom: 20px;
        }

        .receipt-info p {
            margin: 8px 0;
            font-size: 14px;
        }

        .customer-info {
            background: #f9f3ef;
            border: 1px solid #e0d6ce;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .customer-info strong {
            color: #7a3e1d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 8px 6px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background: #f2e6e0;
            color: #7a3e1d;
            font-weight: bold;
            font-size: 14px;
        }

        td {
            font-size: 14px;
        }

        .total-section {
            border-top: 2px solid #d33;
            padding-top: 16px;
            margin-top: 20px;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 16px;
        }

        .total-amount {
            font-weight: bold;
            color: #d33;
            font-size: 18px;
        }

        .payment-method {
            background: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 4px;
            padding: 8px 12px;
            margin-top: 12px;
        }

        .back-btn {
            display: inline-block;
            background: #d33;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin-top: 20px;
            transition: background 0.2s;
        }

        .back-btn:hover {
            background: #a0261c;
        }

        @media (max-width: 600px) {
            .receipt-container {
                margin: 0;
                border-radius: 0;
                padding: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <div class="header">
            <h1>Receipt #<?= h($order['id']) ?></h1>
        </div>

        <div class="receipt-info">
            <p><strong>Cashier:</strong> <?= h($order['username']) ?></p>
            <p><strong>Time:</strong> <?= h($order['created_at']) ?></p>
        </div>

        <?php if (!empty($order['customer_name']) || !empty($order['customer_phone']) || !empty($order['customer_note'])): ?>
            <div class="customer-info">
                <?php if (!empty($order['customer_name']) || !empty($order['customer_phone'])): ?>
                    <p><strong>Customer:</strong> <?= h($order['customer_name'] ?: '') ?> <?php if (!empty($order['customer_phone'])): ?>(<?= h($order['customer_phone']) ?>)<?php endif; ?></p>
                <?php endif; ?>
                <?php if (!empty($order['customer_note'])): ?>
                    <p><strong>Note:</strong> <?= h($order['customer_note']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <table>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Line</th>
            </tr>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= h($it['name']) ?></td>
                    <td><?= h($it['quantity']) ?></td>
                    <td>₱<?= number_format($it['unit_price'], 2) ?></td>
                    <td>₱<?= number_format($it['unit_price'] * $it['quantity'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="total-section">
            <div class="total-line">
                <span>Total:</span>
                <span class="total-amount">₱<?= number_format($order['total'], 2) ?></span>
            </div>
            <div class="payment-method">
                <div class="total-line">
                    <span>Paid (<?= h($payment['method']) ?>):</span>
                    <span>₱<?= number_format($payment['amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <a href="barista_orders.php" class="back-btn">Back</a>
    </div>
</body>

</html>