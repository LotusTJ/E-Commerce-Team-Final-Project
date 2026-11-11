<?php
/**
 * ThriftedGH - Product Details View (Task 2)
 * 
 * Features:
 * - Product image & condition badge
 * - Title, price in GH₵, size, condition
 * - Full item description
 * - Garment measurement table (matching product_measurements schema)
 * - Seller profile info card
 * - Add to Cart action button (frontend placeholder for Task 8)
 */

// Load sample data
require_once __DIR__ . '/../core/sample_data.php';

// Retrieve requested product ID, defaulting to product 1
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$product = isset($sampleProducts[$productId]) ? $sampleProducts[$productId] : $sampleProducts[1];

// Associated seller info
$seller = isset($sampleSellers[$product['seller_id']]) ? $sampleSellers[$product['seller_id']] : reset($sampleSellers);

$pageTitle = htmlspecialchars($product['title']) . " | ThriftedGH";
$activeNav = "shop";

// Include shared header layout
require_once __DIR__ . '/layout/header.php';
require_once __DIR__ . '/components/product_card.php';
?>

<!-- Breadcrumbs -->
<div class="breadcrumb-bar">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb-list">
                <li><a href="<?php echo $basePath; ?>index.php?page=home">Home</a></li>
                <li><a href="<?php echo $basePath; ?>index.php?page=shop">Shop</a></li>
                <li class="active"><?php echo htmlspecialchars($product['title']); ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Product Details Section -->
<section class="section product-detail-section">
    <div class="container">
        <div class="product-detail-grid">
            
            <!-- Left: Product Image Gallery -->
            <div class="product-gallery">
                <div class="product-main-image-wrap">
                    <img src="<?php echo $basePath . htmlspecialchars($product['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>" 
                         class="product-detail-image" 
                         id="mainProductImg">
                    
                    <span class="product-badge condition-<?php echo htmlspecialchars($product['item_condition']); ?> detail-badge">
                        Condition: <?php echo htmlspecialchars($product['condition_label']); ?>
                    </span>
                    <span class="stock-pill">Only 1 Available (One-of-a-kind)</span>
                </div>
            </div>

            <!-- Right: Product Information & Purchase Panel -->
            <div class="product-summary">
                
                <div class="product-meta-header">
                    <span class="product-category-tag"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    <span class="product-sku">Item #TGH-00<?php echo $product['product_id']; ?></span>
                </div>

                <h1 class="product-detail-title"><?php echo htmlspecialchars($product['title']); ?></h1>

                <!-- Price Row -->
                <div class="product-price-row">
                    <div class="price-display">
                        <span class="currency-symbol">GH₵</span>
                        <span class="price-value"><?php echo number_format($product['price'], 2); ?></span>
                    </div>
                    <span class="delivery-hint">📦 Standard delivery to Accra &amp; campuses</span>
                </div>

                <!-- Size and Condition Badges -->
                <div class="product-attributes-panel">
                    <div class="attribute-pill">
                        <span class="attr-label">Size</span>
                        <span class="attr-value size-val"><?php echo htmlspecialchars($product['size']); ?></span>
                    </div>
                    <div class="attribute-pill">
                        <span class="attr-label">Condition</span>
                        <span class="attr-value condition-val"><?php echo htmlspecialchars($product['condition_label']); ?></span>
                    </div>
                    <div class="attribute-pill">
                        <span class="attr-label">Availability</span>
                        <span class="attr-value in-stock-val">In Stock (1 piece)</span>
                    </div>
                </div>

                <!-- Product Description -->
                <div class="product-desc-box">
                    <h3 class="desc-heading">Item Description</h3>
                    <p class="desc-text"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <!-- Garment Measurements (Table Schema Preview) -->
                <div class="measurements-box">
                    <div class="measurements-header">
                        <h3 class="desc-heading">Exact Garment Measurements</h3>
                        <span class="measurements-note">Measured flat in centimeters (cm)</span>
                    </div>
                    <div class="measurements-grid">
                        <?php if (!empty($product['measurements']['chest_cm'])): ?>
                            <div class="meas-item">
                                <span class="meas-name">Chest Width</span>
                                <span class="meas-value"><?php echo $product['measurements']['chest_cm']; ?> cm</span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['measurements']['waist_cm'])): ?>
                            <div class="meas-item">
                                <span class="meas-name">Waist</span>
                                <span class="meas-value"><?php echo $product['measurements']['waist_cm']; ?> cm</span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['measurements']['hips_cm'])): ?>
                            <div class="meas-item">
                                <span class="meas-name">Hips</span>
                                <span class="meas-value"><?php echo $product['measurements']['hips_cm']; ?> cm</span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['measurements']['inseam_cm'])): ?>
                            <div class="meas-item">
                                <span class="meas-name">Inseam Length</span>
                                <span class="meas-value"><?php echo $product['measurements']['inseam_cm']; ?> cm</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="fit-info-text">
                        💡 <em>Tip: Compare these flat measurements to your best-fitting clothes at home for the ideal fit.</em>
                    </p>
                </div>

                <!-- Purchase Buttons -->
                <div class="product-purchase-box">
                    <button type="button" 
                            class="btn btn-lg btn-primary btn-block placeholder-action-btn"
                            data-action-name="Add to Cart" 
                            data-task-info="Task 8 Multi-Seller Shopping Cart">
                            🛒 Add to Cart (Placeholder)
                    </button>
                    
                    <a href="<?php echo $basePath; ?>index.php?page=cart" class="btn btn-lg btn-outline btn-block">
                        View Shopping Cart
                    </a>
                </div>

                <!-- Seller Profile Card -->
                <div class="seller-profile-card">
                    <div class="seller-card-avatar">🏷️</div>
                    <div class="seller-card-info">
                        <div class="seller-type-tag"><?php echo ($seller['seller_type'] === 'business') ? 'Approved Thrift Business' : 'Approved Individual Seller'; ?></div>
                        <h4 class="seller-card-name"><?php echo htmlspecialchars($seller['shop_name']); ?></h4>
                        <p class="seller-card-location">📍 <?php echo htmlspecialchars($seller['location']); ?></p>
                        <p class="seller-card-desc"><?php echo htmlspecialchars($seller['description']); ?></p>
                        <div class="seller-card-links">
                            <a href="<?php echo $basePath; ?>index.php?page=sellers" class="seller-store-link">
                                View Seller's Full Storefront &rarr;
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- More Recommended Thrift Drops -->
        <div class="related-products-section">
            <h2 class="section-title">You Might Also Like</h2>
            <div class="product-grid">
                <?php
                // Show up to 3 other products
                $relatedCount = 0;
                foreach ($sampleProducts as $relItem) {
                    if ($relItem['product_id'] != $product['product_id'] && $relatedCount < 3) {
                        renderProductCard($relItem, $basePath);
                        $relatedCount++;
                    }
                }
                ?>
            </div>
        </div>

    </div>
</section>

<?php
// Include shared footer layout
require_once __DIR__ . '/layout/footer.php';
?>
