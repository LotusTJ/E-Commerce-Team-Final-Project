<?php
require_once __DIR__ . '/../../controllers/Order.php';

$salesPage = tgh_url('views/seller/orders.php');

commerce_post();
$user = guardRequireSeller();

try {
    $next = $_POST['status'] ?? null;
    if (!is_string($next)) {
        throw new DomainException('Invalid status.');
    }
    (new OrderClass())->fulfil($user['user_id'], commerce_id($_POST['item_id'] ?? null), $next);
    tgh_flash_set('success', 'Fulfilment status updated.');
} catch (Throwable $e) {
    commerce_error($e);
}
tgh_redirect($salesPage);
