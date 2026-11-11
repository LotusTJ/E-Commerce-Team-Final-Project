<?php
require_once __DIR__ . '/../controllers/Seller.php';

tgh_require_post();
tgh_require_login(tgh_login_url('../views/'));

$form = '../views/seller/apply.php';

if (!tgh_csrf_valid()) {
    tgh_flash_set('error', 'Your session expired. Please try again.');
    tgh_redirect($form);
}

$result = application_submit_ctr(tgh_current_user_id(), $_POST);

if ($result['ok']) {
    tgh_flash_set('success', 'Your seller application was submitted. An administrator will review it soon.');
    tgh_redirect('../views/buyer/dashboard.php');
}

tgh_flash_set('error', $result['errors']);
tgh_old_input_set($_POST, ['seller_type', 'shop_name', 'location', 'business_details', 'phone']);
tgh_redirect($form);
