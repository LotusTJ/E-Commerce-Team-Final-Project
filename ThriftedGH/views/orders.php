<?php
require_once __DIR__.'/../controllers/Order.php';
$user=commerce_user(); $scope=$_GET['scope']??'buyer';
if(!is_string($scope) || !in_array($scope,['buyer','seller','admin'],true)) $scope='buyer';
if($scope==='admin' && $user['role']!=='admin') {http_response_code(403);exit('Access denied');}
$model=new OrderClass();$orders=[];$detail=null;$summary=[];$loadError=false;
try {
 if(isset($_GET['id'])) { $detail=$model->detail($user,commerce_id($_GET['id']),$scope); if(!$detail){http_response_code(404);$loadError=true;} }
 else $orders=$model->listing($user,$scope);
 if($scope==='admin') $summary=$model->reconciliation();
} catch(Throwable $e) {commerce_error($e);$loadError=true;}
$pageTitle='Orders | ThriftedGH';require __DIR__.'/layout/header.php';
?>
<section class="section"><div class="container"><h1><?=$scope==='seller'?'Seller orders':($scope==='admin'?'Order administration':'Your orders')?></h1>
<nav class="m4-order-nav"><a href="<?=tgh_e($basePath)?>index.php?page=orders">My purchases</a> · <a href="<?=tgh_e($basePath)?>index.php?page=orders&scope=seller">My seller orders</a><?php if($user['role']==='admin'): ?> · <a href="<?=tgh_e($basePath)?>index.php?page=orders&scope=admin">Admin overview</a><?php endif; ?></nav>
<?php tgh_render_flash(); if($loadError): ?><p role="alert">Order information is unavailable.</p>
<?php elseif($detail): ?><h2>Order <?=tgh_e($detail['order_reference'])?></h2><p>Payment: <?=tgh_e($detail['payment_status'])?></p>
<?php if($detail['payment_status']==='paid'): $completed=count(array_filter($detail['items'],fn($i)=>$i['fulfilment_status']==='delivered'))===count($detail['items']); ?><p>Sale progress: <?=$completed?'Completed — all items delivered':'Paid — fulfilment in progress'?></p><?php endif; ?>
<?php if(!empty($detail['payment_review'])): ?><p role="alert">A payment was received after inventory was released. This order requires administrator review and a refund check. It will not be fulfilled automatically.</p><?php endif; ?>
<?php if($detail['payment_status']==='pending'): ?><p>Reservation expires at <?=tgh_e($detail['reservation_expires_at'])?> UTC. If checkout was interrupted, check payment status or wait for the reservation to expire before starting again.</p><?php endif; ?>
<?php if(isset($detail['total_amount'])): ?><p>Total <?=tgh_e(tgh_format_price($detail['total_amount']))?></p><?php endif; ?>
<p>Delivery address: <?=nl2br(tgh_e($detail['delivery_address']))?></p>
<?php foreach($detail['items'] as $item): ?><article class="m4-order-item"><h3><?=tgh_e($item['title_at_purchase']??('Product #'.$item['product_id']))?></h3><p><?=tgh_e($item['shop_name'])?> · <?=tgh_e(tgh_format_price($item['price_at_purchase']))?> · Fulfilment: <?=tgh_e($item['fulfilment_status'])?></p>
<?php if($scope!=='buyer'): ?><p>Commission <?=tgh_e(tgh_format_price($item['commission_amount']))?> · Seller earnings <?=tgh_e(tgh_format_price(CommerceModel::money(CommerceModel::cents($item['price_at_purchase'])-CommerceModel::cents($item['commission_amount']))))?></p><?php endif; ?>
<?php $next=['pending'=>'processing','processing'=>'shipped','shipped'=>'delivered'][$item['fulfilment_status']]??null; if($scope==='seller' && $next): ?><form method="post" action="<?=tgh_e($basePath)?>actions/orders.php"><?=tgh_csrf_field()?><input type="hidden" name="action" value="fulfil"><input type="hidden" name="item_id" value="<?=(int)$item['order_item_id']?>"><button class="btn btn-outline" name="status" value="<?=tgh_e($next)?>">Mark <?=tgh_e($next)?></button></form><?php endif; ?></article><?php endforeach; ?>
<?php if($scope==='buyer' && $detail['payment_status']!=='paid'): ?><form method="post" action="<?=tgh_e($basePath)?>actions/orders.php"><?=tgh_csrf_field()?><input type="hidden" name="action" value="verify"><input type="hidden" name="reference" value="<?=tgh_e($detail['order_reference'])?>"><button class="btn btn-primary">Check payment status</button></form><?php endif; ?>
<?php if($scope==='buyer' && $detail['payment_status']==='pending'): ?><form method="post" action="<?=tgh_e($basePath)?>actions/orders.php"><?=tgh_csrf_field()?><input type="hidden" name="action" value="cancel"><input type="hidden" name="reference" value="<?=tgh_e($detail['order_reference'])?>"><button class="btn btn-outline">Cancel checkout</button></form><?php endif; ?>
<?php else: ?>
<p>Paid means payment is verified. A sale is completed when its items are delivered.</p>
<?php if(!$orders): ?><p>No orders yet.</p><?php endif; ?>
<?php foreach($orders as $o): ?><article class="m4-order-item"><a href="<?=tgh_e($basePath)?>index.php?page=orders&scope=<?=tgh_e($scope)?>&id=<?=(int)$o['order_id']?>"><?=tgh_e($o['order_reference'])?></a><p><?=tgh_e($o['created_at'])?> · Payment: <?=tgh_e($o['payment_status'])?><?=!empty($o['payment_review'])?' · PAYMENT REVIEW REQUIRED':''?></p></article><?php endforeach; ?>
<?php endif; ?>
<?php if($scope==='admin'): ?><h2>Verified sales and commission</h2><div class="m4-table-wrap"><table><thead><tr><th>Seller</th><th>Gross</th><th>Commission (10%)</th><th>Seller earnings</th><th>Delivered sales (gross)</th></tr></thead><tbody><?php foreach($summary as $s): ?><tr><td><?=tgh_e($s['shop_name'])?></td><td><?=tgh_e(tgh_format_price($s['gross']))?></td><td><?=tgh_e(tgh_format_price($s['commission']))?></td><td><?=tgh_e(tgh_format_price($s['net']))?></td><td><?=tgh_e(tgh_format_price($s['completed_gross']))?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</div></section><?php require __DIR__.'/layout/footer.php'; ?>
