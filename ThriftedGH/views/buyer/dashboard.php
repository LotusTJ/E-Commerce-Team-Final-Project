<?php
require_once __DIR__ . '/../../core/guard.php';
require_once __DIR__ . '/../../controllers/Seller.php';

$user = guardRequireLogin();
$application = application_status_ctr($user['user_id']);

tgh_layout_start('My account', 'dashboard');
tgh_render_flash();
?>
<section class="m3-head">
    <h1>My account</h1>
    <p class="m3-muted"><?= tgh_e($user['full_name']) ?> · <?= tgh_e($user['email']) ?><?= $user['phone'] ? ' · ' . tgh_e($user['phone']) : '' ?></p>
</section>

<div class="m3-panel">
    <h2>Shopping</h2>
    <div class="m3-actions-row">
        <a class="m3-btn" href="<?= tgh_e(tgh_url('views/buyer/orders.php')) ?>">My orders</a>
        <a class="m3-btn m3-btn-ghost" href="<?= tgh_e(tgh_url('views/public/shop.php')) ?>">Browse the shop</a>
        <a class="m3-btn m3-btn-ghost" href="<?= tgh_e(tgh_url('views/buyer/find_my_fit.php')) ?>">Find my fit</a>
    </div>
</div>

<div class="m3-panel">
    <h2>Selling</h2>
    <?php if ($user['role'] === 'admin'): ?>
        <p>You are an administrator.</p>
        <p><a class="m3-btn" href="<?= tgh_e(tgh_url('views/admin/dashboard.php')) ?>">Open the admin dashboard</a></p>
    <?php elseif ($application && $application['status'] === 'approved'): ?>
        <p>Your shop <strong><?= tgh_e($application['shop_name']) ?></strong> is approved.</p>
        <div class="m3-actions-row">
            <a class="m3-btn" href="<?= tgh_e(tgh_url('views/seller/dashboard.php')) ?>">Seller dashboard</a>
            <a class="m3-btn m3-btn-ghost" href="<?= tgh_e(tgh_url('views/seller/orders.php')) ?>">My sales</a>
        </div>
    <?php elseif ($application && $application['status'] === 'pending'): ?>
        <p>Your application for <strong><?= tgh_e($application['shop_name']) ?></strong> is waiting for review.</p>
        <p><a class="m3-btn" href="<?= tgh_e(tgh_url('views/seller/documents.php')) ?>">Verification documents</a></p>
    <?php elseif ($application && $application['status'] === 'rejected'): ?>
        <p>Your last application for <strong><?= tgh_e($application['shop_name']) ?></strong> was not approved.</p>
        <p><a class="m3-btn" href="<?= tgh_e(tgh_url('views/seller/apply.php')) ?>">Apply again</a></p>
    <?php else: ?>
        <p>Want to sell your own thrifted pieces? Apply with this account.</p>
        <p><a class="m3-btn" href="<?= tgh_e(tgh_url('views/seller/apply.php')) ?>">Become a seller</a></p>
    <?php endif; ?>
</div>

<p><a class="m3-btn m3-btn-ghost" href="<?= tgh_e(tgh_url('actions/auth/logout.php')) ?>">Sign out</a></p>
<?php tgh_layout_end(); ?>
