<?php
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/auth.php';

$current = authCurrentUser();
if ($current) {
    tgh_redirect(tgh_role_home($current['role']));
}

$pageTitle = 'Sign in | ThriftedGH';
$activeNav = 'login';

ob_start();
?>
<p class="auth-switch-text">
    New here?
    <a href="<?php echo tgh_e(tgh_url('views/auth/register.php')); ?>" class="auth-highlight-link">Create an account</a>
</p>
<div class="seller-prompt-box">
    <span class="prompt-icon"><?php echo tgh_icon('store'); ?></span>
    <div class="prompt-text">
        <strong>Selling on ThriftedGH?</strong>
        <p><a href="<?php echo tgh_e(tgh_url('views/auth/seller_login.php')); ?>">Sign in to your shop</a></p>
    </div>
</div>
<?php
$login = [
    'title' => 'Welcome back',
    'subtitle' => 'Sign in to shop, check out and track your orders.',
    'action' => 'actions/auth/login.php',
    'button' => 'Sign in',
    'footer' => ob_get_clean(),
];

require __DIR__ . '/../layout/header.php';
require __DIR__ . '/../components/login_card.php';
require __DIR__ . '/../layout/footer.php';
