<?php

$root = '../';

require_once __DIR__ . '/../core/guards.php';
require_once __DIR__ . '/../controllers/Storefront.php';
require_once __DIR__ . '/../classes/ProductClass.php';

tgh_load_card_component();

$storefront = storefront_page_ctr($_GET['seller_id'] ?? 0, $_GET['page'] ?? 1);

if (!$storefront) {
    http_response_code(404);
    tgh_layout_start('Storefront not found', $root);
    ?>
    <div class="m3-panel m3-empty">
        <h1>Storefront not found</h1>
        <p>This seller does not exist or is not approved.</p>
        <p><a class="m3-btn" href="sellers.php">Browse sellers</a></p>
    </div>
    <?php
    tgh_layout_end();
    exit;
}

$seller = $storefront['seller'];

tgh_layout_start($seller['shop_name'], $root, 'sellers');
?>
<section class="m3-head">
    <h1><?= tgh_e($seller['shop_name']) ?></h1>
    <p>
        <span class="m3-badge"><?= $seller['seller_type'] === 'business' ? 'Business' : 'Individual' ?></span>
        <?php if (!empty($seller['location'])): ?>
            <span class="m3-muted"><?= tgh_e($seller['location']) ?></span>
        <?php endif; ?>
    </p>
    <?php if (!empty($seller['description'])): ?>
        <p><?= nl2br(tgh_e($seller['description'])) ?></p>
    <?php endif; ?>
    <p class="m3-muted"><?= (int)$storefront['total'] ?> <?= (int)$storefront['total'] === 1 ? 'item' : 'items' ?> available</p>
</section>

<?php if (!$storefront['products']): ?>
    <div class="m3-panel m3-empty">
        <h2>Nothing for sale right now</h2>
        <p>This shop has no available items at the moment. Check back soon or browse other sellers.</p>
        <p><a class="m3-btn" href="sellers.php">Browse sellers</a></p>
    </div>
<?php else: ?>
    <div class="product-grid">
        <?php foreach ($storefront['products'] as $product): ?>
            <?php tgh_product_card($product, $root); ?>
        <?php endforeach; ?>
    </div>

    <?php if ($storefront['pages'] > 1): ?>
        <nav class="m3-pager" aria-label="Pages">
            <?php if ($storefront['page'] > 1): ?>
                <a class="m3-btn m3-btn-ghost" href="storefront.php?seller_id=<?= (int)$seller['seller_id'] ?>&page=<?= $storefront['page'] - 1 ?>">Previous</a>
            <?php endif; ?>
            <span class="m3-muted">Page <?= (int)$storefront['page'] ?> of <?= (int)$storefront['pages'] ?></span>
            <?php if ($storefront['page'] < $storefront['pages']): ?>
                <a class="m3-btn m3-btn-ghost" href="storefront.php?seller_id=<?= (int)$seller['seller_id'] ?>&page=<?= $storefront['page'] + 1 ?>">Next</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
<?php tgh_layout_end(); ?>
