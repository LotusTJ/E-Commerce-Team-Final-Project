<?php
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/auth.php';

$basePath = tgh_base();
$navUser = null;
try {
    $navUser = authCurrentUser();
} catch (Throwable $e) {
    error_log('Header user lookup: ' . $e->getMessage());
}
$navLoggedIn = $navUser !== null;
$navRole = $navLoggedIn ? $navUser['role'] : null;

if (!isset($activeNav)) {
    $activeNav = basename($_SERVER['SCRIPT_NAME'] ?? '', '.php');
}

if (!function_exists('tgh_nav_link')) {
    function tgh_nav_link($href, $label, $key, $active, $extra = '')
    {
        $class = trim('nav-link ' . $extra . ($active === $key ? ' active' : ''));
        return '<li class="nav-item"><a class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" href="' . htmlspecialchars(tgh_url($href), ENT_QUOTES, 'UTF-8') . '">' . $label . '</a></li>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'ThriftedGH | Second-hand clothing from Ghanaian sellers'; ?></title>
    <meta name="description" content="Buy and sell second-hand clothing in Ghana. Every piece is one of one, listed by approved student and shop sellers.">
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
</head>
<body>
    <div class="announcement-bar">
        <div class="container announcement-content">
            <span class="announcement-tag"><?php echo tgh_icon('map-pin'); ?> Ghana</span>
            <span class="announcement-text">Second-hand pieces from approved sellers. Campus delivery and nationwide dispatch.</span>
        </div>
    </div>

    <header class="site-header">
        <div class="container header-container">
            <a href="<?php echo $basePath; ?>views/public/home.php" class="brand-logo" aria-label="ThriftedGH home">
                <span class="brand-icon"><?php echo tgh_icon('shirt'); ?></span>
                <span class="brand-name">Thrifted<span class="brand-gh">GH</span></span>
            </a>

            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation menu" aria-expanded="false">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>

            <nav class="main-nav" id="mainNav">
                <ul class="nav-list">
                    <?php
                    echo tgh_nav_link('views/public/home.php', 'Home', 'home', $activeNav);
                    echo tgh_nav_link('views/public/shop.php', 'Shop', 'shop', $activeNav);
                    echo tgh_nav_link('views/public/sellers.php', 'Sellers', 'sellers', $activeNav);
                    echo tgh_nav_link('views/buyer/find_my_fit.php', 'Find My Fit', 'find_my_fit', $activeNav);
                    if ($navLoggedIn) {
                        echo tgh_nav_link('views/buyer/dashboard.php', 'Account', 'dashboard', $activeNav);
                        echo tgh_nav_link('views/buyer/orders.php', 'Orders', 'orders', $activeNav);
                        if ($navRole === 'seller') {
                            echo tgh_nav_link('views/seller/dashboard.php', 'Seller', 'seller', $activeNav);
                        }
                        if ($navRole === 'admin') {
                            echo tgh_nav_link('views/admin/dashboard.php', 'Admin', 'admin', $activeNav);
                        }
                        echo '<li class="nav-item"><a class="nav-link nav-register-btn" href="' . htmlspecialchars(tgh_url('actions/auth/logout.php'), ENT_QUOTES, 'UTF-8') . '">Logout</a></li>';
                    } else {
                        echo tgh_nav_link('views/auth/login.php', 'Login', 'login', $activeNav);
                        echo tgh_nav_link('views/auth/register.php', 'Register', 'register', $activeNav, 'nav-register-btn');
                    }
                    echo tgh_nav_link('views/buyer/cart.php', tgh_icon('cart', 'cart-icon') . ' Cart', 'cart', $activeNav, 'nav-cart-link');
                    ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
