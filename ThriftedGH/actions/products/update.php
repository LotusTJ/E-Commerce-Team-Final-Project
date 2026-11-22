<?php
require_once __DIR__ . '/../../core/guard.php';
require_once __DIR__ . '/../../controllers/Product.php';

$dashboard = tgh_url('views/seller/dashboard.php');
$productId = (int)tgh_input_string($_POST, 'product_id');
$form = tgh_url('views/seller/product_form.php') . '?id=' . $productId;

guardRequirePost();
guardRequireSeller();
guardRequireCsrf($form);

$result = seller_update_product_ctr(tgh_current_user_id(), $productId, $_POST, $_FILES['image'] ?? null);

if ($result['ok']) {
    tgh_flash_set('success', 'Your listing was updated.');
    tgh_redirect($dashboard);
}

tgh_flash_set('error', $result['errors']);
tgh_old_input_set($_POST, ['title', 'description', 'price', 'category_id', 'size', 'item_condition', 'garment_type', 'chest', 'waist', 'hips', 'inseam']);
tgh_redirect($form);
