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
} catch (Throwable $e) { commerce_error($e); }
tgh_redirect('../index.php?page=orders');
