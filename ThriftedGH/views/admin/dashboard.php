<?php
require_once __DIR__ . '/../../core/guard.php';
require_once __DIR__ . '/../../controllers/Product.php';
require_once __DIR__ . '/../../classes/AdminClass.php';

$user = guardRequireAdmin();
$stats = (new AdminClass())->overview();

tgh_layout_start('Admin dashboard', 'admin');
tgh_render_flash();
?>
<section class="m3-head">
    <h1>Admin dashboard</h1>
    <p class="m3-muted">Signed in as <?= tgh_e($user['full_name']) ?></p>
</section>

<div class="m3-stats">
    <div class="m3-stat"><strong><?= (int)$stats['pending_applications'] ?></strong><span>Applications waiting</span></div>
    <div class="m3-stat"><strong><?= (int)$stats['approved_sellers'] ?></strong><span>Approved sellers</span></div>
    <div class="m3-stat"><strong><?= (int)$stats['available_products'] ?></strong><span>Listings available</span></div>
    <div class="m3-stat"><strong><?= (int)$stats['paid_orders'] ?></strong><span>Paid orders</span></div>
</div>

<?php if ($stats['orders_in_review'] > 0): ?>
    <div class="m3-alert m3-alert-error" role="alert">
        <ul><li><?= (int)$stats['orders_in_review'] ?> payment(s) need a manual review.</li></ul>
    </div>
<?php endif; ?>

<div class="m3-panel">
    <h2>Manage</h2>
    <div class="m3-actions-row">
        <a class="m3-btn" href="<?= tgh_e(tgh_url('views/admin/applications.php')) ?>"><?= tgh_icon('file') ?> Seller applications</a>
        <a class="m3-btn m3-btn-ghost" href="<?= tgh_e(tgh_url('views/admin/orders.php')) ?>"><?= tgh_icon('receipt') ?> Orders and commission</a>
    </div>
</div>
<?php tgh_layout_end(); ?>
