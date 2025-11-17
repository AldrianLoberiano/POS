<?php
require_once __DIR__ . '/config.php';
session_start();

function is_logged_in()
{
    return !empty($_SESSION['user_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: /POS/index.php');
        exit;
    }
}

function current_user()
{
    if (!is_logged_in()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role'],
    ];
}

// Flash message helpers (store short-lived messages in session)
function set_flash($type, $message)
{
    if (!isset($_SESSION['_flash'])) $_SESSION['_flash'] = [];
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

// Retrieve and consume flash messages; returns array of items: ['type'=>..., 'message'=>...]
function get_flash()
{
    if (empty($_SESSION['_flash'])) return [];
    $f = $_SESSION['_flash'];
    unset($_SESSION['_flash']);
    return $f;
}

// Convenience: echo flash HTML (used in templates)
function render_flash()
{
    $flashes = get_flash();
    if (!$flashes) return;
    foreach ($flashes as $f) {
        $cls = $f['type'] === 'success' ? 'flash-success' : 'flash-error';
        $msg = htmlspecialchars($f['message'], ENT_QUOTES, 'UTF-8');
        echo "<div class=\"$cls\">$msg</div>";
    }
}
