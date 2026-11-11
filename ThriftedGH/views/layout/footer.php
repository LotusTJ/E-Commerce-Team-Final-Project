<?php
/**
 * ThriftedGH - Shared Footer Layout
 * Reusable footer component for all marketplace views.
 */

// Determine base path for script asset reference
$basePath = (strpos($_SERVER['SCRIPT_NAME'], '/views/') !== false) ? '../' : './';
?>
    </main>
    <!-- Main Content Area Ends -->

    <!-- Shared Site Footer -->
    <footer class="site-footer">
        <div class="container footer-container">
            <!-- Brand & Mission Column -->
            <div class="footer-col footer-about">
                <div class="footer-brand">
                    <span class="brand-icon">🧥</span>
                    <span class="brand-name">Thrifted<span class="brand-gh">GH</span></span>
                </div>
                <p class="footer-description">
                    ThriftedGH is an online marketplace for second-hand and vintage clothing in Ghana. 
                    We connect individual student sellers and local thrift businesses with fashion-forward buyers 
                    seeking sustainable, one-of-a-kind clothing.
                </p>
            </div>

            <!-- Quick Navigation Links Column -->
            <div class="footer-col footer-links-col">
                <h4 class="footer-heading">Marketplace Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $basePath; ?>index.php?page=home">Home</a></li>
                    <li><a href="<?php echo $basePath; ?>index.php?page=shop">Shop All Clothing</a></li>
                    <li><a href="<?php echo $basePath; ?>index.php?page=sellers">Approved Sellers</a></li>
                    <li><a href="<?php echo $basePath; ?>index.php?page=cart">Shopping Cart</a></li>
                </ul>
            </div>

            <!-- Account & Opportunities Column -->
            <div class="footer-col footer-links-col">
                <h4 class="footer-heading">User Accounts</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo $basePath; ?>index.php?page=login">Sign In</a></li>
                    <li><a href="<?php echo $basePath; ?>index.php?page=register">Create Buyer Account</a></li>
                    <li><a href="<?php echo $basePath; ?>index.php?page=sellers">Become a Seller</a></li>
                </ul>
            </div>
        </div>

        <!-- Copyright & Academic Project Banner -->
        <div class="footer-bottom">
            <div class="container footer-bottom-content">
                <p class="copyright-text">
                    &copy; 2026 <strong>ThriftedGH</strong>. University E-Commerce Group Project. All rights reserved.
                </p>
                <p class="project-note">
                    Developed with Vanilla PHP, HTML5, CSS3 &amp; JavaScript.
                </p>
            </div>
        </div>
    </footer>

    <!-- Main JavaScript File -->
    <script src="<?php echo $basePath; ?>js/main.js"></script>
</body>
</html>
