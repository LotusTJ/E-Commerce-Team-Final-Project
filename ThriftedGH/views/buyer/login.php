<?php
require_once __DIR__ . '/../layout/header.php';
?>
<div style="max-width: 400px; margin: 3rem auto; background: white; padding: 2rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
    <h2 style="margin-bottom: 1.5rem; text-align: center;">Buyer Login</h2>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'registered'): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 1rem; font-size: 0.875rem;">
            Registration successful! Please log in with your credentials.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 1rem; font-size: 0.875rem;">
            <?php 
                if ($_GET['error'] === 'invalid_credentials') echo "Invalid email or password combination.";
                else echo "Please fill in all fields.";
            ?>
        </div>
    <?php endif; ?>

    <form action="/thriftedgh/actions/login_action.php" method="POST">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email Address</label>
            <input type="email" name="email" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
        </div>
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Password</label>
            <input type="password" name="password" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
        </div>
        <button type="submit" class="btn-primary" style="width: 100%; padding: 0.75rem; border: none; cursor: pointer; font-size: 1rem;">Login</button>
    </form>
    <p style="text-align: center; margin-top: 1rem; font-size: 0.875rem;">Don't have an account? <a href="register.php" style="color: #059669;">Register here</a></p>
</div>
<?php
require_once __DIR__ . '/../layout/footer.php';
?>