<?php
require_once __DIR__.'/../controllers/Cart.php';
commerce_post(); $user=commerce_user();
try {
    $id=commerce_id($_POST['product_id']??null); $cart=new CartClass();
    if (($_POST['action']??'')==='add') $cart->add($user['user_id'],$id);
    elseif (($_POST['action']??'')==='remove') $cart->remove($user['user_id'],$id);
    else throw new DomainException('Unknown cart action.');
    tgh_flash_set('success','Your cart has been updated.');
} catch (Throwable $e) { commerce_error($e); }
tgh_redirect('../index.php?page=cart');
