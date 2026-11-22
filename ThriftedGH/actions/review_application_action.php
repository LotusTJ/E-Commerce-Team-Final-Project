<?php
require_once __DIR__ . '/../core/guard.php';
require_once __DIR__ . '/../controllers/Seller.php';

$page = tgh_url('views/admin/applications.php');

guardRequirePost();
guardRequireAdmin();
guardRequireCsrf($page);

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
