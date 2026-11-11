<?php
/**
 * ThriftedGH - Homepage View (Task 2)
 * 
 * Features:
 * 1. Hero banner with clear headline, intro, and "Shop Now" call to action.
 * 2. "How ThriftedGH Works" section.
 * 3. Featured products grid using reusable product card component.
 * 4. "Become a Seller" section for individual & business sellers.
 */

$pageTitle = "ThriftedGH | Online Thrift & Vintage Fashion Marketplace Ghana";
$activeNav = "home";

// Include shared header layout
require_once __DIR__ . '/layout/header.php';

// Load sample data
require_once __DIR__ . '/../core/sample_data.php';

// Include reusable product card component
require_once __DIR__ . '/components/product_card.php';
?>

<!-- 1. HERO SECTION -->
<section class="hero-section">
    <div class="container hero-container">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge-dot"></span> Sustainable Second-Hand Fashion in Ghana
            </div>
            <h1 class="hero-title">
                One-of-a-Kind <span class="highlight-text">Thrift &amp; Vintage</span> Fits, Curated For You
            </h1>
            <p class="hero-subtitle">
                Ghana's online destination for authentic 80s/90s vintage, streetwear, and quality pre-loved fashion. 
                Connecting campus thrifters, independent collectors, and Kantamanto sellers with buyers nationwide.
            </p>
            
            <div class="hero-cta-group">
                <a href="<?php echo $basePath; ?>index.php?page=shop" class="btn btn-lg btn-primary">
                    Shop Now <span>&rarr;</span>
                </a>
                <a href="<?php echo $basePath; ?>views/seller/apply.php" class="btn btn-lg btn-secondary">
                    Become a Seller
                </a>
            </div>

            <!-- Trust / Feature Badges -->
            <div class="hero-features-row">
                <div class="feature-item">
                    <span class="feature-icon">✨</span>
                    <span class="feature-label">1-of-1 Unique Drops</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">🇬🇭</span>
                    <span class="feature-label">Priced in Ghana Cedis (GH₵)</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">♻️</span>
                    <span class="feature-label">Circular &amp; Sustainable</span>
                </div>
            </div>
        </div>

        <div class="hero-image-wrap">
            <div class="hero-card-stack">
                <div class="hero-preview-card">
                    <div class="preview-tag">Featured Drop</div>
                    <img src="<?php echo $basePath; ?>images/products/denim-jacket.svg" alt="Vintage Denim Jacket" class="hero-img">
                    <div class="preview-details">
                        <h4>90s Vintage Denim Jacket</h4>
                        <div class="preview-price-row">
                            <span class="preview-price">GH₵ 220.00</span>
                            <span class="badge condition-like-new">Like New</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 2. HOW THRIFTEDGH WORKS SECTION -->
<section class="section how-it-works-section">
    <div class="container">
        <div class="section-header text-center">
            <span class="section-subtitle">Simple, Sustainable &amp; Safe</span>
            <h2 class="section-title">How ThriftedGH Works</h2>
            <p class="section-description">
                We make buying and selling second-hand clothing in Ghana as effortless and trustworthy as modern online shopping.
            </p>
        </div>

        <div class="steps-grid">
            <!-- Step 1 -->
            <div class="step-card">
                <div class="step-number">01</div>
                <div class="step-icon">🔎</div>
                <h3 class="step-title">Discover Curated Pieces</h3>
                <p class="step-text">
                    Browse unique, one-of-a-kind vintage jackets, retro tees, and branded denim hand-picked by trusted local sellers.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="step-card">
                <div class="step-number">02</div>
                <div class="step-icon">📏</div>
                <h3 class="step-title">Check Condition &amp; Fit</h3>
                <p class="step-text">
                    Every garment is graded (Like New, Good, or Fair) with honest descriptions, clothing sizes, and detailed measurements.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="step-card">
                <div class="step-number">03</div>
                <div class="step-icon">🛍️</div>
                <h3 class="step-title">Order with Confidence</h3>
                <p class="step-text">
                    Add pieces from multiple sellers to a single shopping cart and pay securely in Ghana Cedis through Paystack or Mobile Money.
                </p>
            </div>

            <!-- Step 4 -->
            <div class="step-card">
                <div class="step-number">04</div>
                <div class="step-icon">🚚</div>
                <h3 class="step-title">Campus &amp; Home Delivery</h3>
                <p class="step-text">
                    Receive your vintage package delivered promptly to your university campus hostel, residential address, or pickup hub.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 3. FEATURED PRODUCTS SECTION -->
<section class="section featured-products-section">
    <div class="container">
        <div class="section-header-flex">
            <div>
                <span class="section-subtitle">Freshly Added</span>
                <h2 class="section-title">Featured Drops</h2>
                <p class="section-description">Each item is a single, unique piece. Once it's gone, it's gone!</p>
            </div>
            <a href="<?php echo $basePath; ?>index.php?page=shop" class="btn btn-outline view-all-btn">
                View Full Shop &rarr;
            </a>
        </div>

        <div class="product-grid">
            <?php
            // Filter sample products to show featured ones (first 4 items)
            $featuredList = array_slice($sampleProducts, 0, 4, true);
            foreach ($featuredList as $item) {
                renderProductCard($item, $basePath);
            }
            ?>
        </div>
    </div>
</section>

<!-- 4. BECOME A SELLER CALL-TO-ACTION SECTION -->
<section class="section seller-cta-section">
    <div class="container">
        <div class="seller-cta-card">
            <div class="seller-cta-content">
                <span class="seller-cta-badge">Join Our Vendor Community</span>
                <h2 class="seller-cta-title">Have Pre-Loved Clothes to Sell in Ghana?</h2>
                <p class="seller-cta-desc">
                    Whether you are an individual university student decluttering your closet or a full-time thrift shop owner in Kantamanto, ThriftedGH provides the ideal platform to reach thousands of young fashion enthusiasts.
                </p>
                <ul class="seller-perks-list">
                    <li>✓ Free and easy seller registration</li>
                    <li>✓ Individual and registered business storefronts</li>
                    <li>✓ Direct exposure to student thrifters across Accra and Kumasi</li>
                    <li>✓ Keep 90% of your earnings with fast payouts</li>
                </ul>
                <div class="seller-cta-buttons">
                    <a href="<?php echo $basePath; ?>index.php?page=sellers" class="btn btn-lg btn-accent">
                        Learn About Becoming a Seller
                    </a>
                    <a href="<?php echo $basePath; ?>index.php?page=register" class="btn btn-lg btn-outline-light">
                        Create an Account
                    </a>
                </div>
            </div>
            <div class="seller-cta-graphic">
                <div class="storefront-mock-card">
                    <div class="mock-avatar">🏷️</div>
                    <h4>Your Storefront Name</h4>
                    <p>Accra • Verified Seller</p>
                    <div class="mock-stats">
                        <div><strong>18+</strong> Items Sold</div>
                        <div><strong>5.0 ★</strong> Buyer Rating</div>
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
