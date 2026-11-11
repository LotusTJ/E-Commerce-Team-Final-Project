<?php
/**
 * ThriftedGH - Sellers Directory View (Task 2)
 * 
 * Features:
 * - Approved seller directory
 * - Seller cards with shop name, location, seller type badge, bio
 * - Links to storefront placeholders (Task 7)
 * - Information section on how to apply as a seller
 */

$pageTitle = "Verified Thrift Sellers | ThriftedGH";
$activeNav = "sellers";

// Include shared header layout
require_once __DIR__ . '/layout/header.php';

// Load sample data
require_once __DIR__ . '/../core/sample_data.php';
?>

<!-- Sellers Banner -->
<section class="page-banner sellers-banner">
    <div class="container">
        <div class="page-banner-content">
            <span class="banner-badge">Our Community</span>
            <h1 class="page-title">Verified Thrift Sellers</h1>
            <p class="page-subtitle">
                Explore trusted student thrifters, independent curators, and commercial vintage vendors across Accra, Kumasi, and university campuses.
            </p>
        </div>
    </div>
</section>

<!-- Sellers Directory Section -->
<section class="section sellers-section">
    <div class="container">
        
        <div class="section-intro-text">
            <p>
                Every seller on ThriftedGH undergoes identity review and admin approval before listing items. 
                Support student entrepreneurs and local businesses by shopping directly from their curated collections.
            </p>
        </div>

        <!-- Sellers Grid -->
        <div class="sellers-grid">
            <?php foreach ($sampleSellers as $s): ?>
                <article class="seller-card">
                    <div class="seller-card-top">
                        <div class="seller-avatar-large">
                            <?php echo ($s['seller_type'] === 'business') ? '🏬' : '🎓'; ?>
                        </div>
                        <div class="seller-title-box">
                            <span class="seller-type-pill <?php echo ($s['seller_type'] === 'business') ? 'type-business' : 'type-individual'; ?>">
                                <?php echo ($s['seller_type'] === 'business') ? 'Thrift Business' : 'Individual / Student'; ?>
                            </span>
                            <h3 class="seller-shop-title"><?php echo htmlspecialchars($s['shop_name']); ?></h3>
                            <div class="seller-location-text">📍 <?php echo htmlspecialchars($s['location']); ?></div>
                        </div>
                    </div>

                    <p class="seller-bio-text">
                        <?php echo htmlspecialchars($s['description']); ?>
                    </p>

                    <div class="seller-stats-bar">
                        <div class="stat-item">
                            <span class="stat-number">★ <?php echo $s['rating']; ?></span>
                            <span class="stat-name">Seller Rating</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $s['items_count']; ?></span>
                            <span class="stat-name">Active Drops</span>
                        </div>
                    </div>

                    <div class="seller-card-action">
                        <button type="button" 
                                class="btn btn-primary btn-block placeholder-action-btn"
                                data-action-name="View Storefront" 
                                data-task-info="Task 7 Public Seller Storefronts">
                            Visit Storefront (Placeholder) &rarr;
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- How To Become A Seller Card -->
        <div class="seller-join-box">
            <div class="join-icon">🚀</div>
            <div class="join-content">
                <h3>Want to Sell Your Vintage &amp; Pre-Loved Clothing?</h3>
                <p>
                    ThriftedGH is welcoming student thrifters and boutique vintage vendors. 
                    Register for a buyer account first, then submit your seller application for instant review.
                </p>
                <div class="join-buttons">
                    <a href="<?php echo $basePath; ?>index.php?page=register" class="btn btn-accent">
                        Register &amp; Apply to Sell
                    </a>
                    <a href="<?php echo $basePath; ?>index.php?page=login" class="btn btn-outline">
                        Sign In to Existing Account
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

<?php
// Include shared footer layout
require_once __DIR__ . '/layout/footer.php';
?>
