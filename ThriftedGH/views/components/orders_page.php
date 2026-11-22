<?php
require_once __DIR__.'/../../controllers/Order.php';
require_once __DIR__.'/../../classes/CatalogueRepository.php';
require_once __DIR__.'/order_ui.php';
$model=new OrderClass();$orders=[];$detail=null;$summary=[];$loadError=false;
try {
 $model->expire();
 if(isset($_GET['id'])) { $detail=$model->detail($user,commerce_id($_GET['id']),$scope); if(!$detail){http_response_code(404);$loadError=true;} }
 else $orders=$model->listing($user,$scope);
 if($scope==='admin') $summary=$model->reconciliation();
} catch(Throwable $e) {commerce_error($e);$loadError=true;}
$pageTitle='Orders | ThriftedGH';$activeNav=$scope==='buyer'?'orders':$scope.'_orders';require __DIR__.'/../layout/header.php';
$scopeUrls=['buyer'=>tgh_url('views/buyer/orders.php'),'seller'=>tgh_url('views/seller/orders.php'),'admin'=>tgh_url('views/admin/orders.php')];
$ordersUrl=$scopeUrls[$scope];
$titles=['buyer'=>'Your orders','seller'=>'Sales from your shop','admin'=>'Order administration'];
?>
<section class="section tg-page"><div class="container tg-wrap">
<h1 class="tg-title"><?=tgh_e($detail?($scope==='seller'?'Sale details':'Receipt'):$titles[$scope])?></h1>
<?php if($user['role']!=='admin'): ?><nav class="tg-tabs tg-noprint" aria-label="Order views"><a href="<?=tgh_e($scopeUrls['buyer'])?>" class="<?=$scope==='buyer'?'is-active':''?>">My purchases</a><?php if($user['role']==='seller'): ?><a href="<?=tgh_e($scopeUrls['seller'])?>" class="<?=$scope==='seller'?'is-active':''?>">My sales</a><?php endif; ?></nav><?php endif; ?>
<?php tgh_render_flash(); if($loadError): ?>
<div class="tg-card tg-empty"><div class="tg-empty-icon"><?=tgh_icon('receipt')?></div><h2>We could not load that order</h2><p>It may not exist, or it may belong to another account.</p><a class="btn btn-primary" href="<?=tgh_e($ordersUrl)?>">Back to orders</a></div>

<?php elseif($detail): ?>
<?php
 $items=$detail['items'];
 [$statusLabel,$statusClass]=tg_status($detail,$items);
 $paid=$detail['payment_status']==='paid'; $pending=$detail['payment_status']==='pending'; $failed=$detail['payment_status']==='failed';
 $stage=tg_stage($items);
?>
<p class="tg-back tg-noprint"><a href="<?=tgh_e($ordersUrl)?>"><?=tgh_icon('arrow-left')?> <?=$scope==='seller'?'All sales':'All orders'?></a></p>

<?php if($scope!=='seller'): ?>
<?php if(!empty($detail['payment_review'])): ?><div class="tg-note tg-note-warn" role="alert"><b>Payment under review.</b> A payment was received after the pieces were released. An administrator will check it and arrange a refund if needed. This order will not be fulfilled automatically.</div><?php endif; ?>
<?php if($pending): ?><div class="tg-note tg-note-warn tg-noprint"><b>Waiting for payment.</b> Your pieces are held <span class="tg-countdown" data-expires="<?=tgh_e($detail['reservation_expires_at'])?>">for a little while</span>. Finished paying? Check the status below. Changed your mind? Cancel and the pieces go back on sale.</div><?php endif; ?>
<?php if($failed): ?><div class="tg-note tg-note-bad tg-noprint"><b>This checkout was not completed.</b> The pieces are no longer held for you. If you were charged, check the payment status before paying again.</div><?php endif; ?>
<?php if($paid): ?><div class="tg-card tg-track tg-noprint"><h2>Order progress</h2><?php tg_timeline(['Order placed','Payment confirmed','Being prepared','On the way','Delivered'],2+$stage); ?></div>
<?php elseif($pending): ?><div class="tg-card tg-track tg-noprint"><h2>Order progress</h2><?php tg_timeline(['Order placed','Payment confirmed','Being prepared','On the way','Delivered'],1); ?></div><?php endif; ?>

