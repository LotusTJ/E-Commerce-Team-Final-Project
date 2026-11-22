<?php
require_once __DIR__.'/../../controllers/Cart.php';
require_once __DIR__.'/../../classes/CatalogueRepository.php';
$pageTitle='Your cart | ThriftedGH'; $activeNav='cart';
$items=[];$total=0;$unavailable=false;$loadError=false;
try { if(authCurrentUser()) { $items=(new CartClass())->items((int)$_SESSION['user_id']); foreach($items as $item) { $total+=CommerceModel::cents($item['price']); $unavailable=$unavailable || !$item['purchasable']; } } }
catch(Throwable $e) { commerce_error($e); $loadError=true; }
require __DIR__.'/../layout/header.php';
$sellerCount=count(array_unique(array_column($items,'seller_id')));
?>
<section class="section tg-page"><div class="container tg-wrap">
<h1 class="tg-title">Your cart</h1>
<?php tgh_render_flash(); if(!$navLoggedIn): ?>
<div class="tg-card tg-empty"><div class="tg-empty-icon"><?=tgh_icon('lock')?></div><h2>Sign in to see your cart</h2><p>Your cart stays linked to your account, so you can pick up where you left off.</p><a class="btn btn-primary" href="<?=tgh_e($basePath)?>views/auth/login.php">Sign in</a></div>
<?php elseif($loadError): ?><div class="tg-note tg-note-warn" role="alert">Your cart is temporarily unavailable. Please reload this page.</div>
<?php elseif(!$items): ?>
<div class="tg-card tg-empty"><div class="tg-empty-icon"><?=tgh_icon('bag')?></div><h2>Your cart is empty</h2><p>Every piece here is one of one, so good finds go fast. Have a look around.</p><a class="btn btn-primary" href="<?=tgh_e($basePath)?>views/public/shop.php">Browse the shop</a></div>
<?php else: ?>
<p class="tg-sub"><?=count($items)?> <?=count($items)===1?'piece':'pieces'?> from <?=$sellerCount?> <?=$sellerCount===1?'seller':'sellers'?>. Each piece is one of one, so quantity is always 1.</p>
<div class="tg-grid">
<div class="tg-card tg-list">
<?php foreach($items as $item): ?>
<article class="tg-line">
<img class="tg-thumb" src="<?=tgh_e($basePath.tgh_catalogue_image($item['image_path']))?>" alt="<?=tgh_e($item['title'])?>">
<div class="tg-line-body"><h3><?=tgh_e($item['title'])?></h3><p class="tg-meta">Size <?=tgh_e($item['size']?:'not specified')?> · <?=tgh_e(str_replace('_',' ',$item['item_condition']))?></p><p class="tg-meta">Sold by <?=tgh_e($item['seller_name'])?></p>
<?php if(!$item['purchasable']): ?><p class="tg-flag" role="alert">No longer available. Remove it to continue.</p><?php endif; ?></div>
<div class="tg-price"><?=tgh_e(tgh_format_price($item['price']))?></div>
<form method="post" action="<?=tgh_e(tgh_url('actions/remove_from_cart_action.php'))?>"><?=tgh_csrf_field()?><input type="hidden" name="product_id" value="<?=(int)$item['product_id']?>"><button class="tg-icon-btn" type="submit" aria-label="Remove <?=tgh_e($item['title'])?> from cart" title="Remove"><?=tgh_icon('x')?></button></form>
</article>
<?php endforeach; ?>
</div>
<aside class="tg-card tg-summary"><h2>Order summary</h2>
<?php $fee=CommerceModel::buyerFee($total); $grand=$total+$fee; ?>
<dl class="tg-rows"><div><dt>Subtotal (<?=count($items)?> <?=count($items)===1?'piece':'pieces'?>)</dt><dd>GH₵ <?=CommerceModel::money($total)?></dd></div><div><dt>Service fee (3%)</dt><dd>GH₵ <?=CommerceModel::money($fee)?></dd></div><div><dt>Delivery fee</dt><dd>None added</dd></div></dl>
<div class="tg-total"><span>Total</span><strong>GH₵ <?=CommerceModel::money($grand)?></strong></div>
<?php if(!$unavailable): ?><a class="btn btn-primary tg-block" href="<?=tgh_e($basePath)?>views/buyer/checkout.php">Continue to checkout</a>
<?php else: ?><div class="tg-note tg-note-warn">Remove the unavailable pieces to continue.</div><?php endif; ?>
<a class="tg-link-center" href="<?=tgh_e($basePath)?>views/public/shop.php">Keep browsing</a>
</aside>
</div>
<?php endif; ?>
</div></section>
<?php require __DIR__.'/../layout/footer.php'; ?>
