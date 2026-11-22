<?php
require_once __DIR__ . '/../core/guard.php';
require_once __DIR__ . '/../controllers/Seller.php';

$form = tgh_url('views/seller/apply.php');

guardRequirePost();
guardRequireLogin();
guardRequireCsrf($form);

$result = application_submit_ctr(tgh_current_user_id(), $_POST);

if ($result['ok']) {
    tgh_flash_set('success', 'Your seller application was submitted. Upload your verification documents so an administrator can review it.');
    tgh_redirect(tgh_url('views/seller/documents.php'));
}

tgh_flash_set('error', $result['errors']);
tgh_old_input_set($_POST, ['seller_type', 'shop_name', 'location', 'business_details', 'phone']);
tgh_redirect($form);
