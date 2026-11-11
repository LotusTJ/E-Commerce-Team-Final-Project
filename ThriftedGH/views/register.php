<?php
/**
 * ThriftedGH - Buyer Registration View (Task 2 Frontend Layout)
 * 
 * Features:
 * - Responsive registration form
 * - Full Name, Email, Phone, Password, Confirm Password fields
 * - Clear indicator for prospective sellers (Task 4)
 * - Frontend validation placeholder (Task 3 will handle server-side registration)
 */

$pageTitle = "Create a Buyer Account | ThriftedGH";
$activeNav = "register";

// Include shared header layout
require_once __DIR__ . '/layout/header.php';
?>

<section class="section auth-section">
    <div class="container auth-container">
        
        <div class="auth-card">
            
            <div class="auth-header">
                <span class="auth-icon">📝</span>
                <h1 class="auth-title">Create Buyer Account</h1>
                <p class="auth-subtitle">Join ThriftedGH to shop one-of-a-kind pre-loved fashion and save your favorite drops.</p>
            </div>

            <!-- Task Status / Educational Notice -->
            <div class="task-info-banner">
                <strong>Task 2 Frontend Prototype:</strong> This form demonstrates the responsive visual layout. Server-side validation, password hashing with <code>password_hash()</code>, and database insertion will be implemented in <strong>Task 3</strong>.
            </div>

            <!-- Registration Form -->
            <form action="#" method="POST" class="auth-form" onsubmit="event.preventDefault(); showPlaceholderAlert('Account Registration', 'Task 3 Buyer Registration and Login');">
                
                <div class="form-group">
                    <label for="regFullName" class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" 
                           id="regFullName" 
                           name="full_name" 
                           class="form-control" 
                           placeholder="e.g. Kwame Mensah" 
                           required 
                           autocomplete="name">
                </div>

                <div class="form-group">
                    <label for="regEmail" class="form-label">Email Address <span class="required">*</span></label>
                    <input type="email" 
                           id="regEmail" 
                           name="email" 
                           class="form-control" 
                           placeholder="e.g. kwame@st.ug.edu.gh" 
                           required 
                           autocomplete="email">
                    <span class="field-hint">University or personal email accepted</span>
                </div>

                <div class="form-group">
                    <label for="regPhone" class="form-label">Phone / WhatsApp Number (Optional)</label>
                    <input type="tel" 
                           id="regPhone" 
                           name="phone" 
                           class="form-control" 
                           placeholder="e.g. 024 123 4567" 
                           autocomplete="tel">
                    <span class="field-hint">Used by delivery couriers for campus parcel handover</span>
                </div>

                <div class="form-row-2col">
                    <div class="form-group">
                        <label for="regPassword" class="form-label">Password <span class="required">*</span></label>
                        <input type="password" 
                               id="regPassword" 
                               name="password" 
                               class="form-control" 
                               placeholder="Min. 8 characters" 
                               required 
                               autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="regConfirmPassword" class="form-label">Confirm Password <span class="required">*</span></label>
                        <input type="password" 
                               id="regConfirmPassword" 
                               name="confirm_password" 
                               class="form-control" 
                               placeholder="Re-enter password" 
                               required 
                               autocomplete="new-password">
                    </div>
                </div>

                <div class="form-check-group">
                    <label class="checkbox-container">
                        <input type="checkbox" required id="agreeTerms">
                        <span class="checkmark"></span>
                        I agree to ThriftedGH marketplace guidelines and buyer terms.
                    </label>
                </div>

                <button type="submit" class="btn btn-lg btn-primary btn-block">
                    Create My Buyer Account
                </button>
            </form>

            <!-- Seller Application Callout -->
            <div class="seller-prompt-box highlighted-seller-box">
                <span class="prompt-icon">🏪</span>
                <div class="prompt-text">
                    <strong>Want to sell clothes as an individual or business?</strong>
                    <p>
                        In our marketplace design, all sellers start by creating an account. Once registered, you can submit a seller application with your shop name and location for admin approval in <strong>Task 4</strong>!
                    </p>
                    <a href="<?php echo $basePath; ?>index.php?page=sellers" class="auth-highlight-link">Read seller requirements &rarr;</a>
                </div>
            </div>

            <div class="auth-footer-links">
                <p class="auth-switch-text">
                    Already have an account? 
                    <a href="<?php echo $basePath; ?>index.php?page=login" class="auth-highlight-link">Sign In here</a>
                </p>
            </div>

        </div>

    </div>
</section>

<?php
// Include shared footer layout
require_once __DIR__ . '/layout/footer.php';
?>
