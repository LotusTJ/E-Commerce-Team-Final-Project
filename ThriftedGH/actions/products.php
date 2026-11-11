<?php

require_once __DIR__ . '/../controllers/Product.php';
require_once __DIR__ . '/../controllers/Fit.php';

tgh_require_post();
tgh_require_login(tgh_login_url('../views/'));

$dashboard = '../views/seller_dashboard.php';
$action = tgh_input_string($_POST, 'action');
$productId = (int)tgh_input_string($_POST, 'product_id');
$oldKeys = ['title', 'description', 'price', 'category_id', 'size', 'item_condition', 'garment_type', 'chest', 'waist', 'hips', 'inseam'];

$fitPage = '../views/find_my_fit.php';

if (in_array($action, ['fit_save', 'fit_delete'], true)) {
    if (!tgh_csrf_valid()) {
        tgh_flash_set('error', 'Your session expired. Please try again.');
        tgh_redirect($fitPage);
    }
    if ($action === 'fit_save') {
        $result = fit_save_measurements_ctr(tgh_current_user_id(), $_POST);
        $successMessage = 'Your measurements were saved.';
    } else {
        $result = fit_delete_measurements_ctr(tgh_current_user_id());
        $successMessage = 'Your measurements were deleted.';
    }
    if ($result['ok']) {
        tgh_flash_set('success', $successMessage);
    } else {
        tgh_flash_set('error', $result['errors']);
        if ($action === 'fit_save') {
            tgh_old_input_set($_POST, ['chest', 'waist', 'hips', 'inseam']);
        }
    }
    tgh_redirect($fitPage);
}

if (!in_array($action, ['add', 'edit', 'delete'], true)) {
    tgh_flash_set('error', 'Unknown action.');
    tgh_redirect($dashboard);
}

$form = '../views/seller_product_form.php' . ($action === 'edit' ? '?id=' . $productId : '');
$failureTarget = $action === 'delete' ? $dashboard : $form;

if (!tgh_csrf_valid()) {
    tgh_flash_set('error', 'Your session expired. Please try again.');
    tgh_redirect($failureTarget);
}

$userId = tgh_current_user_id();

if ($action === 'add') {
    $result = seller_add_product_ctr($userId, $_POST, $_FILES['image'] ?? null);
    $successMessage = 'Your listing is live.';
} elseif ($action === 'edit') {
    $result = seller_update_product_ctr($userId, $productId, $_POST, $_FILES['image'] ?? null);
    $successMessage = 'Your listing was updated.';
} else {
    $result = seller_remove_product_ctr($userId, $productId);
    $successMessage = 'The listing was removed.';
}

if ($result['ok']) {
    tgh_flash_set('success', $successMessage);
    tgh_redirect($dashboard);
}

tgh_flash_set('error', $result['errors']);
if ($action !== 'delete') {
    tgh_old_input_set($_POST, $oldKeys);
}
tgh_redirect($failureTarget);
