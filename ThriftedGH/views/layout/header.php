<?php
/**
 * ThriftedGH - Shared Header Layout
 * Reusable navigation bar and head metadata for all marketplace views.
 */

// Determine base path so asset references work whether loaded via index.php or directly in views/
$scriptPath = $_SERVER['SCRIPT_NAME'];
$viewsPos = strpos($scriptPath, '/views/');
$basePath = ($viewsPos !== false) ? str_repeat('../', substr_count(substr($scriptPath, $viewsPos + 7), '/') + 1) : './';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$navLoggedIn = !empty($_SESSION['user_id']);

// Current active navigation identifier
if (!isset($activeNav)) {
    $activeNav = isset($_GET['page']) ? trim($_GET['page']) : 'home';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'ThriftedGH | Online Thrift Fashion Ghana'; ?></title>
    
    <!-- Meta description for fashion marketplace -->
    <meta name="description" content="ThriftedGH is Ghana's premier online marketplace for curated second-hand fashion, vintage clothing, and sustainable thrift wear from local sellers.">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
</head>
<body>

    <!-- Top Announcement Bar -->
    <div class="announcement-bar">
        <div class="container announcement-content">
            <span class="announcement-tag">🇬🇭 GHANA'S THRIFT HUB</span>
            <span class="announcement-text">Curated vintage & pre-loved one-of-one pieces • Campus delivery & nationwide dispatch</span>
        </div>
    </div>

    <!-- Main Shared Navigation Bar -->
    <header class="site-header">
        <div class="container header-container">
            <!-- Brand Logo -->
            <a href="<?php echo $basePath; ?>index.php?page=home" class="brand-logo" aria-label="ThriftedGH Homepage">
                <span class="brand-icon">🧥</span>
                <span class="brand-name">Thrifted<span class="brand-gh">GH</span></span>
                <span class="brand-tagline">Thrift • Renew • Style</span>
            </a>

            <!-- Mobile Hamburger Button -->
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation menu" aria-expanded="false">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>

            <!-- Navigation Links -->
            <nav class="main-nav" id="mainNav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo $basePath; ?>index.php?page=home" 
                           class="nav-link <?php echo ($activeNav === 'home') ? 'active' : ''; ?>">
                           Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $basePath; ?>index.php?page=shop" 
                           class="nav-link <?php echo ($activeNav === 'shop') ? 'active' : ''; ?>">
                           Shop
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $basePath; ?>index.php?page=sellers" 
                           class="nav-link <?php echo ($activeNav === 'sellers') ? 'active' : ''; ?>">
                           Sellers
                        </a>
                    </li>
                    <?php if ($navLoggedIn): ?>
                    <li class="nav-item">
                        <a href="<?php echo $basePath; ?>views/buyer/dashboard.php"
                           class="nav-link <?php echo ($activeNav === 'account') ? 'active' : ''; ?>">
                           Account
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $basePath; ?>logout.php" class="nav-link nav-register-btn">
                           Logout
                        </a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a href="<?php echo $basePath; ?>index.php?page=login" 
                           class="nav-link <?php echo ($activeNav === 'login') ? 'active' : ''; ?>">
                           Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo $basePath; ?>index.php?page=register" 
                           class="nav-link nav-register-btn <?php echo ($activeNav === 'register') ? 'active' : ''; ?>">
                           Register
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="<?php echo $basePath; ?>index.php?page=cart" 
                           class="nav-link nav-cart-link <?php echo ($activeNav === 'cart') ? 'active' : ''; ?>"
                           aria-label="Shopping Cart">
                           <span class="cart-icon">🛒</span> Cart
                           <span class="cart-counter">2</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content Area Starts -->
    <main class="main-content">
