<?php
require_once __DIR__.'/../controllers/Order.php';
commerce_post(); $user=commerce_user();
try {
    // Validate environment before reserving inventory.
    $service=new PaystackService(); $service->callbackUrl();
    $model=new OrderClass(); $model->expire();
    $order=$model->create($user['user_id'],$_POST['delivery_address']??null);
    $url=$service->initialize($order);
    header('Location: '.$url, true, 303); exit;
} catch (Throwable $e) {
    $released=false;
    if (isset($order)) {
        try { $model->fail($order['order_reference']); $released=true; }
        catch (Throwable $releaseError) { error_log('Checkout reservation release failed: '.$releaseError->getMessage()); }
    }
    if ($e instanceof PaystackRequestException) {
        error_log('Checkout: '.$e->getMessage());
        tgh_flash_set('error',$released && !$e->definitelyRejected ? 'We could not reach the payment provider. Your items were released, so please try again.' : $e->getMessage());
    } else commerce_error($e);
}
tgh_redirect('../index.php?page=orders');
