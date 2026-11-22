<?php
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/auth.php';

if (authCurrentUser()) {
    tgh_redirect(tgh_url('views/buyer/dashboard.php'));
}

$old = array_merge(['full_name' => '', 'email' => '', 'phone' => ''], tgh_old_input_take());

$pageTitle = 'Create an account | ThriftedGH';
$activeNav = 'register';
require __DIR__ . '/../layout/header.php';
?>
<section class="section auth-section">
    <div class="container auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Create an account</h1>
                <p class="auth-subtitle">One account lets you buy, and you can apply to sell later.</p>
            </div>

            <?php tgh_render_flash(); ?>

            <form action="<?php echo tgh_e(tgh_url('actions/register_action.php')); ?>" method="POST" class="auth-form" id="registerForm">
                <?php echo tgh_csrf_field(); ?>
                <div class="form-group">
                    <label for="full_name" class="form-label">Full name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required maxlength="100" autocomplete="name" value="<?php echo tgh_e($old['full_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" id="email" name="email" class="form-control" required maxlength="150" autocomplete="email" value="<?php echo tgh_e($old['email']); ?>">
                </div>
                <div class="form-group">
                    <label for="phone" class="form-label">Phone number (optional)</label>
                    <input type="tel" id="phone" name="phone" class="form-control" maxlength="20" autocomplete="tel" value="<?php echo tgh_e($old['phone']); ?>">
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                        <button type="button" class="password-toggle" data-toggle-password="#password" aria-label="Show password"><?php echo tgh_icon('eye', 'eye-open') . tgh_icon('eye-off', 'eye-closed'); ?></button>
                    </div>
                    <p class="form-hint">At least 8 characters, with a letter and a number.</p>
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm password</label>
                    <div class="password-field">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8" autocomplete="new-password">
                        <button type="button" class="password-toggle" data-toggle-password="#confirm_password" aria-label="Show password"><?php echo tgh_icon('eye', 'eye-open') . tgh_icon('eye-off', 'eye-closed'); ?></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-lg btn-primary btn-block">Create account</button>
            </form>

            <div class="auth-footer-links">
                <p class="auth-switch-text">
                    Already have an account?
                    <a href="<?php echo tgh_e(tgh_url('views/auth/login.php')); ?>" class="auth-highlight-link">Sign in</a>
                </p>
                <div class="seller-prompt-box">
                    <span class="prompt-icon"><?php echo tgh_icon('store'); ?></span>
                    <div class="prompt-text">
                        <strong>Planning to sell?</strong>
                        <p>Sellers apply after creating an account. <a href="<?php echo tgh_e(tgh_url('views/public/sellers.php')); ?>">Read how it works</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="<?php echo $basePath; ?>js/auth.js" defer></script>
<?php require __DIR__ . '/../layout/footer.php'; ?>
