<?php
require_once __DIR__.'/../controllers/Order.php';
commerce_post(); $user=commerce_user();
try {
    if (($_POST['action']??'')==='verify') {
        $ref=$_POST['reference']??null;
        if (!is_string($ref)) throw new DomainException('Invalid reference.');
        $o=(new OrderClass())->byReference($ref);
        if (!$o || (int)$o['buyer_id']!==(int)$user['user_id']) throw new DomainException('Order unavailable.');
        commerce_verify($ref);
        tgh_flash_set('success','Payment status checked.');
    } elseif (($_POST['action']??'')==='fulfil') {
        $next=$_POST['status']??null;
        if (!is_string($next)) throw new DomainException('Invalid status.');
        (new OrderClass())->fulfil($user['user_id'],commerce_id($_POST['item_id']??null),$next);
        tgh_flash_set('success','Fulfilment status updated.');
    } else throw new DomainException('Unknown action.');
} catch (Throwable $e) { commerce_error($e); }
tgh_redirect('../index.php?page=orders&scope='.((($_POST['action']??'')==='fulfil')?'seller':'buyer'));
