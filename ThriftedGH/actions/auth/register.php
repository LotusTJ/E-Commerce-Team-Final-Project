<?php
require_once __DIR__ . '/../../core/guard.php';
require_once __DIR__ . '/../../classes/UserClass.php';

$registerPage = tgh_url('views/auth/register.php');

guardRequirePost();
guardRequireCsrf($registerPage);

$fullName = trim(tgh_input_string($_POST, 'full_name'));
$email = trim(tgh_input_string($_POST, 'email'));
$phone = trim(tgh_input_string($_POST, 'phone'));
$password = tgh_input_string($_POST, 'password');

$errors = [];
if ($fullName === '' || mb_strlen($fullName) > 120) {
    $errors[] = 'Enter your full name.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
    $errors[] = 'Enter a valid email address.';
}
if ($phone !== '' && !preg_match('/^\+?[0-9][0-9 ()-]{6,18}$/', $phone)) {
    $errors[] = 'Enter a valid phone number or leave it blank.';
}
if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors[] = 'Your password needs at least 8 characters, with a letter and a number.';
}
if ($password !== tgh_input_string($_POST, 'confirm_password')) {
    $errors[] = 'The two passwords do not match.';
}

$users = new UserClass();
if (!$errors && $users->emailExists($email)) {
    $errors[] = 'That email address is already registered.';
}

if ($errors) {
    tgh_flash_set('error', $errors);
    tgh_old_input_set($_POST, ['full_name', 'email', 'phone']);
    tgh_redirect($registerPage);
}

if ($users->registerUser($fullName, $email, password_hash($password, PASSWORD_DEFAULT), $phone)) {
    tgh_flash_set('success', 'Your account is ready. Log in to continue.');
    tgh_redirect(tgh_url('views/auth/login.php'));
}

tgh_flash_set('error', 'We could not create your account. Please try again.');
tgh_redirect($registerPage);
