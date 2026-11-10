<?php
require_once __DIR__ . '/../../core/core.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ThriftedGH - Multi-Vendor Marketplace</title>
    <link rel="stylesheet" href="/thriftedgh/css/style.css">
</head>
<body>
    <header>
        <nav class="nav-container">
            <a href="/thriftedgh/index.php" class="logo">THRIFTEDGH</a>
            <ul class="nav-links">
                <li><a href="/thriftedgh/index.php">Home</a></li>
                <li><a href="/thriftedgh/views/shop.php">Shop</a></li>
                <li><a href="/thriftedgh/views/sellers.php">Sellers</a></li>
                <li><a href="/thriftedgh/views/cart.php">Cart</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="/thriftedgh/views/buyer/dashboard.php">Account</a></li>
                    <li><a href="/thriftedgh/logout.php" style="color: #dc2626;">Logout</a></li>
                <?php else: ?>
                    <li><a href="/thriftedgh/views/buyer/login.php">Login</a></li>
                    <li><a href="/thriftedgh/views/buyer/register.php" class="btn-primary">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>