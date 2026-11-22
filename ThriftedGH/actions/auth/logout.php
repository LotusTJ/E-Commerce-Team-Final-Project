<?php
require_once __DIR__ . '/../../core/session.php';

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
}
session_destroy();

require_once __DIR__ . '/../../core/paths.php';
header('Location: ' . tgh_url('views/public/home.php'));
exit;
