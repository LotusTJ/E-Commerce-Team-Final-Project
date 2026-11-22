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

if (isset($headerMode)) {
    $navMode = $headerMode;
} elseif ($navRole === 'seller' || $navRole === 'admin') {
    $navMode = $navRole;
} else {
    $navMode = 'public';
}
$brandHref = $navMode === 'public' ? tgh_url('views/public/home.php') : tgh_role_home($navMode);

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
    <?php if ($navMode !== 'public'): ?>
    <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
</head>
<body>
    <?php if ($navMode === 'public'): ?>
    <div class="announcement-bar">
        <div class="container announcement-content">
            <span class="announcement-tag"><?php echo tgh_icon('map-pin'); ?> Ghana</span>
            <span class="announcement-text">Second-hand pieces from approved sellers. Campus delivery and nationwide dispatch.</span>
        </div>
    </div>
    <?php endif; ?>

    <header class="site-header">
        <div class="container header-container">
            <?php if ($navMode === 'plain'): ?>
            <span class="brand-logo">
                <span class="brand-icon"><?php echo tgh_icon('shirt'); ?></span>
                <span class="brand-name">Thrifted<span class="brand-gh">GH</span></span>
            </span>
            <?php else: ?>
            <a href="<?php echo tgh_e($brandHref); ?>" class="brand-logo" aria-label="ThriftedGH home">
                <span class="brand-icon"><?php echo tgh_icon('shirt'); ?></span>
                <span class="brand-name">Thrifted<span class="brand-gh">GH</span><?php if ($navMode === 'seller'): ?> <span class="brand-portal">Seller</span><?php elseif ($navMode === 'admin'): ?> <span class="brand-portal">Admin</span><?php endif; ?></span>
            </a>
            <?php endif; ?>

            <?php if ($navMode !== 'plain'): ?>
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation menu" aria-expanded="false">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>

            <nav class="main-nav" id="mainNav">
                <ul class="nav-list">
                    <?php
                    $logout = '<li class="nav-item"><a class="nav-link nav-register-btn" href="' . htmlspecialchars(tgh_url('actions/logout_action.php'), ENT_QUOTES, 'UTF-8') . '">Sign out</a></li>';
                    if ($navMode === 'seller') {
                        echo tgh_nav_link('views/seller/dashboard.php', 'Dashboard', 'seller', $activeNav);
                        echo tgh_nav_link('views/seller/product_form.php', 'List an item', 'seller_add', $activeNav);
                        echo tgh_nav_link('views/seller/orders.php', 'Orders', 'seller_orders', $activeNav);
                        echo $logout;
                    } elseif ($navMode === 'admin') {
                        echo tgh_nav_link('views/admin/dashboard.php', 'Dashboard', 'admin', $activeNav);
                        echo tgh_nav_link('views/admin/applications.php', 'Applications', 'admin_applications', $activeNav);
                        echo tgh_nav_link('views/admin/orders.php', 'Orders', 'admin_orders', $activeNav);
                        echo $logout;
                    } else {
                        echo tgh_nav_link('views/public/home.php', 'Home', 'home', $activeNav);
                        echo tgh_nav_link('views/public/shop.php', 'Shop', 'shop', $activeNav);
                        echo tgh_nav_link('views/public/sellers.php', 'Sellers', 'sellers', $activeNav);
                        echo tgh_nav_link('views/buyer/find_my_fit.php', 'Find My Fit', 'find_my_fit', $activeNav);
                        if ($navLoggedIn) {
                            echo tgh_nav_link('views/buyer/dashboard.php', 'Account', 'dashboard', $activeNav);
                            echo tgh_nav_link('views/buyer/orders.php', 'Orders', 'orders', $activeNav);
                            echo $logout;
                        } else {
                            echo tgh_nav_link('views/auth/login.php', 'Sign in', 'login', $activeNav);
                            echo tgh_nav_link('views/auth/register.php', 'Join', 'register', $activeNav, 'nav-register-btn');
                        }
                        echo tgh_nav_link('views/buyer/cart.php', tgh_icon('cart', 'cart-icon') . ' Cart', 'cart', $activeNav, 'nav-cart-link');
                    }
                    ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </header>

    <main class="main-content">
