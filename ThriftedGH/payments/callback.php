<?php
require_once __DIR__ . '/../controllers/Order.php';

try {
    commerce_verify($_GET['reference'] ?? null);
    tgh_flash_set('success', 'Payment checked. Your order shows the verified result.');
} catch (Throwable $e) {
    commerce_error($e);
}

$reference = $_GET['reference'] ?? null;
if (is_string($reference) && preg_match('/^TGH-[a-f0-9]{32}$/D', $reference)) {
    tgh_redirect(tgh_url('views/buyer/payment_result.php') . '?reference=' . rawurlencode($reference));
}
tgh_redirect(tgh_url('views/buyer/orders.php'));
