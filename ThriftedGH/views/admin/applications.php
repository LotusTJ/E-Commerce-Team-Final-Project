<?php
$root = '../../';

require_once __DIR__ . '/../../controllers/Seller.php';

authRequireAdmin();

$pending = application_pending_ctr();
$recent = application_recent_ctr(10);

tgh_layout_start('Seller applications', $root, 'account');
tgh_render_flash();
?>
<section class="m3-head">
    <h1>Seller applications</h1>
    <p class="m3-muted"><?= count($pending) ?> waiting for review</p>
</section>

<?php if (!$pending): ?>
    <div class="m3-panel m3-empty">
        <h2>No applications waiting</h2>
        <p>New seller applications will appear here.</p>
    </div>
<?php else: ?>
    <div class="m3-list">
        <?php foreach ($pending as $application): ?>
            <div class="m3-row m3-row-plain">
                <div class="m3-row-body">
                    <h3><?= tgh_e($application['shop_name']) ?> <span class="m3-badge"><?= tgh_e(SellerClass::TYPES[$application['seller_type']] ?? '') ?></span></h3>
                    <p><?= tgh_e($application['full_name']) ?> · <?= tgh_e($application['email']) ?><?= $application['phone'] ? ' · ' . tgh_e($application['phone']) : '' ?></p>
                    <p class="m3-muted"><?= tgh_e($application['location']) ?> · applied <?= tgh_e(date('j M Y', strtotime($application['created_at']))) ?></p>
                    <p><?= nl2br(tgh_e($application['business_details'])) ?></p>
                </div>
                <div class="m3-row-actions">
                    <form method="post" action="<?= tgh_e($root) ?>actions/approval.php">
                        <?= tgh_csrf_field() ?>
                        <input type="hidden" name="application_id" value="<?= (int)$application['application_id'] ?>">
                        <input type="hidden" name="decision" value="approve">
                        <button class="m3-btn m3-btn-small" type="submit">Approve</button>
                    </form>
                    <form method="post" action="<?= tgh_e($root) ?>actions/approval.php" class="js-confirm" data-message="Reject this application?">
                        <?= tgh_csrf_field() ?>
                        <input type="hidden" name="application_id" value="<?= (int)$application['application_id'] ?>">
                        <input type="hidden" name="decision" value="reject">
                        <button class="m3-btn m3-btn-small m3-btn-danger" type="submit">Reject</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($recent): ?>
    <section class="m3-section">
        <h2>Recent decisions</h2>
        <div class="m3-list">
            <?php foreach ($recent as $decision): ?>
                <div class="m3-row m3-row-split">
                    <div class="m3-row-body">
                        <h3><?= tgh_e($decision['shop_name']) ?></h3>
                        <p class="m3-muted"><?= tgh_e($decision['full_name']) ?> · <?= $decision['reviewed_at'] ? tgh_e(date('j M Y', strtotime($decision['reviewed_at']))) : '' ?></p>
                    </div>
                    <span class="m3-badge <?= $decision['status'] === 'approved' ? 'm3-badge-available' : 'm3-badge-sold' ?>"><?= $decision['status'] === 'approved' ? 'Approved' : 'Rejected' ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
<?php tgh_layout_end(); ?>
