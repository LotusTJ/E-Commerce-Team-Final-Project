<?php
require_once __DIR__.'/../controllers/Order.php';
$user=commerce_user();$ref=$_GET['reference']??null;
if(!is_string($ref) || !preg_match('/^TGH-[a-f0-9]{32}$/D',$ref)) {http_response_code(404);exit('Order unavailable');}
$o=(new OrderClass())->byReference($ref);
if(!$o || (int)$o['buyer_id']!==(int)$user['user_id']) {http_response_code(404);exit('Order unavailable');}
$pageTitle='Payment result | ThriftedGH';require __DIR__.'/layout/header.php';
$heading=!empty($o['payment_review'])?'Payment requires review':($o['payment_status']==='paid'?'Payment successful':($o['payment_status']==='failed'?'Payment unsuccessful or reservation expired':'Payment awaiting confirmation'));
?>
<section class="section"><div class="container"><h1><?=tgh_e($heading)?></h1><?php tgh_render_flash(); ?>
<?php if(!empty($o['payment_review'])): ?><p>Your payment arrived after the reservation was released. Please contact the marketplace administrator with your order reference for a refund check.</p>
<?php elseif($o['payment_status']==='paid'): ?><p>Your payment has been verified and your items are purchased. Track fulfilment in your order.</p>
<?php elseif($o['payment_status']==='failed'): ?><p>This order has not been marked paid. Its items are no longer reserved. If your payment is still processing, check its status in your order before paying again.</p>
<?php else: ?><p>Your order is awaiting a verified payment result. Check its payment status from your order.</p><?php endif; ?>
<p>Reference: <?=tgh_e($o['order_reference'])?></p><a class="btn btn-primary" href="<?=tgh_e($basePath)?>index.php?page=orders&id=<?=(int)$o['order_id']?>">View order</a></div></section>
<?php require __DIR__.'/layout/footer.php'; ?>
