<?php
require_once __DIR__ . '/../controllers/Cart.php';

$cartPage = tgh_url('views/buyer/cart.php');

commerce_post();
$user = guardRequireLogin();

try {
    (new CartClass())->add($user['user_id'], commerce_id($_POST['product_id'] ?? null));
    tgh_flash_set('success', 'Added to your cart.');
} catch (Throwable $e) {
    commerce_error($e);
}
tgh_redirect($cartPage);
