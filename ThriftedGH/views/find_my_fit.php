<?php

$root = '../';

require_once __DIR__ . '/../core/guards.php';
require_once __DIR__ . '/../controllers/Fit.php';
require_once __DIR__ . '/../classes/ProductClass.php';

tgh_require_login(tgh_login_url(''));

tgh_load_card_component();

$userId = tgh_current_user_id();
$saved = fit_get_measurements_ctr($userId);
$old = tgh_old_input_take();

$typeFilter = isset($_GET['type']) && is_string($_GET['type']) ? $_GET['type'] : '';
$result = fit_get_matches_ctr($userId, $typeFilter);

function tgh_measurement_value($name, $column, $old, $saved)
{
    if (isset($old[$name])) {
        return $old[$name];
    }
    if ($saved && $saved[$column] !== null) {
        return rtrim(rtrim((string)$saved[$column], '0'), '.');
    }
    return '';
}

$fields = [
    'chest' => ['Chest (cm)', 'chest_cm'],
    'waist' => ['Waist (cm)', 'waist_cm'],
    'hips' => ['Hips (cm)', 'hips_cm'],
    'inseam' => ['Inseam (cm)', 'inseam_cm']
];

tgh_layout_start('Find My Fit', $root);
tgh_render_flash();
?>
<section class="m3-head">
    <h1>Find My Fit</h1>
    <p class="m3-muted">Save your measurements and see available pieces that could suit you. These are suggestions based on garment measurements, not a guarantee of fit. Your measurements are private and only used to compare against listings.</p>
</section>

<form class="m3-form" method="post" action="<?= tgh_e($root) ?>actions/fit.php" novalidate id="fit-form">
    <?= tgh_csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <div class="m3-two">
        <?php foreach ($fields as $name => $meta): ?>
            <div>
                <label for="<?= tgh_e($name) ?>"><?= tgh_e($meta[0]) ?></label>
                <input type="text" inputmode="decimal" id="<?= tgh_e($name) ?>" name="<?= tgh_e($name) ?>" value="<?= tgh_e(tgh_measurement_value($name, $meta[1], $old, $saved)) ?>">
            </div>
        <?php endforeach; ?>
    </div>
    <p class="m3-hint">Measure your body, not clothes. Chest around the fullest part, waist at the narrowest, hips at the widest, inseam from crotch to ankle. Inseam is optional.</p>
    <div class="m3-actions-row">
        <button type="submit" class="m3-btn"><?= $saved ? 'Update measurements' : 'Save measurements' ?></button>
    </div>
</form>

<?php if ($saved): ?>
    <form method="post" action="<?= tgh_e($root) ?>actions/fit.php" class="js-confirm m3-inline-form" data-message="Delete your saved measurements?">
        <?= tgh_csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="m3-btn m3-btn-danger m3-btn-small">Delete my measurements</button>
    </form>
<?php endif; ?>

<section class="m3-section">
    <h2>Suggested for you</h2>

    <?php if (!$result['has_measurements']): ?>
        <div class="m3-panel m3-empty">
            <p>Save your measurements above to see suggestions.</p>
        </div>
    <?php else: ?>
        <form method="get" class="m3-filter">
            <label for="type">Show</label>
            <select id="type" name="type" onchange="this.form.submit()">
                <option value="">All garment types</option>
                <?php foreach (FitClass::TYPE_LABELS as $key => $label): ?>
                    <option value="<?= tgh_e($key) ?>" <?= $typeFilter === $key ? 'selected' : '' ?>><?= tgh_e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="m3-btn m3-btn-small">Apply</button></noscript>
        </form>

        <?php foreach ($result['unavailable_types'] as $key => $info): ?>
            <p class="m3-hint">Add your <?= tgh_e(implode(', ', $info['missing'])) ?> to see <?= tgh_e(strtolower($info['label'])) ?>.</p>
        <?php endforeach; ?>

        <?php if (!$result['matches']): ?>
            <div class="m3-panel m3-empty">
                <h3>No matches right now</h3>
                <p>Nothing available fits your measurements at the moment. New one-of-one pieces are added all the time, so check again soon.</p>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($result['matches'] as $match): ?>
                    <div class="m3-fit-item">
                        <?php tgh_product_card($match, $root); ?>
                        <div class="m3-fit-info">
                            <span class="m3-badge m3-badge-fit"><?= tgh_e($match['fit']['label']) ?></span>
                            <ul class="m3-fit-notes">
                                <?php foreach ($match['fit']['details'] as $detail): ?>
                                    <li><?= tgh_e(ucfirst($detail['dimension'])) ?>: <?= tgh_e($detail['feel']) ?> (garment <?= tgh_e(rtrim(rtrim(number_format($detail['garment'], 1), '0'), '.')) ?> cm)</li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="m3-hint">Possible matches only. Check the seller's description and photos before buying.</p>
        <?php endif; ?>
    <?php endif; ?>
</section>
<script src="<?= tgh_e($root) ?>js/product_form.js"></script>
<?php tgh_layout_end(); ?>
