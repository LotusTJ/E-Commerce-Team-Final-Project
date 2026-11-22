<?php
require_once __DIR__ . '/../../core/guard.php';
require_once __DIR__ . '/../../classes/UserClass.php';

$loginPage = tgh_url('views/auth/login.php');

guardRequirePost();
guardRequireCsrf($loginPage);

$email = trim(tgh_input_string($_POST, 'email'));
$password = tgh_input_string($_POST, 'password');

if ($email === '' || $password === '') {
    tgh_flash_set('error', 'Enter your email and password.');
    tgh_redirect($loginPage);
}

$users = new UserClass();
$ip = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);

if ($users->isLockedOut($email, $ip)) {
    tgh_flash_set('error', 'Too many failed attempts. Please wait ' . UserClass::LOCK_MINUTES . ' minutes and try again.');
    tgh_redirect($loginPage);
}

$user = $users->loginUser($email);

if (!$user || !password_verify($password, $user['password_hash'])) {
    $users->recordFailure($email, $ip);
    tgh_flash_set('error', 'That email and password do not match an account.');
    tgh_redirect($loginPage);
}

$users->clearFailures($email);

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['user_id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email'] = $user['email'];

if ($user['role'] === 'admin') {
    tgh_redirect(tgh_url('views/admin/dashboard.php'));
}
tgh_redirect(tgh_url('views/public/home.php'));
