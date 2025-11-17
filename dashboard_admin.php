<?php
// Admin dashboard
require_once __DIR__ . '/init.php';
require_login();
$user = current_user();

// Handle user deletion (POST for safety)
$delete_user_msg = '';
$create_user_msg = '';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_user_id']) &&
    $user['role'] === 'admin'
) {
    $delete_id = intval($_POST['delete_user_id']);
    if ($delete_id === $user['id']) {
        $delete_user_msg = '<span style="color:red">You cannot delete your own account.</span>';
    } else {
        $mysqli = db_connect();
        // Prevent deleting last admin
        $stmt = $mysqli->prepare('SELECT COUNT(*) as admin_count FROM users WHERE role = "admin"');
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $stmt = $mysqli->prepare('SELECT role FROM users WHERE id = ?');
        $stmt->bind_param('i', $delete_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $target = $result->fetch_assoc();
        $stmt->close();
        if ($target && $target['role'] === 'admin' && $row['admin_count'] <= 1) {
            set_flash('error', 'Cannot delete the last admin account.');
        } else {
            $stmt = $mysqli->prepare('DELETE FROM users WHERE id = ?');
            $stmt->bind_param('i', $delete_id);
            if ($stmt->execute()) {
                set_flash('success', 'User deleted.');
            } else {
                set_flash('error', 'Delete failed: ' . h($mysqli->error));
            }
            $stmt->close();
        }
    }
}

// Handle create user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_create_user'])) {
    $new_username = trim($_POST['new_username'] ?? '');
    $new_email = trim($_POST['new_email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $new_role = $_POST['new_role'] ?? 'cashier';
    if ($new_username === '' || $new_email === '' || $new_password === '') {
        set_flash('error', 'Please fill all required fields.');
    } elseif (!in_array($new_role, ['admin', 'barista', 'cashier'])) {
        set_flash('error', 'Invalid role selected.');
    } else {
        $mysqli = db_connect();
        $stmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->bind_param('ss', $new_username, $new_email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            set_flash('error', 'Username or email already exists.');
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt->close();
            $stmt = $mysqli->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $new_username, $new_email, $hash, $new_role);
            if ($stmt->execute()) {
                set_flash('success', 'User created successfully!');
            } else {
                set_flash('error', 'Registration failed: ' . h($mysqli->error));
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Admin Dashboard - Coffee POS</title>
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

        .actions {
            margin-bottom: 16px;
        }

        .card {
            background: #f9f3ef;
            border: 1px solid #e0d6ce;
            border-radius: 8px;
            padding: 16px 18px;
        }

        .stack>* {
            margin-bottom: 16px;
        }

        .stack>*:last-child {
            margin-bottom: 0;
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
        <h1>Welcome, <?= h($user['username']) ?> (Admin)</h1>
        <p><a href="logout.php">Logout</a></p>
        <?php render_flash(); ?>
        <div class="stack">
            <div class="card">
                <h2>Site Management</h2>
                <ul>
                    <li><a href="seed_admin.php">(Re)seed default admin</a></li>
                    <li><a href="admin_products.php">Manage Products</a></li>
                </ul>
            </div>

            <div class="card">
                <h3>Create User</h3>
                <?php if ($create_user_msg) echo '<div>' . $create_user_msg . '</div>'; ?>
                <?php if ($delete_user_msg) echo '<div>' . $delete_user_msg . '</div>'; ?>
                <form method="post" style="margin-bottom:24px">
                    <input type="hidden" name="admin_create_user" value="1">
                    <label>Username<br><input type="text" name="new_username" required></label><br>
                    <label>Email<br><input type="email" name="new_email" required></label><br>
                    <label>Password<br><input type="password" name="new_password" required></label><br>
                    <label>Role<br>
                        <select name="new_role">
                            <option value="cashier">Cashier</option>
                            <option value="barista">Barista</option>
                            <option value="admin">Admin</option>
                        </select>
                    </label><br>
                    <button type="submit">Create User</button>
                </form>
            </div>

            <div class="card">
                <h3>Users</h3>
                <?php
                $mysqli = db_connect();
                $res = $mysqli->query('SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC');
                if ($res && $res->num_rows):
                ?>
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                        <?php while ($r = $res->fetch_assoc()): ?>
                            <tr>
                                <td><?= h($r['id']) ?></td>
                                <td><?= h($r['username']) ?></td>
                                <td><?= h($r['email']) ?></td>
                                <td><?= h($r['role']) ?></td>
                                <td><?= h($r['created_at']) ?></td>
                                <td>
                                    <?php if ($r['id'] != $user['id']): ?>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Delete user <?= h($r['username']) ?>?')">
                                            <input type="hidden" name="delete_user_id" value="<?= h($r['id']) ?>">
                                            <button type="submit">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <em>Current</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p>No users yet.</p>
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