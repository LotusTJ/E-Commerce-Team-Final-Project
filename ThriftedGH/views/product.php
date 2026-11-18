<?php
require_once __DIR__.'/../classes/CatalogueRepository.php';
require_once __DIR__.'/components/product_card.php';
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
$product=null; $related=[]; $error=false;
try { $repo=new CatalogueRepository(); if($id && $id>0){$product=$repo->detail($id); if($product) $related=array_filter($repo->browse([],8),fn($p)=>(int)$p['product_id']!==$id); } }
catch(Throwable $e){error_log('Task 6 product: '.$e->getMessage());$error=true;}
if(!$product){http_response_code($error?503:404);}
$pageTitle=$product?htmlspecialchars($product['title'],ENT_QUOTES,'UTF-8').' | ThriftedGH':'Product unavailable | ThriftedGH';$activeNav='shop';
require __DIR__.'/layout/header.php';
function t6detailh($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<section class="section product-detail-section"><div class="container">
<?php if(!$product):?><div class="empty-state-card"><h1><?=$error?'Unable to load product':'This item is no longer available'?></h1><p><?=$error?'Please try again later.':'It may have been sold, reserved, hidden or removed.'?></p><a class="btn btn-primary" href="<?=t6detailh($basePath)?>index.php?page=shop">Browse available clothing</a></div>
<?php else:?>
<nav aria-label="Breadcrumb"><a href="<?=t6detailh($basePath)?>index.php?page=shop">Shop</a> / <?=t6detailh($product['title'])?></nav>
<div class="product-detail-grid"><div class="product-gallery"><div class="product-main-image-wrap"><img class="product-detail-image" src="<?=t6detailh($basePath.tgh_catalogue_image($product['image_path']))?>" alt="<?=t6detailh($product['title'])?>"><span class="stock-pill">Only 1 available</span></div></div>
<div class="product-summary"><span class="product-category-tag"><?=t6detailh($product['category_name'])?></span><h1 class="product-detail-title"><?=t6detailh($product['title'])?></h1>
<div class="product-price-row"><div class="price-display"><span class="currency-symbol">GH₵</span><span class="price-value"><?=number_format((float)$product['price'],2)?></span></div></div>
<div class="product-attributes-panel"><div class="attribute-pill"><span class="attr-label">Size</span><span class="attr-value"><?=t6detailh($product['size']?:'Not specified')?></span></div><div class="attribute-pill"><span class="attr-label">Condition</span><span class="attr-value"><?=t6detailh(ucwords(str_replace('_',' ',$product['item_condition'])))?></span></div><div class="attribute-pill"><span class="attr-label">Availability</span><span class="attr-value">In stock (1)</span></div></div>
<div class="product-desc-box"><h3 class="desc-heading">Item description</h3><p class="desc-text"><?=nl2br(t6detailh($product['description']?:'No description provided.'))?></p></div>
<div class="measurements-box"><h3 class="desc-heading">Garment measurements</h3><div class="measurements-grid"><?php $hasMeasurements=false;foreach(['chest_cm'=>'Chest','waist_cm'=>'Waist','hips_cm'=>'Hips','inseam_cm'=>'Inseam'] as $key=>$label):if($product[$key]!==null){$hasMeasurements=true;?><div class="meas-item"><span class="meas-name"><?=$label?></span><span class="meas-value"><?=t6detailh($product[$key])?> cm</span></div><?php }endforeach;if(!$hasMeasurements):?><p>Measurements have not been provided.</p><?php endif;?></div></div>
<div class="seller-profile-card"><div class="seller-card-avatar">🏷️</div><div class="seller-card-info"><div class="seller-type-tag"><?=t6detailh(ucfirst($product['seller_type']))?> seller</div><h4 class="seller-card-name"><?=t6detailh($product['seller_name'])?></h4><p class="seller-card-location"><?=t6detailh($product['location']?:'Location not provided')?></p><p><?=t6detailh($product['seller_description']?:'')?></p><a class="seller-store-link" href="<?=t6detailh($basePath)?>views/storefront.php?seller_id=<?=(int)$product['seller_id']?>">View seller storefront →</a></div></div>
<form method="post" action="<?=tgh_e($basePath)?>actions/cart.php"><?=tgh_csrf_field()?><input type="hidden" name="product_id" value="<?=(int)$product['product_id']?>"><?php if($navLoggedIn): ?><button class="btn btn-primary" name="action" value="add">Add to cart</button><?php else: ?><a class="btn btn-primary" href="<?=tgh_e($basePath)?>index.php?page=login">Log in to buy</a><?php endif; ?></form>
<a class="btn btn-outline" href="<?=t6detailh($basePath)?>index.php?page=shop">Continue shopping</a>
</div></div>
<?php if($related):?><div class="related-products-section"><h2 class="section-title">You might also like</h2><div class="product-grid"><?php foreach(array_slice($related,0,3) as $item)renderProductCard($item,$basePath);?></div></div><?php endif;?>
<?php endif;?></div></section>
<?php require __DIR__.'/layout/footer.php';?>
