<?php
require_once __DIR__ . '/init.php';
require_login();
$user = current_user();
if (!in_array($user['role'], ['barista', 'admin'])) {
    header('Location: dashboard.php');
    exit;
}

$mysqli = db_connect();
$res = $mysqli->query("SELECT o.id, o.total, o.status, o.created_at, u.username, o.customer_name, o.customer_phone, o.customer_note FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE o.status IN ('pending','in_progress') ORDER BY o.created_at ASC");
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Barista Orders</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f6f6f6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1100px;
            margin: 32px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            padding: 32px 40px 40px 40px;
        }

        h1 {
            color: #333;
            margin-top: 0;
        }

        h2,
        h3 {
            color: #7a3e1d;
        }

        a {
            color: #d33;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: #fafafa;
        }

        th,
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }

        th {
            background: #f2e6e0;
            color: #7a3e1d;
        }

        tr:last-child td {
            border-bottom: none;
        }

        button,
        input[type="submit"] {
            background: #d33;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 7px 16px;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover,
        input[type="submit"]:hover {
            background: #a0261c;
        }

        .toggle-btn {
            cursor: pointer;
            color: #d33;
            text-decoration: underline;
            background: none;
            border: none;
            padding: 0;
            font: inherit;
        }

        .toggle-btn:hover {
            color: #a0261c;
        }

        .items {
            background: #f8f8f8;
        }

        @media (max-width: 900px) {
            .container {
                padding: 18px 6px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Orders Queue</h1>
        <p>Welcome, <?= h($user['username']) ?> (<em><?= h($user['role']) ?></em>) | <a href="logout.php">Logout</a></p>
        <?php if ($res && $res->num_rows): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Time</th>
                    <th>Cashier</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php while ($o = $res->fetch_assoc()):
                    // fetch items for this order
                    $items_res = $mysqli->query('SELECT oi.quantity, oi.unit_price, p.name FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ' . intval($o['id']));
                    $items = [];
                    while ($it = $items_res->fetch_assoc()) $items[] = $it;
                ?>
                    <tr>
                        <td><?= h($o['id']) ?></td>
                        <td><?= h($o['created_at']) ?></td>
                        <td><?= h($o['username']) ?></td>
                        <td><?php if (!empty($o['customer_name'])): ?><?= h($o['customer_name']) ?><?php if (!empty($o['customer_phone'])): ?> &ndash; <?= h($o['customer_phone']) ?><?php endif; ?><?php else: ?>&mdash;<?php endif; ?></td>
                        <td><?= number_format($o['total'], 2) ?></td>
                        <td><?= h($o['status']) ?></td>
                        <td>
                            <?php if ($o['status'] === 'pending'): ?>
                                <a href="order_action.php?action=start&order_id=<?= h($o['id']) ?>">Start</a>
                            <?php endif; ?>
                            <?php if ($o['status'] !== 'completed'): ?>
                                <?php if ($o['status'] === 'pending'): ?> | <?php endif; ?>
                                <a href="order_action.php?action=complete&order_id=<?= h($o['id']) ?>">Complete</a>
                            <?php endif; ?>
                            | <button class="toggle-btn" onclick="toggleItems(<?= h($o['id']) ?>)">Items</button>
                        </td>
                    </tr>
                    <tr id="items-<?= h($o['id']) ?>" class="items" style="display:none">
                        <td colspan="7">
                            <?php if (!empty($o['customer_note'])): ?><div><strong>Note:</strong> <?= h($o['customer_note']) ?></div><?php endif; ?>
                            <table cellpadding="4" border="0">
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
                                        <td><?= number_format($it['unit_price'], 2) ?></td>
                                        <td><?= number_format($it['unit_price'] * $it['quantity'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <script>
                function toggleItems(id) {
                    var el = document.getElementById('items-' + id);
                    if (!el) return;
                    el.style.display = (el.style.display === 'none') ? 'table-row' : 'none';
                }
            </script>
        <?php else: ?>
            <p>No active orders.</p>
        <?php endif; ?>
    </div>
</body>

</html>