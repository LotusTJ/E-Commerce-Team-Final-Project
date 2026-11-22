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
    $model = new OrderClass();
    $order = $model->byReference($ref);
    if (!$order || (int)$order['buyer_id'] !== (int)$user['user_id']) {
        throw new DomainException('Order unavailable.');
    }
    try {
        commerce_verify($ref);
    } catch (PaystackRequestException $e) {
        error_log('Cancel verify: ' . $e->getMessage());
    }
    $model->fail($ref);
    $order = $model->byReference($ref);
    tgh_flash_set('success', $order['payment_status'] === 'paid' ? 'Your payment had already gone through, so this order was kept.' : 'Checkout cancelled. Your items are available again.');
} catch (Throwable $e) {
    commerce_error($e);
}
tgh_redirect($ordersPage);
