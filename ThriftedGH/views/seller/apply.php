<?php
$root = '../../';

require_once __DIR__ . '/../../controllers/Seller.php';

tgh_require_login($root . 'index.php?page=login');

$userId = tgh_current_user_id();
$application = application_status_ctr($userId);

if ($application && $application['status'] === 'approved') {
    tgh_redirect('../seller_dashboard.php');
}

$old = tgh_old_input_take();
$values = array_merge(
    ['seller_type' => '', 'shop_name' => '', 'location' => '', 'business_details' => '', 'phone' => ''],
    $old
);

tgh_layout_start('Become a seller', $root, 'sellers');
tgh_render_flash();
?>
<section class="m3-head">
    <h1>Become a seller</h1>
    <p class="m3-muted">Apply with your existing account. An administrator reviews every application before you can publish.</p>
</section>

<?php if ($application && $application['status'] === 'pending'): ?>
    <div class="m3-panel">
        <h2>Your application is being reviewed</h2>
        <p><strong><?= tgh_e($application['shop_name']) ?></strong> (<?= tgh_e(SellerClass::TYPES[$application['seller_type']] ?? '') ?>) was submitted on <?= tgh_e(date('j M Y', strtotime($application['created_at']))) ?>.</p>
        <p class="m3-muted">You will be able to publish products as soon as it is approved.</p>
        <p><a class="m3-btn m3-btn-ghost" href="../buyer/dashboard.php">Back to my account</a></p>
    </div>
<?php else: ?>
    <?php if ($application && $application['status'] === 'rejected'): ?>
        <div class="m3-alert m3-alert-error" role="alert">
            <ul><li>Your last application for <?= tgh_e($application['shop_name']) ?> was not approved. You can update your details and apply again.</li></ul>
        </div>
    <?php endif; ?>

    <form class="m3-form" method="post" action="<?= tgh_e($root) ?>actions/seller_application.php">
        <?= tgh_csrf_field() ?>

        <label for="seller_type">Seller type</label>
        <select id="seller_type" name="seller_type" required>
            <option value="">Choose a type</option>
            <?php foreach (SellerClass::TYPES as $key => $label): ?>
                <option value="<?= tgh_e($key) ?>" <?= $values['seller_type'] === $key ? 'selected' : '' ?>><?= tgh_e($label) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="shop_name">Shop name</label>
        <input type="text" id="shop_name" name="shop_name" maxlength="120" required value="<?= tgh_e($values['shop_name']) ?>">

        <div class="m3-two">
            <div>
                <label for="location">Location</label>
                <input type="text" id="location" name="location" maxlength="150" required value="<?= tgh_e($values['location']) ?>">
            </div>
            <div>
                <label for="phone">Contact phone (optional)</label>
                <input type="text" id="phone" name="phone" maxlength="20" inputmode="tel" value="<?= tgh_e($values['phone']) ?>">
            </div>
        </div>

        <label for="business_details">About your shop</label>
        <textarea id="business_details" name="business_details" rows="5" minlength="10" maxlength="1000" required><?= tgh_e($values['business_details']) ?></textarea>
        <p class="m3-hint">What do you sell, and where do your pieces come from?</p>

        <button class="m3-btn" type="submit">Submit application</button>
    </form>
<?php endif; ?>
<?php tgh_layout_end(); ?>
