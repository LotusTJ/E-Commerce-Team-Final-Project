<?php

$root = '../';

require_once __DIR__ . '/../controllers/Product.php';

tgh_require_login(tgh_login_url(''));

$seller = seller_find_approved_ctr(tgh_current_user_id());
if (!$seller) {
    tgh_flash_set('error', 'Your seller account is not approved, so you cannot publish products.');
    tgh_redirect('seller_dashboard.php');
}

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $productId > 0;
$product = null;

if ($editing) {
    $product = seller_owned_product_ctr($productId, $seller['seller_id']);
    if (!$product || $product['status'] !== 'available') {
        tgh_flash_set('error', 'That listing cannot be edited.');
        tgh_redirect('seller_dashboard.php');
    }
}

$old = tgh_old_input_take();
$categories = seller_form_categories_ctr();

function tgh_field_value($name, $old, $product, $column = null)
{
    if (isset($old[$name])) {
        return $old[$name];
    }
    if ($product) {
        $value = $product[$column ?? $name] ?? '';
        if ($value === null) {
            return '';
        }
        if ($column !== null && $value !== '') {
            return rtrim(rtrim(number_format((float)$value, 2, '.', ''), '0'), '.');
        }
        return $value;
    }
    return '';
}

$values = [
    'title' => tgh_field_value('title', $old, $product),
    'description' => tgh_field_value('description', $old, $product),
    'price' => tgh_field_value('price', $old, $product),
    'category_id' => tgh_field_value('category_id', $old, $product),
    'size' => tgh_field_value('size', $old, $product),
    'item_condition' => tgh_field_value('item_condition', $old, $product),
    'garment_type' => tgh_field_value('garment_type', $old, $product),
    'chest' => tgh_field_value('chest', $old, $product, 'chest_cm'),
    'waist' => tgh_field_value('waist', $old, $product, 'waist_cm'),
    'hips' => tgh_field_value('hips', $old, $product, 'hips_cm'),
    'inseam' => tgh_field_value('inseam', $old, $product, 'inseam_cm')
];

tgh_layout_start($editing ? 'Edit product' : 'Add a product', $root);
tgh_render_flash();
?>
<section class="m3-head">
    <h1><?= $editing ? 'Edit product' : 'Add a product' ?></h1>
    <p class="m3-muted">Every listing is a one-of-one piece. Once it sells it leaves the shop automatically.</p>
</section>

<div id="form-errors" class="m3-alert m3-alert-error" role="alert" hidden></div>

<form id="product-form" class="m3-form" method="post" enctype="multipart/form-data" novalidate
      action="<?= tgh_e($root) ?>actions/products.php">
    <?= tgh_csrf_field() ?>
    <input type="hidden" name="action" value="<?= $editing ? 'edit' : 'add' ?>">
    <?php if ($editing): ?>
        <input type="hidden" name="product_id" value="<?= (int)$productId ?>">
    <?php endif; ?>

    <label for="title">Title</label>
    <input type="text" id="title" name="title" maxlength="150" required value="<?= tgh_e($values['title']) ?>">

    <label for="category_id">Category</label>
    <select id="category_id" name="category_id" required>
        <option value="">Choose a category</option>
        <?php foreach ($categories as $category): ?>
            <option value="<?= (int)$category['category_id'] ?>" <?= (string)$values['category_id'] === (string)$category['category_id'] ? 'selected' : '' ?>>
                <?= tgh_e($category['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div class="m3-two">
        <div>
            <label for="price">Price (GH₵)</label>
            <input type="text" inputmode="decimal" id="price" name="price" required value="<?= tgh_e($values['price']) ?>">
        </div>
        <div>
            <label for="size">Size</label>
            <select id="size" name="size" required>
                <option value="">Choose a size</option>
                <?php foreach (ProductClass::SIZES as $size): ?>
                    <option value="<?= tgh_e($size) ?>" <?= $values['size'] === $size ? 'selected' : '' ?>><?= tgh_e($size) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <label for="item_condition">Condition</label>
    <select id="item_condition" name="item_condition" required>
        <option value="">Choose a condition</option>
        <?php foreach (ProductClass::CONDITIONS as $key => $label): ?>
            <option value="<?= tgh_e($key) ?>" <?= $values['item_condition'] === $key ? 'selected' : '' ?>><?= tgh_e($label) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="5" maxlength="2000" required><?= tgh_e($values['description']) ?></textarea>

    <label for="image"><?= $editing ? 'Replace photo (optional)' : 'Photo' ?></label>
    <?php if ($editing && !empty($product['image_path'])): ?>
        <img class="m3-preview" id="current-image" src="<?= tgh_e($root . $product['image_path']) ?>" alt="Current photo">
    <?php endif; ?>
    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" <?= $editing ? '' : 'required' ?>>
    <p class="m3-hint">JPG, PNG or WEBP, up to 2 MB.</p>
    <img class="m3-preview" id="image-preview" alt="" hidden>

    <fieldset class="m3-fieldset">
        <legend>Garment measurements</legend>
        <p class="m3-hint">These power Find My Fit. Lay the garment flat, measure across, then double it to get the full circumference. Skip this for shoes, bags and accessories.</p>

        <label for="garment_type">Garment type</label>
        <select id="garment_type" name="garment_type">
            <option value="">No measurements</option>
            <?php foreach (ProductClass::GARMENT_TYPES as $key => $label): ?>
                <option value="<?= tgh_e($key) ?>" <?= $values['garment_type'] === $key ? 'selected' : '' ?>><?= tgh_e($label) ?></option>
            <?php endforeach; ?>
        </select>

        <div id="measure-fields" class="m3-two">
            <div data-dim="chest">
                <label for="chest">Chest (cm)</label>
                <input type="text" inputmode="decimal" id="chest" name="chest" value="<?= tgh_e($values['chest']) ?>">
            </div>
            <div data-dim="waist">
                <label for="waist">Waist (cm)</label>
                <input type="text" inputmode="decimal" id="waist" name="waist" value="<?= tgh_e($values['waist']) ?>">
            </div>
            <div data-dim="hips">
                <label for="hips">Hips (cm)</label>
                <input type="text" inputmode="decimal" id="hips" name="hips" value="<?= tgh_e($values['hips']) ?>">
            </div>
            <div data-dim="inseam">
                <label for="inseam">Inseam (cm, optional)</label>
                <input type="text" inputmode="decimal" id="inseam" name="inseam" value="<?= tgh_e($values['inseam']) ?>">
            </div>
        </div>
    </fieldset>

    <div class="m3-actions-row">
        <button type="submit" class="m3-btn"><?= $editing ? 'Save changes' : 'Publish listing' ?></button>
        <a class="m3-btn m3-btn-ghost" href="seller_dashboard.php">Cancel</a>
    </div>
</form>
<?php tgh_layout_end(); ?>
