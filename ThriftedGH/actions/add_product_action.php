<?php
require_once __DIR__ . '/../core/guard.php';
require_once __DIR__ . '/../controllers/Product.php';

$dashboard = tgh_url('views/seller/dashboard.php');
$form = tgh_url('views/seller/product_form.php');

guardRequirePost();
guardRequireSeller();
guardRequireCsrf($form);

$result = seller_add_product_ctr(tgh_current_user_id(), $_POST, $_FILES['image'] ?? null);

if ($result['ok']) {
    tgh_flash_set('success', 'Your listing is live.');
    tgh_redirect($dashboard);
}

tgh_flash_set('error', $result['errors']);
tgh_old_input_set($_POST, ['title', 'description', 'price', 'category_id', 'size', 'item_condition', 'garment_type', 'chest', 'waist', 'hips', 'inseam']);
tgh_redirect($form);
