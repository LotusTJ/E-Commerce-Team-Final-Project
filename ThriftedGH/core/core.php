<?php
// Start session securely if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function checkLogin() {
    if (!isLoggedIn()) {
        header("Location: /thriftedgh/views/buyer/login.php");
        exit();
    }
}
?>