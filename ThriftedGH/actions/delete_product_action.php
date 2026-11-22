<?php
require_once __DIR__ . '/../core/guard.php';
require_once __DIR__ . '/../controllers/Product.php';

$dashboard = tgh_url('views/seller/dashboard.php');

guardRequirePost();
guardRequireSeller();
guardRequireCsrf($dashboard);

$result = seller_remove_product_ctr(tgh_current_user_id(), (int)tgh_input_string($_POST, 'product_id'));

if ($result['ok']) {
    tgh_flash_set('success', 'The listing was removed.');
} else {
    tgh_flash_set('error', $result['errors']);
}
tgh_redirect($dashboard);
