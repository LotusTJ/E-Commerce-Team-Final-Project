<?php
/**
 * ThriftedGH - Front Controller / Main Router
 * 
 * Routes incoming GET requests to the appropriate view in views/
 * Allows running directly via XAMPP at http://localhost/thriftedgh/
 * 
 * Technology: Pure Vanilla PHP without frameworks.
 * Database connection is intentionally omitted for Task 2.
 */

// Retrieve requested page parameter; default to 'home'
$page = isset($_GET['page']) ? trim($_GET['page']) : 'home';

// Whitelist of allowed customer-facing pages (Task 2)
$routes = [
    'home'     => __DIR__ . '/views/home.php',
    'shop'     => __DIR__ . '/views/shop.php',
    'product'  => __DIR__ . '/views/product.php',
    'sellers'  => __DIR__ . '/views/sellers.php',
    'login'    => __DIR__ . '/views/login.php',
    'register' => __DIR__ . '/views/register.php',
    'cart'     => __DIR__ . '/views/cart.php',
];

// Dispatch to matching view file
if (array_key_exists($page, $routes) && file_exists($routes[$page])) {
    require_once $routes[$page];
} else {
    // Fallback gracefully to homepage if unknown route is given
    require_once __DIR__ . '/views/home.php';
}
