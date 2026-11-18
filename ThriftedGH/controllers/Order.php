<?php
require_once __DIR__.'/Cart.php';
require_once __DIR__.'/../classes/OrderClass.php';
require_once __DIR__.'/../payments/PaystackService.php';
function commerce_verify($ref) {
    if (!is_string($ref) || !preg_match('/^TGH-[a-f0-9]{32}$/D',$ref)) throw new DomainException('Invalid payment reference.');
    $model=new OrderClass();
    if (!$model->byReference($ref)) throw new DomainException('Unknown payment reference.');
    $data=(new PaystackService())->verify($ref);
    if (($data['reference']??null)!==$ref || ($data['domain']??null)!=='test') throw new DomainException('Payment reference or mode did not match.');
    if (($data['status']??'')==='success') return $model->settle($ref,$data);
    if (in_array($data['status']??'', ['failed','abandoned','reversed'],true)) $model->fail($ref);
    return $model->byReference($ref);
}
