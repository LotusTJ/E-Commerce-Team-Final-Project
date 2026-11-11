<?php
require_once __DIR__ . '/../layout/header.php';
?>
<div style="max-width: 450px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 0.5rem; border: 1px solid #e5e7eb;">
    <h2 style="margin-bottom: 1.5rem; text-align: center;">Create Buyer Account</h2>

    <?php if (isset($_GET['error'])): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 1rem; font-size: 0.875rem;">
            <?php 
                if ($_GET['error'] === 'email_exists') echo "This email address is already registered.";
                elseif ($_GET['error'] === 'empty_fields') echo "Please fill in all required fields.";
                elseif ($_GET['error'] === 'weak_password') echo "Password must be at least 6 characters long.";
                elseif ($_GET['error'] === 'invalid_email') echo "Please enter a valid email address.";
                else echo "An error occurred. Please try again.";
            ?>
        </div>
    <?php endif; ?>

    <form action="/thriftedgh/actions/register_action.php" method="POST" id="registerForm" onsubmit="return validateForm()">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Full Name *</label>
            <input type="text" name="full_name" id="full_name" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
        </div>
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email Address *</label>
            <input type="email" name="email" id="email" required style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
        </div>
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Phone Number</label>
            <input type="text" name="phone" id="phone" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
        </div>
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Password *</label>
            <input type="password" name="password" id="password" required minlength="6" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem;">
        </div>
        <button type="submit" class="btn-primary" style="width: 100%; padding: 0.75rem; border: none; cursor: pointer; font-size: 1rem;">Register</button>
    </form>
    <p style="text-align: center; margin-top: 1rem; font-size: 0.875rem;">Already have an account? <a href="login.php" style="color: #059669;">Log in</a></p>
</div>

<script>
function validateForm() {
    const pass = document.getElementById('password').value;
    if (pass.length < 6) {
        alert("Password must be at least 6 characters long.");
        return false;
    }
    return true;
}
</script>
<?php
require_once __DIR__ . '/../layout/footer.php';
?>