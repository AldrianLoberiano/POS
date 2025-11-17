<?php
require_once __DIR__ . '/init.php';
require_login();
$user = current_user();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cashier - Coffee POS</title>
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

        input[type="number"] {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 5px 8px;
            width: 60px;
        }

        .quick-products {
            flex: 1;
        }

        .cart-summary {
            width: 340px;
            background: #f9f3ef;
            border: 1px solid #e0d6ce;
            border-radius: 8px;
            padding: 18px 20px;
            margin-left: 32px;
        }

        .cart-summary h3 {
            margin-top: 0;
        }

        .cart-summary ul {
            padding-left: 18px;
        }

        .cart-summary strong {
            color: #d33;
        }

        .prod-thumb {
            width: 32px;
            height: 32px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e0d6ce;
            background: #fff;
            margin-right: 8px;
            vertical-align: middle;
        }

        @media (max-width: 900px) {
            .container {
                padding: 18px 6px;
            }

            .cart-summary {
                margin-left: 0;
                width: 100%;
                margin-top: 24px;
            }

            .main-flex {
                flex-direction: column;
            }
        }

        .flash-success {
            background: #e6ffed;
            border: 1px solid #7be08f;
            color: #1b6b2e;
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .flash-error {
            background: #fff0f0;
            border: 1px solid #f1a1a1;
            color: #8b1b1b;
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .flash-hidden {
            opacity: 0 !important;
            transform: translateY(-6px);
            transition: opacity .45s ease, transform .35s ease, max-height .35s ease;
            max-height: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
            overflow: hidden;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Cashier Terminal</h1>
        <p>Welcome, <?= h($user['username']) ?> (<em><?= h($user['role']) ?></em>)</p>
        <?php render_flash(); ?>
        <p><a href="logout.php">Logout</a></p>

        <h2>New Sale</h2>
        <?php
        // Quick product list for cashier (with optional search)
        $mysqli = db_connect();
        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $like = '%' . $q . '%';
            $stmt = $mysqli->prepare('SELECT id, name, price, stock, image_path FROM products WHERE name LIKE ? ORDER BY name ASC LIMIT 50');
            $stmt->bind_param('s', $like);
            $stmt->execute();
            $p_res = $stmt->get_result();
        } else {
            $p_res = $mysqli->query('SELECT id, name, price, stock, image_path FROM products ORDER BY name ASC LIMIT 50');
        }
        ?>
        <div class="main-flex" style="display:flex; gap:32px; align-items:flex-start;">
            <div class="quick-products">
                <h3>Quick Products</h3>
                <form method="get" action="dashboard.php" style="margin-bottom:12px;display:flex;gap:8px;align-items:center">
                    <input type="hidden" name="page" value="cashier">
                    <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search products by name" style="flex:1;padding:8px;border:1px solid #ddd;border-radius:6px">
                    <input type="submit" value="Search" style="padding:8px 12px;background:#d33;color:#fff;border-radius:6px;border:none;cursor:pointer">
                    <?php if ($q !== ''): ?>
                        <a href="dashboard.php?page=cashier" style="margin-left:8px;color:#555;text-decoration:underline">Clear</a>
                    <?php endif; ?>
                </form>
                <?php if ($p_res && $p_res->num_rows): ?>
                    <table>
                        <tr>
                            <th>Product</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Action</th>
                        </tr>
                        <?php while ($p = $p_res->fetch_assoc()): ?>
                            <tr>
                                <td><?php if (!empty($p['image_path'])): ?><img class="prod-thumb" src="<?= h($p['image_path']) ?>" alt="<?= h($p['name']) ?>"><?php endif; ?></td>
                                <td><?= h($p['name']) ?></td>
                                <td>₱<?= number_format($p['price'], 2) ?></td>
                                <td>
                                    <?php
                                    // show stock; NULL means unlimited/NA
                                    $stockVal = is_null($p['stock']) ? null : intval($p['stock']);
                                    if (is_null($stockVal)) {
                                        echo '&mdash;';
                                    } else {
                                        echo h($stockVal);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <form action="add_to_cart.php" method="post" style="display:inline">
                                        <input type="hidden" name="product_id" value="<?= h($p['id']) ?>">
                                        <?php $maxAttr = (!is_null($stockVal) && $stockVal > 0) ? 'max="' . $stockVal . '"' : ''; ?>
                                        <?php if (!is_null($stockVal) && $stockVal <= 0): ?>
                                            <input type="number" name="qty" value="0" min="0" readonly style="width:60px;text-align:center">
                                            <button type="button" disabled style="opacity:.6;cursor:not-allowed;background:#ccc;border:none;padding:6px 10px;border-radius:4px">Out of Stock</button>
                                        <?php else: ?>
                                            <input type="number" name="qty" value="1" min="1" <?= $maxAttr ?> style="width:60px;text-align:center">
                                            <button type="submit">Add</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p>No products available. Ask admin to add products.</p>
                <?php endif; ?>
                <p><a href="products.php">All products</a></p>
            </div>
            <div class="cart-summary">
                <h3>Cart Summary</h3>
                <?php $cart = $_SESSION['cart'] ?? [];
                if ($cart): ?>
                    <ul>
                        <?php $sum = 0;
                        foreach ($cart as $pid => $it): $line = $it['price'] * $it['qty'];
                            $sum += $line; ?>
                            <li><?= h($it['name']) ?> x<?= h($it['qty']) ?> — ₱<?= number_format($line, 2) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><strong>Total: ₱<?= number_format($sum, 2) ?></strong></p>
                    <p><a href="cart.php">🛒 View cart / Checkout</a></p>
                <?php else: ?>
                    <p>Cart is empty.</p>
                    <p><a href="cart.php">🛒 View cart</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
<script>
    (function() {
        try {
            const flashes = document.querySelectorAll('.flash-success, .flash-error');
            if (!flashes || flashes.length === 0) return;
            flashes.forEach(function(el) {
                el.style.cursor = 'pointer';
                el.addEventListener('click', function() {
                    el.classList.add('flash-hidden');
                });
                setTimeout(function() {
                    el.classList.add('flash-hidden');
                }, 4000);
            });
        } catch (e) {
            console && console.warn && console.warn('flash JS failed', e);
        }
    })();
</script>