<?php
/**
 * ThriftedGH - Login View (Task 2 Frontend Layout)
 * 
 * Features:
 * - Clean responsive form layout
 * - Email and password inputs
 * - Link to buyer registration
 * - Link for sellers / seller application
 * - Clearly noted as frontend placeholder (Task 3 will handle auth)
 */

$pageTitle = "Login to Your Account | ThriftedGH";
$activeNav = "login";

// Include shared header layout
require_once __DIR__ . '/layout/header.php';
?>

<section class="section auth-section">
    <div class="container auth-container">
        
        <div class="auth-card">
            
            <div class="auth-header">
                <span class="auth-icon">🔐</span>
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to your ThriftedGH account to manage orders, wishlist, and cart.</p>
            </div>

            <!-- Task Status / Educational Notice -->
            <div class="task-info-banner">
                <strong>Task 2 Frontend Prototype:</strong> This form demonstrates the responsive visual layout. PHP authentication, session handling, and database password verification will be implemented in <strong>Task 3</strong>.
            </div>

            <!-- Login Form -->
            <form action="#" method="POST" class="auth-form" onsubmit="event.preventDefault(); showPlaceholderAlert('User Login', 'Task 3 Buyer Registration and Login');">
                
                <div class="form-group">
                    <label for="loginEmail" class="form-label">Email Address <span class="required">*</span></label>
                    <input type="email" 
                           id="loginEmail" 
                           name="email" 
                           class="form-control" 
                           placeholder="e.g. kwame@st.ug.edu.gh" 
                           required 
                           autocomplete="email">
                </div>

                <div class="form-group">
                    <div class="form-label-row">
                        <label for="loginPassword" class="form-label">Password <span class="required">*</span></label>
                        <a href="#" class="forgot-link" onclick="event.preventDefault(); showPlaceholderAlert('Password Reset', 'Future enhancement');">Forgot password?</a>
                    </div>
                    <input type="password" 
                           id="loginPassword" 
                           name="password" 
                           class="form-control" 
                           placeholder="••••••••" 
                           required 
                           autocomplete="current-password">
                </div>

                <div class="form-check-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember" id="rememberMe">
                        <span class="checkmark"></span>
                        Remember my login on this device
                    </label>
                </div>

                <button type="submit" class="btn btn-lg btn-primary btn-block">
                    Sign In to ThriftedGH
                </button>
            </form>

            <!-- Links to Registration & Seller Application -->
            <div class="auth-footer-links">
                <p class="auth-switch-text">
                    Don't have an account yet? 
                    <a href="<?php echo $basePath; ?>index.php?page=register" class="auth-highlight-link">Create a Buyer Account</a>
                </p>

                <div class="seller-prompt-box">
                    <span class="prompt-icon">🛍️</span>
                    <div class="prompt-text">
                        <strong>Interested in selling thrift fashion?</strong>
                        <p>Join as an individual student seller or thrift store. <a href="<?php echo $basePath; ?>index.php?page=sellers">Learn about becoming a seller &rarr;</a></p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<?php
// Include shared footer layout
require_once __DIR__ . '/layout/footer.php';
?>
