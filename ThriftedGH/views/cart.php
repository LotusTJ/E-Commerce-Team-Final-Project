<?php
require_once __DIR__.'/../controllers/Cart.php';
require_once __DIR__.'/../classes/CatalogueRepository.php';
$pageTitle='Your cart | ThriftedGH'; $activeNav='cart';
$items=[];$total=0;$unavailable=false;$loadError=false;
try { if(authCurrentUser()) { $items=(new CartClass())->items((int)$_SESSION['user_id']); foreach($items as $item) { $total+=CommerceModel::cents($item['price']); $unavailable=$unavailable || !$item['purchasable']; } } }
catch(Throwable $e) { commerce_error($e); $loadError=true; }
require __DIR__.'/layout/header.php';
?>
<section class="section"><div class="container"><h1>Your shopping cart</h1>
<?php tgh_render_flash(); if(!$navLoggedIn): ?>
<p>Log in to keep your cart securely linked to your account.</p><a class="btn btn-primary" href="<?=tgh_e($basePath)?>index.php?page=login">Log in</a>
<?php elseif($loadError): ?><p role="alert">Your cart is temporarily unavailable. Please reload this page.</p>
<?php elseif(!$items): ?><p>Your cart is empty.</p><a class="btn btn-primary" href="<?=tgh_e($basePath)?>index.php?page=shop">Browse the shop</a>
<?php else: ?><p><?=count($items)?> unique <?=count($items)===1?'item':'items'?> from <?=count(array_unique(array_column($items,'seller_id')))?> <?=count(array_unique(array_column($items,'seller_id')))===1?'seller':'sellers'?>. Each item has quantity one.</p>
<div class="cart-layout-grid"><div class="cart-items-wrapper">
<?php foreach($items as $item): ?><article class="cart-item-row">
<div class="cart-item-info"><img class="cart-item-thumb" src="<?=tgh_e($basePath.tgh_catalogue_image($item['image_path']))?>" alt="<?=tgh_e($item['title'])?>"><div><h2 class="cart-item-name"><?=tgh_e($item['title'])?></h2><p>Size <?=tgh_e($item['size']?:'Not specified')?> · <?=tgh_e(str_replace('_',' ',$item['item_condition']))?></p><?php if(!$item['purchasable']): ?><p role="alert">Unavailable — remove to continue.</p><?php endif; ?></div></div>
<div class="cart-item-seller"><?=tgh_e($item['seller_name'])?></div><div class="cart-item-price"><?=tgh_e(tgh_format_price($item['price']))?></div>
<form method="post" action="<?=tgh_e($basePath)?>actions/cart.php"><?=tgh_csrf_field()?><input type="hidden" name="product_id" value="<?=(int)$item['product_id']?>"><button class="btn btn-outline" name="action" value="remove">Remove</button></form>
</article><?php endforeach; ?></div>
<aside class="order-summary-card"><h2>Order summary</h2><p>Total: <strong>GH₵ <?=CommerceModel::money($total)?></strong></p><p>No delivery fee is added by this checkout.</p><p>Items are only reserved when you proceed to payment.</p>
<?php if(!$unavailable): ?><a class="btn btn-primary" href="<?=tgh_e($basePath)?>index.php?page=checkout">Proceed to checkout</a><?php else: ?><p>Remove unavailable items before checkout.</p><?php endif; ?></aside></div>
<?php endif; ?></div></section>
<?php require __DIR__.'/layout/footer.php'; ?>
