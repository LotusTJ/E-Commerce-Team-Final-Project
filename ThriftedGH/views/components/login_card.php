<section class="section auth-section">
    <div class="container auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title"><?php echo tgh_e($login['title']); ?></h1>
                <p class="auth-subtitle"><?php echo tgh_e($login['subtitle']); ?></p>
            </div>

            <?php tgh_render_flash(); ?>

            <form action="<?php echo tgh_e(tgh_url($login['action'])); ?>" method="POST" class="auth-form">
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
                <button type="submit" class="btn btn-lg btn-primary btn-block"><?php echo tgh_e($login['button']); ?></button>
            </form>

            <?php if (!empty($login['footer'])): ?>
            <div class="auth-footer-links">
                <?php echo $login['footer']; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<script src="<?php echo $basePath; ?>js/auth.js" defer></script>
