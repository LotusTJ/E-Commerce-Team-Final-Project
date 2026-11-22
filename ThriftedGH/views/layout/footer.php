<?php
require_once __DIR__ . '/../../core/helpers.php';
require_once __DIR__ . '/../../core/auth.php';
$basePath = tgh_base();
$footerUser = null;
try {
    $footerUser = authCurrentUser();
} catch (Throwable $e) {
    error_log('Footer user lookup: ' . $e->getMessage());
}
$footerMode = isset($headerMode) ? $headerMode : (($footerUser && in_array($footerUser['role'], ['seller', 'admin'], true)) ? $footerUser['role'] : 'public');
?>
    </main>

    <footer class="site-footer">
        <?php if ($footerMode === 'public'): ?>
        <div class="container footer-container">
            <div class="footer-col footer-about">
                <div class="footer-brand">
                    <span class="brand-icon"><?php echo tgh_icon('shirt'); ?></span>
                    <span class="brand-name">Thrifted<span class="brand-gh">GH</span></span>
                </div>
                <p class="footer-description">
                    A marketplace for second-hand clothing in Ghana. Student sellers and local thrift shops list one-of-one pieces, and buyers pay securely through Paystack.
                </p>
            </div>

            <div class="footer-col footer-links-col">
                <h4 class="footer-heading">Browse</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $basePath; ?>views/public/home.php">Home</a></li>
                    <li><a href="<?php echo $basePath; ?>views/public/shop.php">Shop</a></li>
                    <li><a href="<?php echo $basePath; ?>views/public/sellers.php">Sellers</a></li>
                    <li><a href="<?php echo $basePath; ?>views/buyer/cart.php">Cart</a></li>
                </ul>
            </div>

            <div class="footer-col footer-links-col">
                <h4 class="footer-heading">Accounts</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $basePath; ?>views/auth/login.php">Buyer log in</a></li>
                    <li><a href="<?php echo $basePath; ?>views/auth/seller_login.php">Seller log in</a></li>
                    <li><a href="<?php echo $basePath; ?>views/auth/register.php">Create an account</a></li>
                    <li><a href="<?php echo $basePath; ?>views/seller/apply.php">Become a seller</a></li>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <div class="footer-bottom">
            <div class="container footer-bottom-content">
                <p class="copyright-text">&copy; 2026 ThriftedGH. Ashesi University E-Commerce group project.</p>
            </div>
        </div>
    </footer>

    <script src="<?php echo $basePath; ?>js/main.js"></script>
</body>
</html>
