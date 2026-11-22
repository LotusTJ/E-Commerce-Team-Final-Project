<?php
require_once __DIR__.'/../../classes/CatalogueRepository.php';
require_once __DIR__.'/../components/product_card.php';
$catalogueError=false; $products=[]; $categories=[]; $sizes=[];
$filters=[];
foreach (['q','category','size','condition','min_price','max_price','sort'] as $key) { $filters[$key]=substr(trim((string)($_GET[$key]??'')),0,120); }
$priceRangeError = $filters['min_price'] !== ''
    && $filters['max_price'] !== ''
    && is_numeric($filters['min_price'])
    && is_numeric($filters['max_price'])
    && (float)$filters['min_price'] > (float)$filters['max_price'];
try { $repo=new CatalogueRepository(); $categories=$repo->categories(); $sizes=$repo->sizes(); if (!$priceRangeError) { $products=$repo->browse($filters); } }
catch (Throwable $e) { error_log('Shop catalogue: '.$e->getMessage()); $catalogueError=true; }
$pageTitle='Shop Second-Hand Clothing | ThriftedGH'; $activeNav='shop';
require __DIR__.'/../layout/header.php';
function t6h($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}
?>
<section class="page-banner shop-banner"><div class="container"><div class="page-banner-content"><h1 class="page-title">Shop</h1><p class="page-subtitle">One-of-one pieces from approved sellers.</p></div></div></section>
<section class="section shop-section"><div class="container">
<form method="get" action="<?=t6h($basePath)?>views/public/shop.php" class="catalog-controls" aria-label="Search and filter clothing">
<div class="catalog-search-wrap"><label for="shopSearchInput">Search by product name</label><input class="form-control search-input" id="shopSearchInput" type="search" name="q" value="<?=t6h($filters['q'])?>" placeholder="Search clothing..."></div>
<div class="filter-toolbar">
<div class="filter-group"><label class="filter-label" for="categoryFilter">Category</label><select class="form-select filter-select" id="categoryFilter" name="category"><option value="">All categories</option><?php foreach($categories as $c):?><option value="<?= (int)$c['category_id']?>" <?= $filters['category']===(string)$c['category_id']?'selected':''?>><?=t6h($c['category_name'])?></option><?php endforeach;?></select></div>
<div class="filter-group"><label class="filter-label" for="sizeFilter">Size</label><select class="form-select filter-select" id="sizeFilter" name="size"><option value="">All sizes</option><?php foreach($sizes as $s):?><option value="<?=t6h($s['value'])?>" <?=$filters['size']===$s['value']?'selected':''?>><?=t6h($s['value'])?></option><?php endforeach;?></select></div>
<div class="filter-group"><label class="filter-label" for="conditionFilter">Condition</label><select class="form-select filter-select" id="conditionFilter" name="condition"><option value="">All conditions</option><?php foreach(['like_new'=>'Like new','good'=>'Good','fair'=>'Fair'] as $k=>$v):?><option value="<?=$k?>" <?=$filters['condition']===$k?'selected':''?>><?=$v?></option><?php endforeach;?></select></div>
<div class="filter-group"><label class="filter-label" for="minPrice">Min price (GH₵)</label><input class="form-control" style="max-width:125px" id="minPrice" type="number" min="0" step="0.01" name="min_price" value="<?=t6h($filters['min_price'])?>"></div>
<div class="filter-group"><label class="filter-label" for="maxPrice">Max price (GH₵)</label><input class="form-control" style="max-width:125px" id="maxPrice" type="number" min="0" step="0.01" name="max_price" value="<?=t6h($filters['max_price'])?>"></div>
<div class="filter-group"><label class="filter-label" for="sortOrder">Sort</label><select class="form-select filter-select" name="sort" id="sortOrder"><option value="">Latest</option><option value="price-asc" <?=$filters['sort']==='price-asc'?'selected':''?>>Price: low to high</option><option value="price-desc" <?=$filters['sort']==='price-desc'?'selected':''?>>Price: high to low</option></select></div>
<button class="btn btn-sm btn-primary" type="submit">Apply filters</button><a class="btn btn-sm btn-ghost" href="<?=t6h($basePath)?>views/public/shop.php">Reset</a>
</div></form>
<?php if($priceRangeError):?><div class="empty-state-card" role="alert"><h3>Invalid price range</h3><p>Minimum price cannot be greater than maximum price. Please adjust your prices and apply filters again.</p></div><?php endif;?>
<?php if(!$priceRangeError):?>
<div class="catalog-status-bar"><span class="results-count">Showing <strong><?=count($products)?></strong> available items<?=count($products)===100?' (first 100)':''?></span><span class="catalog-badge">One of one, no restocks</span></div>
<?php if($catalogueError):?><div class="empty-state-card" role="alert"><h3>Shop temporarily unavailable</h3><p>We could not load the catalogue. Please try again in a moment.</p></div>
<?php elseif(!$products):?><div class="empty-state-card"><h3>No matching items found</h3><p>Try another search or clear your filters. Sold and unavailable items are not shown.</p><a class="btn btn-primary" href="<?=t6h($basePath)?>views/public/shop.php">View all items</a></div>
<?php else:?><div class="product-grid" id="catalogProductGrid"><?php foreach($products as $item) renderProductCard($item,$basePath);?></div><?php endif;?>
<?php endif;?>
</div></section>
<?php require __DIR__.'/../layout/footer.php'; ?>
