<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/UserClass.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');

    // Server-side validation
    if (empty($fullName) || empty($email) || empty($password)) {
        header("Location: /thriftedgh/views/buyer/register.php?error=empty_fields");
        exit();
    }

    if (strlen($password) < 6) {
        header("Location: /thriftedgh/views/buyer/register.php?error=weak_password");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /thriftedgh/views/buyer/register.php?error=invalid_email");
        exit();
    }

    $userModel = new UserClass();

    // Check duplicate email
    if ($userModel->emailExists($email)) {
        header("Location: /thriftedgh/views/buyer/register.php?error=email_exists");
        exit();
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    if ($userModel->registerUser($fullName, $email, $passwordHash, $phone)) {
        header("Location: /thriftedgh/views/buyer/login.php?success=registered");
        exit();
    } else {
        header("Location: /thriftedgh/views/buyer/register.php?error=server_error");
        exit();
    }
} else {
    header("Location: /thriftedgh/views/buyer/register.php");
    exit();
}
?>