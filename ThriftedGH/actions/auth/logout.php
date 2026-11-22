<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/paths.php';
require_once __DIR__ . '/../../core/helpers.php';

$user = authCurrentUser();
$role = $user ? $user['role'] : 'buyer';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
}
session_destroy();

if ($role === 'seller' || $role === 'admin') {
    header('Location: ' . tgh_login_url($role));
} else {
    header('Location: ' . tgh_url('views/public/home.php'));
}
exit;
