<?php
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/auth.php';

if (authCurrentUser()) {
    tgh_redirect(tgh_url('views/buyer/dashboard.php'));
}

$pageTitle = 'Log in | ThriftedGH';
$activeNav = 'login';
require __DIR__ . '/../layout/header.php';
?>
<section class="section auth-section">
    <div class="container auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Log in</h1>
                <p class="auth-subtitle">Buyers and sellers use the same login.</p>
            </div>

            <?php tgh_render_flash(); ?>

            <form action="<?php echo tgh_e(tgh_url('actions/auth/login.php')); ?>" method="POST" class="auth-form">
                <?php echo tgh_csrf_field(); ?>
                <div class="form-group">
                    <label for="loginEmail" class="form-label">Email address</label>
                    <input type="email" id="loginEmail" name="email" class="form-control" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="loginPassword" class="form-label">Password</label>
                    <div class="password-field">
                        <input type="password" id="loginPassword" name="password" class="form-control" required autocomplete="current-password">
                        <button type="button" class="password-toggle" data-toggle-password="#loginPassword" aria-label="Show password"><?php echo tgh_icon('eye', 'eye-open') . tgh_icon('eye-off', 'eye-closed'); ?></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-lg btn-primary btn-block">Log in</button>
            </form>

            <div class="auth-footer-links">
                <p class="auth-switch-text">
                    New here?
                    <a href="<?php echo tgh_e(tgh_url('views/auth/register.php')); ?>" class="auth-highlight-link">Create an account</a>
                </p>
                <div class="seller-prompt-box">
                    <span class="prompt-icon"><?php echo tgh_icon('store'); ?></span>
                    <div class="prompt-text">
                        <strong>Want to sell?</strong>
                        <p>Make an account, then apply to become a seller. <a href="<?php echo tgh_e(tgh_url('views/public/sellers.php')); ?>">See our sellers</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="<?php echo $basePath; ?>js/auth.js" defer></script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
