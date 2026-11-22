<?php
require_once __DIR__ . '/../../controllers/Cart.php';
require_once __DIR__ . '/../../classes/CatalogueRepository.php';

if (!function_exists('renderProductCard')) {
    function renderProductCard($product, $basePath = null)
    {
        $basePath = $basePath ?? tgh_base();
        $image = tgh_catalogue_image($product['image_path'] ?? '');
        $conditionClass = 'condition-' . strtolower(str_replace('_', '-', $product['item_condition']));
        $conditionName = $product['condition_label'] ?? ucwords(str_replace('_', ' ', $product['item_condition']));
        $productUrl = tgh_url('views/public/product.php') . '?id=' . (int)$product['product_id'];
        $cardUser = function_exists('authCurrentUser') ? authCurrentUser() : null;
        $loggedIn = $cardUser !== null;
        $canBuy = $cardUser === null || $cardUser['role'] === 'buyer';
        ?>
        <article class="product-card" data-category="<?php echo tgh_e($product['category_name'] ?? ''); ?>" data-size="<?php echo tgh_e($product['size'] ?? ''); ?>">
            <div class="product-image-wrap">
                <a href="<?php echo tgh_e($productUrl); ?>" class="product-img-link" tabindex="-1" aria-hidden="true">
                    <img src="<?php echo tgh_e($basePath . $image); ?>" alt="<?php echo tgh_e($product['title']); ?>" class="product-image" loading="lazy">
                </a>
                <span class="product-badge <?php echo tgh_e($conditionClass); ?>"><?php echo tgh_e($conditionName); ?></span>
                <span class="product-size-badge">Size <?php echo tgh_e($product['size'] ?? 'n/a'); ?></span>
            </div>

            <div class="product-info">
                <div class="product-meta">
                    <span class="product-category"><?php echo tgh_e($product['category_name'] ?? 'Clothing'); ?></span>
                    <span class="product-stock-tag">1 of 1</span>
                </div>

                <h3 class="product-title"><a href="<?php echo tgh_e($productUrl); ?>"><?php echo tgh_e($product['title']); ?></a></h3>

                <div class="product-seller">
                    <span class="seller-icon"><?php echo tgh_icon('tag'); ?></span>
                    <span class="seller-text"><?php echo tgh_e($product['seller_name'] ?? 'Seller'); ?></span>
                </div>

                <div class="product-footer">
                    <div class="product-price-box">
                        <span class="currency">GH₵</span>
                        <span class="amount"><?php echo number_format((float)$product['price'], 2); ?></span>
                    </div>

                    <div class="product-actions">
                        <?php if ($loggedIn && $canBuy): ?>
                            <form method="post" action="<?php echo tgh_e(tgh_url('actions/add_to_cart_action.php')); ?>">
                                <?php echo tgh_csrf_field(); ?>
                                <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
                                <button class="btn btn-sm btn-primary" type="submit">Add to cart</button>
                            </form>
                        <?php elseif (!$loggedIn): ?>
                            <a class="btn btn-sm btn-primary" href="<?php echo tgh_e(tgh_url('views/auth/login.php')); ?>">Sign in to buy</a>
                        <?php else: ?>
                            <a class="btn btn-sm btn-outline" href="<?php echo tgh_e($productUrl); ?>">View piece</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </article>
        <?php
    }
}
