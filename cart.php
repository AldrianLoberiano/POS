<?php
require_once __DIR__ . '/init.php';
require_login();
$cart = $_SESSION['cart'] ?? [];

// Update quantities
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    foreach ($_POST['qty'] as $pid => $q) {
        $pid = intval($pid);
        $q = intval($q);
        if ($pid > 0) {
            if ($q <= 0) unset($_SESSION['cart'][$pid]);
            else $_SESSION['cart'][$pid]['qty'] = $q;
        }
    }
    header('Location: cart.php');
    exit;
}

// Remove item
if (isset($_GET['remove'])) {
    $rem = intval($_GET['remove']);
    unset($_SESSION['cart'][$rem]);
    header('Location: cart.php');
    exit;
}

$total = 0;
foreach ($cart as $item) $total += $item['price'] * $item['qty'];
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cart - Coffee POS</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f6f6f6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
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

        h2 {
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
            margin-bottom: 24px;
        }

        th,
        td {
            padding: 12px;
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
            padding: 8px 16px;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover,
        input[type="submit"]:hover {
            background: #a0261c;
        }

        input[type="number"],
        input[type="text"],
        select,
        textarea {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 8px 12px;
        }

        .checkout-form {
            background: #f9f3ef;
            border: 1px solid #e0d6ce;
            border-radius: 8px;
            padding: 20px;
            margin-top: 24px;
        }

        .checkout-form label {
            display: block;
            margin-bottom: 12px;
            font-weight: bold;
        }

        .checkout-form input,
        .checkout-form select,
        .checkout-form textarea {
            width: 100%;
            margin-top: 4px;
            box-sizing: border-box;
        }

        .total {
            font-size: 18px;
            font-weight: bold;
            color: #d33;
            margin: 16px 0;
        }

        @media (max-width: 800px) {
            .container {
                padding: 18px 6px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Cart</h1>
        <p><a href="dashboard.php">Back</a> | <a href="logout.php">Logout</a></p>
        <?php if ($cart): ?>
            <form method="post">
                <table>
                    <tr>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Qty</th>
                        <th>Line</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($cart as $pid => $it): ?>
                        <tr>
                            <td><?= h($it['name']) ?></td>
                            <td>₱<?= number_format($it['price'], 2) ?></td>
                            <td><input type="number" name="qty[<?= h($pid) ?>]" value="<?= h($it['qty']) ?>" min="0"></td>
                            <td>₱<?= number_format($it['price'] * $it['qty'], 2) ?></td>
                            <td><a href="cart.php?remove=<?= h($pid) ?>">Remove</a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div class="total">Total: ₱<?= number_format($total, 2) ?></div>
                <p><button type="submit" name="update">Update Cart</button></p>
            </form>
            <div class="checkout-form">
                <h2>Checkout</h2>
                <form action="checkout.php" method="post">
                    <label>Customer name (optional):
                        <input type="text" name="customer_name" placeholder="Customer name">
                    </label>
                    <label>Phone (optional):
                        <input type="text" name="customer_phone" placeholder="Phone number">
                    </label>
                    <label>Note (optional):
                        <textarea name="customer_note" placeholder="e.g. no sugar" rows="2"></textarea>
                    </label>
                    <label>Payment method:
                        <select name="method">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                        </select>
                    </label>
                    <!-- send_to_barista removed; orders will be completed at checkout -->
                    <input type="hidden" name="total" value="<?= h($total) ?>">
                    <button type="submit">Checkout</button>
                </form>
            </div>
        <?php else: ?>
            <p>Cart is empty.</p>
        <?php endif; ?>
    </div>
</body>

</html>