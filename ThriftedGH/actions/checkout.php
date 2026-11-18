<?php
require_once __DIR__.'/../controllers/Order.php';
commerce_post(); $user=commerce_user();
try {
    // Validate environment before reserving inventory.
    $service=new PaystackService(); $service->callbackUrl();
    $model=new OrderClass(); $model->expire();
    $order=$model->create($user['user_id'],$_POST['delivery_address']??null);
    // An ambiguous network failure must not release inventory: Paystack may have accepted initialization.
    $url=$service->initialize($order);
    header('Location: '.$url, true, 303); exit;
} catch (Throwable $e) {
    if ($e instanceof PaystackRequestException && $e->definitelyRejected && isset($order)) {
        try { $model->fail($order['order_reference']); }
        catch (Throwable $releaseError) { error_log('Checkout reservation release failed: '.$releaseError->getMessage()); }
    }
    if ($e instanceof PaystackRequestException) {
        error_log('Checkout: '.$e->getMessage());
        tgh_flash_set('error',$e->getMessage());
    } else commerce_error($e);
}
tgh_redirect('../index.php?page=orders');
