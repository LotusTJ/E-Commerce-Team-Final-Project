<?php
require_once __DIR__ . '/../core/core.php';
require_once __DIR__ . '/../classes/UserClass.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header("Location: /thriftedgh/views/buyer/login.php?error=empty_fields");
        exit();
    }

    $userModel = new UserClass();
    $user = $userModel->loginUser($email);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Session fixation protection
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        header("Location: /thriftedgh/index.php");
        exit();
    } else {
        header("Location: /thriftedgh/views/buyer/login.php?error=invalid_credentials");
        exit();
    }
} else {
    header("Location: /thriftedgh/views/buyer/login.php");
    exit();
}
?>