<?php
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/auth.php';

$current = authCurrentUser();
if ($current) {
    tgh_redirect(tgh_role_home($current['role']));
}

$pageTitle = 'Seller sign in | ThriftedGH';
$activeNav = '';
$headerMode = 'plain';

ob_start();
?>
<p class="auth-switch-text">
    Not selling yet?
    <a href="<?php echo tgh_e(tgh_url('views/auth/register.php')); ?>" class="auth-highlight-link">Create an account</a>
    then apply to sell from your account page.
</p>
<p class="auth-switch-text">
    Here to shop?
    <a href="<?php echo tgh_e(tgh_url('views/auth/login.php')); ?>" class="auth-highlight-link">Sign in as a buyer</a>
</p>
<?php
$login = [
    'title' => 'Seller sign in',
    'subtitle' => 'Manage your shop and fulfil your orders.',
    'action' => 'actions/auth/seller_login.php',
    'button' => 'Sign in to my shop',
    'footer' => ob_get_clean(),
];

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../components/login_card.php';
require __DIR__ . '/../layout/footer.php';
