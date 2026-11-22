<?php
require_once __DIR__ . '/../controllers/Cart.php';

$cartPage = tgh_url('views/buyer/cart.php');

commerce_post();
$user = guardRequireLogin();

try {
    (new CartClass())->remove($user['user_id'], commerce_id($_POST['product_id'] ?? null));
    tgh_flash_set('success', 'Removed from your cart.');
} catch (Throwable $e) {
    commerce_error($e);
}
tgh_redirect($cartPage);
