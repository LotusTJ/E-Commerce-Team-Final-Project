<?php
require_once __DIR__.'/../controllers/Order.php';
$user=commerce_user(); $items=[];$total=0;$ready=false;
try { $model=new OrderClass();$model->expire(); $items=(new CartClass())->items($user['user_id']); $ready=count($items)>0;
 foreach($items as $p) {$total+=CommerceModel::cents($p['price']);$ready=$ready && (bool)$p['purchasable'];}
} catch(Throwable $e) {commerce_error($e);}
$pageTitle='Checkout | ThriftedGH';require __DIR__.'/layout/header.php'; ?>
<section class="section"><div class="container"><h1>Checkout</h1><?php tgh_render_flash(); ?>
<?php if(!$ready): ?><p>Your cart is empty or contains unavailable items.</p><a href="<?=tgh_e($basePath)?>index.php?page=cart">Return to cart</a>
<?php else: ?><p><?=count($items)?> items · Total GH₵ <?=CommerceModel::money($total)?></p><p>Paystack test checkout. No real payment is required. Your items will be held for 15 minutes.</p>
<form method="post" action="<?=tgh_e($basePath)?>actions/checkout.php"><?=tgh_csrf_field()?><label for="delivery_address">Delivery address</label><textarea class="form-control" id="delivery_address" name="delivery_address" required minlength="10" maxlength="2000" rows="4"></textarea><button class="btn btn-primary" type="submit">Continue to Paystack test payment</button></form>
<?php endif; ?></div></section><?php require __DIR__.'/layout/footer.php'; ?>
