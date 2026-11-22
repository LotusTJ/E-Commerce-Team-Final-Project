<?php
require_once __DIR__ . '/../../controllers/Order.php';

$ordersPage = tgh_url('views/buyer/orders.php');

commerce_post();
$user = guardRequireLogin();

try {
    $ref = $_POST['reference'] ?? null;
    if (!is_string($ref)) {
        throw new DomainException('Invalid reference.');
    }
    $order = (new OrderClass())->byReference($ref);
    if (!$order || (int)$order['buyer_id'] !== (int)$user['user_id']) {
        throw new DomainException('Order unavailable.');
    }
    commerce_verify($ref);
    tgh_flash_set('success', 'Payment status checked.');
} catch (Throwable $e) {
    commerce_error($e);
}
tgh_redirect($ordersPage);
