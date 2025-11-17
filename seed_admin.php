<?php
require_once __DIR__ . '/config.php';
$mysqli = db_connect();
$username = 'admin';
$email = 'admin@coffee.local';
$password = 'Admin@123';
$role = 'admin';

$stmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo "Admin user already exists.\n";
    echo "You can login with username 'admin' and your chosen password.\n";
    exit;
}
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $username, $email, $hash, $role);
if ($stmt->execute()) {
    echo "Admin user created. Username: admin, Password: Admin@123\n";
    echo "Please change the password after first login.\n";
} else {
    echo "Failed to create admin: " . $mysqli->error;
}
