<?php
if(PHP_SAPI!=='cli') {http_response_code(404);exit;}
require_once __DIR__.'/../classes/OrderClass.php';
require_once __DIR__.'/../payments/PaystackService.php';
$model=new OrderClass();$provider=new PaystackService();
foreach($model->pendingReferences() as $row) {
    try {
        $ref=$row['order_reference'];$data=$provider->verify($ref);
        if(($data['reference']??null)!==$ref || ($data['domain']??null)!=='test') throw new DomainException('Payment reference or mode did not match.');
        if(($data['status']??'')==='success') $model->settle($ref,$data);
        elseif(in_array($data['status']??'', ['failed','abandoned','reversed'],true)) $model->fail($ref);
    } catch(Throwable $e) {error_log('Payment reconciliation: '.$e->getMessage());}
}
$model->expire();
echo "Pending payments reconciled and expired reservations released.\n";