<article class="tg-receipt">
<header class="tg-receipt-head"><div><div class="tg-brand">Thrifted<span>GH</span></div><div class="tg-label">Receipt</div></div>
<div class="tg-receipt-meta"><span class="tg-badge tg-badge-<?=tgh_e($statusClass)?>"><?=tgh_e($statusLabel)?></span><div class="tg-ref-row"><span class="tg-ref" id="refText"><?=tgh_e($detail['order_reference'])?></span><button type="button" class="tg-chip tg-noprint" data-copy="#refText">Copy</button></div>
<div class="tg-meta">Placed <?=tgh_e(tg_when($detail['created_at']))?><?php if($paid && !empty($detail['paid_at'])): ?><br>Paid <?=tgh_e(tg_when($detail['paid_at']))?><?php endif; ?></div></div></header>
<div class="tg-receipt-body">
<h2 class="tg-section-h">Items</h2>
<?php foreach($items as $item): ?>
<div class="tg-line tg-line-receipt"><img class="tg-thumb" src="<?=tg_img($basePath,$item['image_path']??'')?>" alt=""><div class="tg-line-body"><h3><?=tgh_e($item['title_at_purchase']??('Product #'.$item['product_id']))?></h3><p class="tg-meta">Sold by <?=tgh_e($item['shop_name'])?></p><?php if($paid): ?><span class="tg-pill tg-pill-<?=tgh_e($item['fulfilment_status'])?>"><?=tgh_e(tg_fulfilment_label($item['fulfilment_status']))?></span><?php endif; ?></div><div class="tg-price"><?=tgh_e(tgh_format_price($item['price_at_purchase']))?></div></div>
<?php endforeach; ?>
<dl class="tg-rows tg-rows-receipt"><div><dt>Subtotal</dt><dd><?=tgh_e(tgh_format_price($detail['total_amount']))?></dd></div><div><dt>Delivery</dt><dd>None added</dd></div></dl>
<div class="tg-total"><span><?=$paid?'Total paid':'Total'?></span><strong><?=tgh_e(tgh_format_price($detail['total_amount']))?></strong></div>
<div class="tg-two"><div><h2 class="tg-section-h">Deliver to</h2><p class="tg-address"><?=nl2br(tgh_e($detail['delivery_address']))?></p></div>
<div><h2 class="tg-section-h">Payment</h2><p class="tg-address">Paystack · GHS<br><?=$paid?'Verified by Paystack':($pending?'Awaiting confirmation':'Not completed')?></p></div></div>
</div>
<footer class="tg-receipt-foot">Thank you for buying second-hand.</footer>
</article>

<div class="tg-actions tg-noprint">
<?php if($paid): ?><button type="button" class="btn btn-primary" data-print>Print or save as PDF</button><?php endif; ?>
<?php if($scope==='buyer' && $detail['payment_status']!=='paid'): ?><form method="post" action="<?=tgh_e(tgh_url('actions/orders/verify.php'))?>"><?=tgh_csrf_field()?><input type="hidden" name="reference" value="<?=tgh_e($detail['order_reference'])?>"><button class="btn btn-primary" data-busy="Checking…">Check payment status</button></form><?php endif; ?>
<?php if($scope==='buyer' && $pending): ?><form method="post" action="<?=tgh_e(tgh_url('actions/orders/cancel.php'))?>"><?=tgh_csrf_field()?><input type="hidden" name="reference" value="<?=tgh_e($detail['order_reference'])?>"><button class="btn btn-outline" data-confirm="Tap again to confirm">Cancel checkout</button></form><?php endif; ?>
<?php if($failed): ?><a class="btn btn-outline" href="<?=tgh_e($basePath)?>views/public/shop.php">Back to the shop</a><?php endif; ?>
</div>

