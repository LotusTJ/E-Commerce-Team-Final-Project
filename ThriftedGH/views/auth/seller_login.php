<?php
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/auth.php';

$current = authCurrentUser();
if ($current) {
    tgh_redirect(tgh_role_home($current['role']));
}

$pageTitle = 'Seller log in | ThriftedGH';
$activeNav = '';
$headerMode = 'plain';

ob_start();
?>
<p class="auth-switch-text">
    Not a seller yet?
    <a href="<?php echo tgh_e(tgh_url('views/auth/register.php')); ?>" class="auth-highlight-link">Create an account</a>
    and apply from your account page.
</p>
<p class="auth-switch-text">
    Shopping instead?
    <a href="<?php echo tgh_e(tgh_url('views/auth/login.php')); ?>" class="auth-highlight-link">Buyer log in</a>
</p>
<?php
$login = [
    'title' => 'Seller log in',
    'subtitle' => 'Manage your listings and the orders you need to fulfil.',
    'action' => 'actions/auth/seller_login.php',
    'button' => 'Log in to seller portal',
    'footer' => ob_get_clean(),
];

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../components/login_card.php';
require __DIR__ . '/../layout/footer.php';
