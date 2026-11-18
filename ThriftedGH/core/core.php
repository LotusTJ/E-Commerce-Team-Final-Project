<?php
// Start session securely if not already started
require_once __DIR__.'/session.php';

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