<?php else: ?>
<div class="tg-card tg-sale-head"><div><div class="tg-label">Order</div><div class="tg-ref"><?=tgh_e($detail['order_reference'])?></div></div><div class="tg-meta">Placed <?=tgh_e(tg_when($detail['created_at']))?></div></div>
<div class="tg-card"><h2>Deliver to</h2><p class="tg-address"><?=nl2br(tgh_e($detail['delivery_address']))?></p></div>
<?php foreach($items as $item):
 $sellerNet=CommerceModel::cents($item['price_at_purchase'])-CommerceModel::cents($item['commission_amount']);
 $next=['pending'=>'processing','processing'=>'shipped','shipped'=>'delivered'][$item['fulfilment_status']]??null;
 $rank=['pending'=>0,'processing'=>1,'shipped'=>2,'delivered'=>3][$item['fulfilment_status']]??0; ?>
<div class="tg-card tg-sale-item">
<div class="tg-line tg-line-receipt"><img class="tg-thumb" src="<?=tg_img($basePath,$item['image_path']??'')?>" alt=""><div class="tg-line-body"><h3><?=tgh_e($item['title_at_purchase']??('Product #'.$item['product_id']))?></h3><p class="tg-meta"><?=tgh_e($item['shop_name'])?></p><span class="tg-pill tg-pill-<?=tgh_e($item['fulfilment_status'])?>"><?=tgh_e(tg_fulfilment_label($item['fulfilment_status']))?></span></div><div class="tg-price"><?=tgh_e(tgh_format_price($item['price_at_purchase']))?></div></div>
<?php tg_timeline(['Paid','Preparing','Shipped','Delivered'],1+$rank); ?>
<dl class="tg-rows"><div><dt>Sale price</dt><dd><?=tgh_e(tgh_format_price($item['price_at_purchase']))?></dd></div><div><dt>Marketplace commission</dt><dd>− <?=tgh_e(tgh_format_price($item['commission_amount']))?></dd></div></dl>
<div class="tg-total"><span>You earn</span><strong><?=tgh_e(tgh_format_price(CommerceModel::money($sellerNet)))?></strong></div>
<?php if($scope==='seller' && $next): ?><form method="post" action="<?=tgh_e(tgh_url('actions/orders/fulfil.php'))?>" class="tg-actions"><?=tgh_csrf_field()?><input type="hidden" name="item_id" value="<?=(int)$item['order_item_id']?>"><button class="btn btn-primary" name="status" value="<?=tgh_e($next)?>" data-busy="Updating…">Mark as <?=tgh_e(['processing'=>'being prepared','shipped'=>'shipped','delivered'=>'delivered'][$next])?></button></form><?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php else: ?>
<?php
 $counts=['all'=>count($orders),'paid'=>0,'pending'=>0,'failed'=>0];
 foreach($orders as $o) { [, $c]=tg_status($o); if($c==='review') $c='paid'; $counts[$c]=($counts[$c]??0)+1; }
