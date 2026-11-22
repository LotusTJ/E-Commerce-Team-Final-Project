<?php
require_once __DIR__ . '/../core/guard.php';
require_once __DIR__ . '/../controllers/Fit.php';

$fitPage = tgh_url('views/buyer/find_my_fit.php');

guardRequirePost();
guardRequireLogin();
guardRequireCsrf($fitPage);

$result = fit_save_measurements_ctr(tgh_current_user_id(), $_POST);

if ($result['ok']) {
    tgh_flash_set('success', 'Your measurements were saved.');
} else {
    tgh_flash_set('error', $result['errors']);
    tgh_old_input_set($_POST, ['chest', 'waist', 'hips', 'inseam']);
}
tgh_redirect($fitPage);
