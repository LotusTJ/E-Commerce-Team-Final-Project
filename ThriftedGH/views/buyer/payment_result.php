<?php
require_once __DIR__.'/../../controllers/Order.php';
require_once __DIR__.'/../../classes/CatalogueRepository.php';
require_once __DIR__.'/../components/order_ui.php';
$user=commerce_user();$ref=$_GET['reference']??null;
if(!is_string($ref) || !preg_match('/^TGH-[a-f0-9]{32}$/D',$ref)) {http_response_code(404);exit('Order unavailable');}
$o=(new OrderClass())->byReference($ref);
if(!$o || (int)$o['buyer_id']!==(int)$user['user_id']) {http_response_code(404);exit('Order unavailable');}
$detail=null; try { $detail=(new OrderClass())->detail($user,(int)$o['order_id'],'buyer'); } catch(Throwable $e) { commerce_error($e); }
$items=$detail['items']??[];
$state=!empty($o['payment_review'])?'review':($o['payment_status']==='paid'?'paid':($o['payment_status']==='failed'?'failed':'pending'));
$map=[
 'paid'=>['check','Payment successful','Thank you. Your payment is confirmed and the seller has been notified. They will start preparing your order now.'],
 'review'=>['alert','Payment needs a quick review','Your payment arrived after the reservation was released, so a person needs to check it. Contact the marketplace administrator with your reference and we will sort out a refund check.'],
 'failed'=>['x','Payment not completed','This order was not marked as paid and the pieces are no longer reserved. If you were charged, check the status below before paying again.'],
 'pending'=>['clock','Waiting for confirmation','We have not received a confirmed result from Paystack yet. This can take a moment, so check again in a few seconds.'],
];
[$icon,$heading,$text]=$map[$state];
$pageTitle='Payment result | ThriftedGH';$activeNav='orders';require __DIR__.'/../layout/header.php';
?>
<section class="section tg-page"><div class="container tg-wrap">
<?php tgh_render_flash(); ?>
<div class="tg-hero"><div class="tg-hero-icon is-<?=$state?>" aria-hidden="true"><?=tgh_icon($icon)?></div><h1><?=tgh_e($heading)?></h1><p><?=tgh_e($text)?></p></div>
<div class="tg-card tg-result-card">
<div class="tg-result-top"><div><span class="tg-label">Reference</span><div class="tg-ref-row"><span class="tg-ref" id="refText"><?=tgh_e($o['order_reference'])?></span><button type="button" class="tg-chip" data-copy="#refText">Copy</button></div></div>
<div class="tg-result-amount"><span class="tg-label"><?=$state==='paid'?'Amount paid':'Order total'?></span><strong>GH₵ <?=tgh_e(CommerceModel::money(CommerceModel::cents($o['total_amount'])))?></strong></div></div>
<?php if($items): ?><div class="tg-result-items"><?php foreach($items as $item): ?><div class="tg-mini"><img class="tg-thumb" src="<?=tg_img($basePath,$item['image_path']??'')?>" alt=""><div><b><?=tgh_e($item['title_at_purchase']??'Item')?></b><span><?=tgh_e($item['shop_name'])?></span></div><em><?=tgh_e(tgh_format_price($item['price_at_purchase']))?></em></div><?php endforeach; ?></div><?php endif; ?>
<?php if($state==='paid'): ?><ul class="tg-ticks tg-ticks-inline"><li>Paid <?=tgh_e(tg_when($o['paid_at']??$o['created_at']))?></li><li>Seller notified</li><li>Track delivery in your receipt</li></ul><?php endif; ?>
</div>
<div class="tg-actions tg-actions-center">
<?php if($state==='pending'): ?><form method="post" action="<?=tgh_e(tgh_url('actions/verify_order_action.php'))?>"><?=tgh_csrf_field()?><input type="hidden" name="reference" value="<?=tgh_e($o['order_reference'])?>"><button class="btn btn-primary" data-busy="Checking…">Check payment status</button></form><?php endif; ?>
<a class="btn <?=$state==='pending'?'btn-outline':'btn-primary'?>" href="<?=tgh_e($basePath)?>views/buyer/orders.php?id=<?=(int)$o['order_id']?>"><?=$state==='paid'?'View full receipt':'View order'?></a>
<?php if($state==='failed'): ?><a class="btn btn-primary" href="<?=tgh_e($basePath)?>views/buyer/cart.php">Back to cart</a><?php endif; ?>
<a class="btn btn-outline" href="<?=tgh_e($basePath)?>views/public/shop.php">Keep shopping</a>
</div>
</div></section>
<script src="<?=tgh_e($basePath)?>js/commerce.js" defer></script>
<?php require __DIR__.'/../layout/footer.php'; ?>
