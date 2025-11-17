<?php
require_once __DIR__ . '/init.php';
require_login();
$user = current_user();
if ($user['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$mysqli = db_connect();

// Ensure image_path column exists
$colCheck = $mysqli->query("SHOW COLUMNS FROM products LIKE 'image_path'");
if (!$colCheck || $colCheck->num_rows === 0) {
    $mysqli->query("ALTER TABLE products ADD COLUMN image_path VARCHAR(255) NULL AFTER stock");
}

// Handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = isset($_POST['stock']) && $_POST['stock'] !== '' ? intval($_POST['stock']) : null;
    $imagePath = null;

    // Handle image upload if provided
    if (!empty($_FILES['image']['name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $uploadBase = __DIR__ . '/uploads/products';
        if (!is_dir($uploadBase)) {
            @mkdir($uploadBase, 0777, true);
        }
        $info = @getimagesize($_FILES['image']['tmp_name']);
        $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        if ($info && isset($allowedMime[$info['mime']])) {
            $ext = $allowedMime[$info['mime']];
            $basename = uniqid('prod_', true) . '.' . $ext;
            $destFs = $uploadBase . '/' . $basename;
            if (@move_uploaded_file($_FILES['image']['tmp_name'], $destFs)) {
                $imagePath = 'uploads/products/' . $basename;
            }
        }
    }

    if ($name !== '') {
        $stmt = $mysqli->prepare('INSERT INTO products (name, price, stock, image_path) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sdis', $name, $price, $stock, $imagePath);
        $stmt->execute();
        set_flash('success', 'Product created successfully.');
    }
    header('Location: admin_products.php');
    exit;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $mysqli->prepare('DELETE FROM products WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    set_flash('success', 'Product deleted.');
    header('Location: admin_products.php');
    exit;
}

$res = $mysqli->query('SELECT id, name, price, stock, image_path FROM products ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Manage Products</title>
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

        input[type="text"],
        input[type="number"],
        input[type="file"] {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 6px 8px;
        }

        .card {
            background: #f9f3ef;
            border: 1px solid #e0d6ce;
            border-radius: 8px;
            padding: 16px 18px;
            margin-bottom: 24px;
        }

        .prod-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e0d6ce;
            background: #fff;
            margin-right: 8px;
        }

        @media (max-width: 900px) {
            .container {
                padding: 18px 6px;
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
        <h1>Products</h1>
        <p><a href="dashboard.php">Back</a> | <a href="logout.php">Logout</a></p>
        <?php render_flash(); ?>
        <div class="card">
            <h2>Create Product</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <label>Name<br><input name="name" required></label><br>
                <label>Price<br><input name="price" type="number" step="0.01" required></label><br>
                <label>Stock<br><input name="stock" type="number"></label><br>
                <label>Image<br><input name="image" type="file" accept="image/*"></label><br>
                <button type="submit">Create</button>
            </form>
        </div>

        <div class="card">
            <h2>Existing Products</h2>
            <?php if ($res && $res->num_rows): ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Product</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($p = $res->fetch_assoc()): ?>
                        <tr>
                            <td><?= h($p['id']) ?></td>
                            <td>
                                <?php if (!empty($p['image_path'])): ?>
                                    <img class="prod-thumb" src="<?= h($p['image_path']) ?>" alt="<?= h($p['name']) ?>">
                                <?php endif; ?>
                            </td>
                            <td><?= h($p['name']) ?></td>
                            <td><?= number_format($p['price'], 2) ?></td>
                            <td><?= h($p['stock']) ?></td>
                            <td><a href="admin_products.php?delete=<?= h($p['id']) ?>" onclick="return confirm('Delete?')">Delete</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>No products yet.</p>
            <?php endif; ?>
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
                // click to dismiss
                el.style.cursor = 'pointer';
                el.addEventListener('click', function() {
                    el.classList.add('flash-hidden');
                });
                // auto-dismiss after 4s
                setTimeout(function() {
                    el.classList.add('flash-hidden');
                }, 4000);
            });
        } catch (e) {
            // keep silent
            console && console.warn && console.warn('flash JS failed', e);
        }
    })();
</script>