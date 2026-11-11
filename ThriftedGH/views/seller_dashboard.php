<?php

$root = '../';

require_once __DIR__ . '/../controllers/Product.php';

tgh_require_login(tgh_login_url(''));

$seller = seller_find_approved_ctr(tgh_current_user_id());
$products = $seller ? seller_dashboard_products_ctr($seller['seller_id']) : [];

$counts = ['available' => 0, 'reserved' => 0, 'sold' => 0];
foreach ($products as $product) {
    if (isset($counts[$product['status']])) {
        $counts[$product['status']]++;
    }
}

tgh_layout_start('Seller dashboard', $root);
tgh_render_flash();
?>
<section class="m3-head">
    <h1>Seller dashboard</h1>
    <?php if ($seller): ?>
        <p class="m3-muted"><?= tgh_e($seller['shop_name']) ?></p>
    <?php endif; ?>
</section>

<?php if (!$seller): ?>
    <div class="m3-panel">
        <h2>Your seller account is not approved yet</h2>
        <p>You can publish products once an administrator approves your seller application. Pending and rejected applications cannot publish.</p>
        <p><a class="m3-btn" href="seller/apply.php">Seller application</a></p>
    </div>
<?php else: ?>
    <div class="m3-stats">
        <div class="m3-stat"><strong><?= (int)$counts['available'] ?></strong><span>Available</span></div>
        <div class="m3-stat"><strong><?= (int)$counts['reserved'] ?></strong><span>In checkout</span></div>
        <div class="m3-stat"><strong><?= (int)$counts['sold'] ?></strong><span>Sold</span></div>
    </div>

    <div class="m3-actions-row">
        <a class="m3-btn" href="seller_product_form.php">Add a product</a>
        <a class="m3-btn m3-btn-ghost" href="storefront.php?seller_id=<?= (int)$seller['seller_id'] ?>">View my storefront</a>
    </div>

    <?php if (!$products): ?>
        <div class="m3-panel m3-empty">
            <h2>No listings yet</h2>
            <p>Add your first one-of-one item. Every listing is a single piece, so it comes off the shop as soon as it sells.</p>
        </div>
    <?php else: ?>
        <ul class="m3-list">
            <?php foreach ($products as $product): ?>
                <li class="m3-row">
                    <img class="m3-thumb" src="<?= tgh_e($root . $product['image_path']) ?>" alt="<?= tgh_e($product['title']) ?>" loading="lazy">
                    <div class="m3-row-body">
                        <h3><?= tgh_e($product['title']) ?></h3>
                        <p class="m3-price"><?= tgh_e(tgh_format_price($product['price'])) ?></p>
                        <p class="m3-muted">
                            <?= tgh_e($product['category_name']) ?> · Size <?= tgh_e($product['size']) ?> · <?= tgh_e(ProductClass::CONDITIONS[$product['item_condition']] ?? '') ?>
                        </p>
                        <p><span class="m3-badge m3-badge-<?= tgh_e($product['status']) ?>"><?= tgh_e(ProductClass::STATUS_LABELS[$product['status']] ?? $product['status']) ?></span></p>
                    </div>
                    <?php if ($product['status'] === 'available'): ?>
                        <div class="m3-row-actions">
                            <a class="m3-btn m3-btn-small" href="seller_product_form.php?id=<?= (int)$product['product_id'] ?>">Edit</a>
                            <form method="post" action="<?= tgh_e($root) ?>actions/products.php" class="js-confirm" data-message="Remove this listing?">
                                <?= tgh_csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                                <button type="submit" class="m3-btn m3-btn-small m3-btn-danger">Remove</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>
<?php tgh_layout_end(); ?>
