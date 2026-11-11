<?php
/**
 * ThriftedGH - Reusable Product Card Component
 * 
 * Displays a single fashion item card with:
 * - Product image & condition badge
 * - Product title
 * - Clothing size
 * - Item condition
 * - Seller name
 * - Price in Ghana Cedis (GH₵)
 * - View details & Add to Cart action buttons
 * 
 * Can be used by setting $product before including this file,
 * or by calling renderProductCard($product, $basePath).
 */

if (!function_exists('renderProductCard')) {
    function renderProductCard($product, $basePath = './') {
        $conditionClass = 'condition-' . strtolower(str_replace('_', '-', $product['item_condition']));
        $conditionName = isset($product['condition_label']) ? $product['condition_label'] : ucwords(str_replace('_', ' ', $product['item_condition']));
        $productUrl = $basePath . 'index.php?page=product&id=' . (int)$product['product_id'];
        ?>
        <article class="product-card" data-category="<?php echo htmlspecialchars($product['category_name'] ?? ''); ?>" data-size="<?php echo htmlspecialchars($product['size'] ?? ''); ?>">
            <!-- Product Image & Badges -->
            <div class="product-image-wrap">
                <a href="<?php echo $productUrl; ?>" class="product-img-link" tabindex="-1" aria-hidden="true">
                    <img src="<?php echo $basePath . htmlspecialchars($product['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>" 
                         class="product-image" 
                         loading="lazy">
                </a>
                
                <!-- Floating Condition Badge -->
                <span class="product-badge <?php echo $conditionClass; ?>" title="Item Condition">
                    <?php echo htmlspecialchars($conditionName); ?>
                </span>

                <!-- Clothing Size Tag -->
                <span class="product-size-badge" title="Clothing Size">
                    Size: <?php echo htmlspecialchars($product['size']); ?>
                </span>
            </div>

            <!-- Product Card Content -->
            <div class="product-info">
                <div class="product-meta">
                    <span class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Vintage'); ?></span>
                    <span class="product-stock-tag">1 of 1 piece</span>
                </div>

                <h3 class="product-title">
                    <a href="<?php echo $productUrl; ?>">
                        <?php echo htmlspecialchars($product['title']); ?>
                    </a>
                </h3>

                <!-- Seller Attribute -->
                <div class="product-seller">
                    <span class="seller-icon">🏷️</span>
                    <span class="seller-text">Seller: <strong><?php echo htmlspecialchars($product['seller_name'] ?? 'Local Vendor'); ?></strong></span>
                </div>

                <!-- Price & Actions Footer -->
                <div class="product-footer">
                    <div class="product-price-box">
                        <span class="currency">GH₵</span>
                        <span class="amount"><?php echo number_format($product['price'], 2); ?></span>
                    </div>

                    <div class="product-actions">
                        <a href="<?php echo $productUrl; ?>" class="btn btn-sm btn-outline" title="View garment details and measurements">
                            Details
                        </a>
                        <button type="button" 
                                class="btn btn-sm btn-primary placeholder-action-btn" 
                                data-action-name="Add to Cart" 
                                data-task-info="Task 8 Multi-Seller Shopping Cart"
                                title="Add to Cart (Placeholder)">
                            + Cart
                        </button>
                    </div>
                </div>
            </div>
        </article>
        <?php
    }
}

// If included directly with $product set in scope, render it immediately
if (isset($product) && is_array($product)) {
    $currentBasePath = isset($basePath) ? $basePath : './';
    renderProductCard($product, $currentBasePath);
}
