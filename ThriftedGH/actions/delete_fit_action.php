<?php
require_once __DIR__ . '/../core/guard.php';
require_once __DIR__ . '/../controllers/Fit.php';

$fitPage = tgh_url('views/buyer/find_my_fit.php');

guardRequirePost();
guardRequireLogin();
guardRequireCsrf($fitPage);

$result = fit_delete_measurements_ctr(tgh_current_user_id());

if ($result['ok']) {
    tgh_flash_set('success', 'Your measurements were deleted.');
} else {
    tgh_flash_set('error', $result['errors']);
}
tgh_redirect($fitPage);
