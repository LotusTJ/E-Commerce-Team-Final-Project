<?php
require_once __DIR__ . '/../controllers/Seller.php';

tgh_require_post();
authRequireAdmin();

$page = '../views/admin/applications.php';

if (!tgh_csrf_valid()) {
    tgh_flash_set('error', 'Your session expired. Please try again.');
    tgh_redirect($page);
}

$result = application_review_ctr(
    tgh_current_user_id(),
    tgh_input_string($_POST, 'application_id'),
    tgh_input_string($_POST, 'decision')
);

if ($result['ok']) {
    $verb = $result['status'] === 'approved' ? 'approved' : 'rejected';
    tgh_flash_set('success', $result['shop_name'] . ' was ' . $verb . '.');
} else {
    tgh_flash_set('error', $result['error']);
}
tgh_redirect($page);
