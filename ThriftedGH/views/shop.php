<?php
/**
 * ThriftedGH - Shop Catalog View (Task 2)
 * 
 * Features:
 * - Page heading & catalog intro
 * - Real-time keyword search bar
 * - Category, size, and condition filters
 * - Dynamic product grid with sample products
 * - Reusable product card components
 */

$pageTitle = "Shop Curated Thrift Clothing | ThriftedGH";
$activeNav = "shop";

// Include shared header layout
require_once __DIR__ . '/layout/header.php';

// Load sample data
require_once __DIR__ . '/../core/sample_data.php';

// Include reusable product card component
require_once __DIR__ . '/components/product_card.php';
?>

<!-- Shop Header Banner -->
<section class="page-banner shop-banner">
    <div class="container">
        <div class="page-banner-content">
            <span class="banner-badge">Ghana Thrift Collection</span>
            <h1 class="page-title">Shop All Clothing</h1>
            <p class="page-subtitle">
                Authentic, one-of-a-kind second-hand pieces, vintage finds, and pre-loved street fashion.
            </p>
        </div>
    </div>
</section>

<!-- Main Catalog Section -->
<section class="section shop-section">
    <div class="container">

        <!-- Search and Filter Controls -->
        <div class="catalog-controls">
            <!-- Search Bar -->
            <div class="catalog-search-wrap">
                <div class="search-input-group">
                    <span class="search-icon">🔍</span>
                    <input type="text" 
                           id="shopSearchInput" 
                           class="form-control search-input" 
                           placeholder="Search vintage jackets, band tees, corduroys, denim..."
                           aria-label="Search clothing items">
                    <button type="button" id="clearSearchBtn" class="clear-search-btn" title="Clear search" style="display:none;">&times;</button>
                </div>
            </div>

            <!-- Filter Badges Row -->
            <div class="filter-toolbar">
                <!-- Category Filter -->
                <div class="filter-group">
                    <label for="categoryFilter" class="filter-label">Category:</label>
                    <select id="categoryFilter" class="form-select filter-select">
                        <option value="all">All Categories</option>
                        <option value="Jackets & Coats">Jackets &amp; Coats</option>
                        <option value="T-Shirts & Tops">T-Shirts &amp; Tops</option>
                        <option value="Trousers & Jeans">Trousers &amp; Jeans</option>
                        <option value="Dresses & Skirts">Dresses &amp; Skirts</option>
                        <option value="Shirts">Shirts</option>
                        <option value="Knitwear & Sweaters">Knitwear &amp; Sweaters</option>
                    </select>
                </div>

                <!-- Size Filter -->
                <div class="filter-group">
                    <label for="sizeFilter" class="filter-label">Size:</label>
                    <select id="sizeFilter" class="form-select filter-select">
                        <option value="all">All Sizes</option>
                        <option value="S">Small (S)</option>
                        <option value="M">Medium (M)</option>
                        <option value="L">Large (L)</option>
                        <option value="XL">Extra Large (XL)</option>
                        <option value="32">Waist 32</option>
                        <option value="34">Waist 34</option>
                    </select>
                </div>

                <!-- Sort Order -->
                <div class="filter-group">
                    <label for="sortOrder" class="filter-label">Sort:</label>
                    <select id="sortOrder" class="form-select filter-select">
                        <option value="default">Latest Listings</option>
                        <option value="price-asc">Price: Low to High</option>
                        <option value="price-desc">Price: High to Low</option>
                    </select>
                </div>

                <!-- Reset Filters Button -->
                <button type="button" id="resetFiltersBtn" class="btn btn-sm btn-ghost">
                    Reset Filters
                </button>
            </div>
        </div>

        <!-- Results Counter & Status -->
        <div class="catalog-status-bar">
            <span class="results-count" id="resultsCount">Showing <strong><?php echo count($sampleProducts); ?></strong> unique items</span>
            <span class="catalog-badge">1-of-1 Pieces • No Restocks</span>
        </div>

        <!-- Product Grid -->
        <div class="product-grid" id="catalogProductGrid">
            <?php
            foreach ($sampleProducts as $product) {
                renderProductCard($product, $basePath);
            }
            ?>
        </div>

        <!-- No Results Fallback Message (Hidden by default) -->
        <div class="no-results-message" id="noResultsMsg" style="display: none;">
            <div class="empty-state-card">
                <span class="empty-state-icon">🧥</span>
                <h3>No Matching Items Found</h3>
                <p>We couldn't find any one-of-a-kind pieces matching your filter criteria.</p>
                <button type="button" class="btn btn-primary" onclick="resetAllFilters()">View All Items</button>
            </div>
        </div>

    </div>
</section>

<?php
// Include shared footer layout
require_once __DIR__ . '/layout/footer.php';
?>
