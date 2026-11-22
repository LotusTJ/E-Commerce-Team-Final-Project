<?php
require_once __DIR__ . '/../../controllers/Cart.php';
require_once __DIR__ . '/../../classes/CatalogueRepository.php';
require_once __DIR__ . '/../components/product_card.php';

$featured = [];
try {
    $featured = array_slice((new CatalogueRepository())->browse([], 4), 0, 4);
} catch (Throwable $e) {
    error_log('Home catalogue: ' . $e->getMessage());
}

$pageTitle = 'ThriftedGH | Second-hand clothing in Ghana';
$activeNav = 'home';
require __DIR__ . '/../layout/header.php';
?>
<section class="hero-section">
    <div class="container hero-container">
        <div class="hero-content">
            <div class="hero-badge"><span class="badge-dot"></span> Second-hand clothing in Ghana</div>
            <h1 class="hero-title">Buy and sell <span class="highlight-text">thrifted clothes</span> without the market crowd</h1>
            <p class="hero-subtitle">
                Student sellers and local thrift shops list their pieces here. Each item is one of one, with its size, condition and measurements shown before you pay.
            </p>

            <div class="hero-cta-group">
                <a href="<?php echo $basePath; ?>views/public/shop.php" class="btn btn-lg btn-primary">Shop now <?php echo tgh_icon('arrow-right'); ?></a>
                <a href="<?php echo $basePath; ?>views/seller/apply.php" class="btn btn-lg btn-secondary">Become a seller</a>
            </div>

            <div class="hero-features-row">
                <div class="feature-item">
                    <span class="feature-icon"><?php echo tgh_icon('tag'); ?></span>
                    <span class="feature-label">Every piece is one of one</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon"><?php echo tgh_icon('shield'); ?></span>
                    <span class="feature-label">Sellers are approved by an admin</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon"><?php echo tgh_icon('repeat'); ?></span>
                    <span class="feature-label">Prices in Ghana cedis (GH₵)</span>
                </div>
            </div>
        </div>

        <div class="hero-image-wrap">
            <div class="hero-card-stack">
                <div class="hero-preview-card">
                    <img src="<?php echo $basePath; ?>images/products/denim-jacket.svg" alt="Denim jacket" class="hero-img">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section how-it-works-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">How it works</h2>
            <p class="section-description">Four steps from browsing to your door.</p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">01</div>
                <div class="step-icon"><?php echo tgh_icon('search'); ?></div>
                <h3 class="step-title">Browse</h3>
                <p class="step-text">Filter by category, size, condition and price to find what fits your style and budget.</p>
            </div>
            <div class="step-card">
                <div class="step-number">02</div>
                <div class="step-icon"><?php echo tgh_icon('ruler'); ?></div>
                <h3 class="step-title">Check the fit</h3>
                <p class="step-text">Save your measurements once and Find My Fit shows which listings are likely to suit you.</p>
            </div>
            <div class="step-card">
                <div class="step-number">03</div>
                <div class="step-icon"><?php echo tgh_icon('bag'); ?></div>
                <h3 class="step-title">Pay with Paystack</h3>
                <p class="step-text">Add pieces to your cart and pay by card or mobile money. Your pieces are held for 15 minutes while you pay.</p>
            </div>
            <div class="step-card">
                <div class="step-number">04</div>
                <div class="step-icon"><?php echo tgh_icon('truck'); ?></div>
                <h3 class="step-title">Get it delivered</h3>
                <p class="step-text">The seller prepares and ships your order, and you can follow each stage from your orders page.</p>
            </div>
        </div>
    </div>
</section>

<section class="section featured-products-section">
    <div class="container">
        <div class="section-header-flex">
            <div>
                <h2 class="section-title">Latest listings</h2>
                <p class="section-description">Once a piece is sold it disappears from the shop.</p>
            </div>
            <a href="<?php echo $basePath; ?>views/public/shop.php" class="btn btn-outline view-all-btn">View the full shop <?php echo tgh_icon('arrow-right'); ?></a>
        </div>

        <div class="product-grid">
            <?php if (!$featured): ?>
                <p>No pieces are listed right now. Check back soon.</p>
            <?php endif; ?>
            <?php foreach ($featured as $item) { renderProductCard($item, $basePath); } ?>
        </div>
    </div>
</section>

<section class="section seller-cta-section">
    <div class="container">
        <div class="seller-cta-card">
            <div class="seller-cta-content">
                <h2 class="seller-cta-title">Have clothes to sell?</h2>
                <p class="seller-cta-desc">
                    Whether you are a student clearing your wardrobe or you run a thrift shop, you can apply for a seller account. An administrator reviews your application and documents before you can list anything.
                </p>
                <ul class="seller-perks-list">
                    <li><?php echo tgh_icon('check'); ?> Individual and business storefronts</li>
                    <li><?php echo tgh_icon('check'); ?> Upload your verification documents online</li>
                    <li><?php echo tgh_icon('check'); ?> ThriftedGH keeps a 10% commission, you keep 90%</li>
                </ul>
                <div class="seller-cta-buttons">
                    <a href="<?php echo $basePath; ?>views/public/sellers.php" class="btn btn-lg btn-accent">See our sellers</a>
                    <a href="<?php echo $basePath; ?>views/auth/register.php" class="btn btn-lg btn-outline-light">Create an account</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
