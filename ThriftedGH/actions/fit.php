<?php

require_once __DIR__ . '/../core/guards.php';
require_once __DIR__ . '/../controllers/Fit.php';

tgh_require_post();
tgh_require_login(tgh_login_url('../views/'));

$page = '../views/find_my_fit.php';
$action = tgh_input_string($_POST, 'action');

if (!in_array($action, ['save', 'delete'], true)) {
    tgh_flash_set('error', 'Unknown action.');
    tgh_redirect($page);
}

if (!tgh_csrf_valid()) {
    tgh_flash_set('error', 'Your session expired. Please try again.');
    tgh_redirect($page);
}

if ($action === 'save') {
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
    if ($action === 'save') {
        tgh_old_input_set($_POST, ['chest', 'waist', 'hips', 'inseam']);
    }
}
tgh_redirect($page);
