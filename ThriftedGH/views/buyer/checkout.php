<?php
require_once __DIR__.'/../../controllers/Order.php';
require_once __DIR__.'/../../classes/CatalogueRepository.php';
$user=commerce_user(); $items=[];$total=0;$ready=false;
try { $model=new OrderClass();$model->expire(); $items=(new CartClass())->items($user['user_id']); $ready=count($items)>0;
 foreach($items as $p) {$total+=CommerceModel::cents($p['price']);$ready=$ready && (bool)$p['purchasable'];}
} catch(Throwable $e) {commerce_error($e);}
$pageTitle='Checkout | ThriftedGH';$activeNav='cart';require __DIR__.'/../layout/header.php'; ?>
<section class="section tg-page"><div class="container tg-wrap">
<h1 class="tg-title">Checkout</h1><?php tgh_render_flash(); ?>
<?php if(!$ready): ?>
<div class="tg-card tg-empty"><div class="tg-empty-icon"><?=tgh_icon('basket')?></div><h2>Nothing to check out yet</h2><p>Your cart is empty or has a piece that is no longer available.</p><a class="btn btn-primary" href="<?=tgh_e($basePath)?>views/buyer/cart.php">Back to cart</a></div>
<?php else: ?>
<p class="tg-sub">One last step. Tell us where to deliver, then pay securely with Paystack.</p>
<form method="post" action="<?=tgh_e(tgh_url('actions/start_checkout_action.php'))?>" id="checkoutForm" class="tg-grid">
<?=tgh_csrf_field()?>
<div class="tg-stack">
<div class="tg-card tg-field"><h2><span class="tg-num">1</span> Delivery address</h2>
<label for="delivery_address">Where should the seller send your order?</label>
<textarea id="delivery_address" name="delivery_address" required minlength="10" maxlength="2000" rows="4" placeholder="House or hostel, street, area, city. Add a landmark and a phone number so the rider can reach you."></textarea>
<div class="tg-meter"><span id="addrBar"></span></div>
<div class="tg-field-foot"><span id="addrHint">At least 10 characters</span><button type="button" class="tg-chip" id="useLastAddress" hidden>Use my last address</button></div>
</div>
<div class="tg-card"><h2><span class="tg-num">2</span> Review your pieces</h2>
<?php foreach($items as $item): ?>
<article class="tg-line tg-line-compact"><img class="tg-thumb" src="<?=tgh_e($basePath.tgh_catalogue_image($item['image_path']))?>" alt="<?=tgh_e($item['title'])?>"><div class="tg-line-body"><h3><?=tgh_e($item['title'])?></h3><p class="tg-meta"><?=tgh_e($item['seller_name'])?> · Size <?=tgh_e($item['size']?:'not specified')?></p></div><div class="tg-price"><?=tgh_e(tgh_format_price($item['price']))?></div></article>
<?php endforeach; ?>
<a class="tg-link" href="<?=tgh_e($basePath)?>views/buyer/cart.php">Edit cart</a>
</div>
<div class="tg-card"><h2><span class="tg-num">3</span> What happens next</h2>
<ol class="tg-how"><li><b>Pay on Paystack.</b> You will be taken to Paystack's secure page.</li><li><b>Come straight back.</b> We confirm your payment and show your receipt.</li><li><b>The seller ships.</b> Follow every step from your orders page.</li></ol>
</div>
</div>
<aside class="tg-card tg-summary"><h2>Order summary</h2>
<?php $fee=CommerceModel::buyerFee($total); $grand=$total+$fee; ?>
<dl class="tg-rows"><div><dt>Subtotal (<?=count($items)?> <?=count($items)===1?'piece':'pieces'?>)</dt><dd>GH₵ <?=CommerceModel::money($total)?></dd></div><div><dt>Service fee (3%)</dt><dd>GH₵ <?=CommerceModel::money($fee)?></dd></div><div><dt>Delivery fee</dt><dd>None added</dd></div></dl>
<div class="tg-total"><span>Total</span><strong>GH₵ <?=CommerceModel::money($grand)?></strong></div>
<button class="btn btn-primary tg-block tg-pay" type="submit" id="payBtn" data-label="Pay GH₵ <?=CommerceModel::money($grand)?> with Paystack">Pay GH₵ <?=CommerceModel::money($grand)?> with Paystack</button>
<p class="tg-fine">Your pieces are held for 15 minutes once you start payment. Changed your mind? You can cancel from your orders page.</p>
<div class="tg-secure"><?=tgh_icon('lock')?> Payments secured by Paystack</div>
</aside>
</form>
<?php endif; ?>
</div></section>
<script src="<?=tgh_e($basePath)?>js/commerce.js" defer></script>
<?php require __DIR__.'/../layout/footer.php'; ?>