?>
<?php if(!$orders): ?>
<div class="tg-card tg-empty"><div class="tg-empty-icon"><?=tgh_icon($scope==='seller'?'package':'bag')?></div><h2><?=$scope==='seller'?'No sales yet':'No orders yet'?></h2><p><?=$scope==='seller'?'Paid orders for your pieces will show up here the moment they come in.':'When you buy something it will appear here with a full receipt and live progress.'?></p><?php if($scope!=='seller'): ?><a class="btn btn-primary" href="<?=tgh_e($basePath)?>views/public/shop.php">Start shopping</a><?php endif; ?></div>
<?php else: ?>
<?php if($scope!=='seller'): ?><div class="tg-chips tg-noprint" role="group" aria-label="Filter orders"><button type="button" class="tg-chip is-active" data-filter="all">All <span><?=$counts['all']?></span></button><button type="button" class="tg-chip" data-filter="paid">Paid <span><?=$counts['paid']?></span></button><button type="button" class="tg-chip" data-filter="pending">Awaiting payment <span><?=$counts['pending']?></span></button><button type="button" class="tg-chip" data-filter="failed">Not completed <span><?=$counts['failed']?></span></button></div><?php endif; ?>
<div id="orderList">
<?php foreach($orders as $o):
 [$label,$class]=tg_status($o); $filter=$class==='review'?'paid':$class;
 $thumbs=array_values(array_filter(explode('|',(string)($o['thumbs']??''))));
 $link=$ordersUrl.'?id='.(int)$o['order_id'];
 $count=(int)($o['item_count']??0); ?>
<article class="tg-order" data-status="<?=tgh_e($filter)?>">
<?php if($scope==='buyer'): ?><div class="tg-order-thumbs"><?php foreach(array_slice($thumbs,0,3) as $t): ?><img class="tg-thumb" src="<?=tg_img($basePath,$t)?>" alt=""><?php endforeach; ?><?php if(!$thumbs): ?><span class="tg-thumb tg-thumb-empty"><?=tgh_icon('shirt')?></span><?php endif; ?></div>
<?php else: ?><div class="tg-order-thumbs"><span class="tg-thumb tg-thumb-empty"><?=tgh_icon($scope==='seller'?'package':'receipt')?></span></div><?php endif; ?>
<div class="tg-order-main">
<div class="tg-order-top"><a class="tg-order-ref" href="<?=tgh_e($link)?>">Order <?=tgh_e(tg_short_ref($o['order_reference']))?></a><span class="tg-badge tg-badge-<?=tgh_e($class)?>"><?=tgh_e($label)?></span></div>
<p class="tg-meta"><?php if(!empty($o['first_title'])): ?><?=tgh_e($o['first_title'])?><?=$count>1?' + '.($count-1).' more':''?> · <?php endif; ?><?=tgh_e(tg_when($o['created_at']))?></p>
<?php if($class==='pending'): ?><p class="tg-countdown-line">Held <span class="tg-countdown" data-expires="<?=tgh_e($o['reservation_expires_at'])?>"></span></p><?php endif; ?>
<?php if(!empty($o['payment_review'])): ?><p class="tg-flag">Payment review required</p><?php endif; ?>
<div class="tg-order-foot"><strong><?=isset($o['total_amount'])?tgh_e(tgh_format_price($o['total_amount'])):''?></strong><a class="btn btn-outline btn-sm" href="<?=tgh_e($link)?>"><?=$scope==='seller'?'Manage sale':($class==='pending'?'Finish or cancel':'View receipt')?></a></div>
</div>
</article>
<?php endforeach; ?>
</div>
<p class="tg-meta tg-filter-empty" id="filterEmpty" hidden>No orders in this view.</p>
<?php endif; ?>
<?php endif; ?>

<?php if($scope==='admin'): ?><div class="tg-card"><h2>Verified sales and commission</h2><div class="m4-table-wrap"><table><thead><tr><th>Seller</th><th>Gross</th><th>Commission (10%)</th><th>Seller earnings</th><th>Delivered sales (gross)</th></tr></thead><tbody><?php foreach($summary as $s): ?><tr><td><?=tgh_e($s['shop_name'])?></td><td><?=tgh_e(tgh_format_price($s['gross']))?></td><td><?=tgh_e(tgh_format_price($s['commission']))?></td><td><?=tgh_e(tgh_format_price($s['net']))?></td><td><?=tgh_e(tgh_format_price($s['completed_gross']))?></td></tr><?php endforeach; ?></tbody></table></div></div><?php endif; ?>
</div></section>
<script src="<?=tgh_e($basePath)?>js/commerce.js" defer></script>
<?php require __DIR__.'/../layout/footer.php'; ?>
