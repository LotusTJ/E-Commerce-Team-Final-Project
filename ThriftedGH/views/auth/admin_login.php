<?php
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/auth.php';

$current = authCurrentUser();
if ($current) {
    tgh_redirect(tgh_role_home($current['role']));
}

$pageTitle = 'Administration | ThriftedGH';
$activeNav = '';
$headerMode = 'plain';

$login = [
    'title' => 'Administration',
    'subtitle' => 'Authorised staff only.',
    'action' => 'actions/auth/admin_login.php',
    'button' => 'Log in',
    'footer' => '',
];

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../components/login_card.php';
require __DIR__ . '/../layout/footer.php';
