<?php
require_once __DIR__ . '/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user = trim($_POST['user'] ?? '');
$password = $_POST['password'] ?? '';

if ($user === '' || $password === '') {
    header('Location: index.php?error=' . urlencode('Please fill in both fields'));
    exit;
}

$mysqli = db_connect();

$stmt = $mysqli->prepare('SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1');
$stmt->bind_param('ss', $user, $user);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row) {
    header('Location: index.php?error=' . urlencode('Invalid credentials'));
    exit;
}

if (!password_verify($password, $row['password'])) {
    header('Location: index.php?error=' . urlencode('Invalid credentials'));
    exit;
}

// login success
session_regenerate_id(true);
$_SESSION['user_id'] = $row['id'];
$_SESSION['username'] = $row['username'];
$_SESSION['email'] = $row['email'];
$_SESSION['role'] = $row['role'];

header('Location: dashboard.php');
exit;
