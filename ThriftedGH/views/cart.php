<?php
/**
 * ThriftedGH - Shopping Cart View (Task 2 Frontend Layout)
 * 
 * Features:
 * - Layout prepared for multi-seller cart items and totals
 * - Empty cart state view
 * - Interactive state switcher to easily preview both active cart and empty cart states
 * - Clear placeholders for Task 8 (Cart) and Task 9 (Paystack Checkout)
 */

$pageTitle = "Your Shopping Cart | ThriftedGH";
$activeNav = "cart";

// Check if previewing empty cart state via URL or default
$cartState = isset($_GET['state']) && $_GET['state'] === 'empty' ? 'empty' : 'active';

// Include shared header layout
require_once __DIR__ . '/layout/header.php';
?>

<!-- Cart Page Header -->
<section class="page-banner cart-banner">
    <div class="container">
        <div class="page-banner-content">
            <span class="banner-badge">Secure Bag</span>
            <h1 class="page-title">Shopping Cart</h1>
            <p class="page-subtitle">
                Review your selected one-of-one pieces before proceeding to checkout.
            </p>
        </div>
    </div>
</section>

<section class="section cart-section">
    <div class="container">

        <!-- Task Status & State Preview Switcher Bar -->
        <div class="cart-preview-toolbar">
            <div class="task-info-tag">
                <strong>Task 2 Frontend Mockup:</strong> Cart logic, sessions &amp; totals will be linked in <strong>Task 8</strong>. Paystack checkout will be connected in <strong>Task 9</strong>.
            </div>

            <!-- View Switcher to preview populated vs empty states -->
            <div class="state-toggle-group">
                <span class="state-toggle-label">Preview State:</span>
                <a href="<?php echo $basePath; ?>index.php?page=cart&state=active" 
                   class="btn btn-sm <?php echo ($cartState === 'active') ? 'btn-primary' : 'btn-ghost'; ?>">
                    Populated Cart (2 Items)
                </a>
                <a href="<?php echo $basePath; ?>index.php?page=cart&state=empty" 
                   class="btn btn-sm <?php echo ($cartState === 'empty') ? 'btn-primary' : 'btn-ghost'; ?>">
                    Empty Cart State
                </a>
            </div>
        </div>

        <?php if ($cartState === 'empty'): ?>
            <!-- ================= EMPTY CART STATE ================= -->
            <div class="empty-cart-card">
                <div class="empty-cart-icon">🛒</div>
                <h2 class="empty-cart-title">Your Cart is Currently Empty</h2>
                <p class="empty-cart-text">
                    Looks like you haven't added any pre-loved vintage items to your cart yet. 
                    Every thrift piece is unique and one-of-a-kind — don't miss out on rare gems!
                </p>
                <div class="empty-cart-actions">
                    <a href="<?php echo $basePath; ?>index.php?page=shop" class="btn btn-lg btn-primary">
                        Start Shopping Thrift Drops &rarr;
                    </a>
                    <a href="<?php echo $basePath; ?>index.php?page=sellers" class="btn btn-lg btn-outline">
                        Browse Approved Sellers
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- ================= POPULATED CART LAYOUT ================= -->
            
            <!-- Multi-seller notice -->
            <div class="multi-seller-notice">
                <span class="notice-icon">ℹ️</span>
                <span>
                    <strong>Multi-Seller Cart:</strong> You have items from 2 independent sellers. All items will be purchased together in one seamless transaction.
                </span>
            </div>

            <div class="cart-layout-grid">
                
                <!-- Cart Items List -->
                <div class="cart-items-wrapper">
                    <div class="cart-table-header">
                        <span class="col-item">Item Details</span>
                        <span class="col-seller">Seller</span>
                        <span class="col-price">Price</span>
                        <span class="col-action">Action</span>
                    </div>

                    <!-- Sample Cart Item 1 -->
                    <div class="cart-item-row">
                        <div class="cart-item-info">
                            <img src="<?php echo $basePath; ?>images/products/denim-jacket.svg" 
                                 alt="Vintage Denim Jacket" 
                                 class="cart-item-thumb">
                            <div class="cart-item-meta">
                                <span class="cart-item-tag condition-like-new">Like New</span>
                                <h3 class="cart-item-name">
                                    <a href="<?php echo $basePath; ?>index.php?page=product&id=1">
                                        90s Vintage Oversized Denim Jacket
                                    </a>
                                </h3>
                                <div class="cart-item-attrs">
                                    <span>Size: <strong>L</strong></span>
                                    <span>Qty: <strong>1</strong> (Unique 1-of-1)</span>
                                </div>
                            </div>
                        </div>

                        <div class="cart-item-seller">
                            <span class="seller-pill">Accra Vintage Vault</span>
                            <small class="seller-loc">Osu, Accra</small>
                        </div>

                        <div class="cart-item-price">
                            <span class="currency">GH₵</span> 220.00
                        </div>

                        <div class="cart-item-actions">
                            <button type="button" 
                                    class="btn-remove placeholder-action-btn"
                                    data-action-name="Remove Item" 
                                    data-task-info="Task 8 Multi-Seller Shopping Cart"
                                    title="Remove this item">
                                &times; Remove
                            </button>
                        </div>
                    </div>

                    <!-- Sample Cart Item 2 -->
                    <div class="cart-item-row">
                        <div class="cart-item-info">
                            <img src="<?php echo $basePath; ?>images/products/graphic-tee.svg" 
                                 alt="Retro Graphic Band Tee" 
                                 class="cart-item-thumb">
                            <div class="cart-item-meta">
                                <span class="cart-item-tag condition-good">Good Condition</span>
                                <h3 class="cart-item-name">
                                    <a href="<?php echo $basePath; ?>index.php?page=product&id=2">
                                        Retro Graphic Band Tee (Tour Edition)
                                    </a>
                                </h3>
                                <div class="cart-item-attrs">
                                    <span>Size: <strong>M</strong></span>
                                    <span>Qty: <strong>1</strong> (Unique 1-of-1)</span>
                                </div>
                            </div>
                        </div>

                        <div class="cart-item-seller">
                            <span class="seller-pill">Campus Thrift Hub</span>
                            <small class="seller-loc">Legon, Accra</small>
                        </div>

                        <div class="cart-item-price">
                            <span class="currency">GH₵</span> 95.00
                        </div>

                        <div class="cart-item-actions">
                            <button type="button" 
                                    class="btn-remove placeholder-action-btn"
                                    data-action-name="Remove Item" 
                                    data-task-info="Task 8 Multi-Seller Shopping Cart"
                                    title="Remove this item">
                                &times; Remove
                            </button>
                        </div>
                    </div>

                    <!-- Continue Shopping Link -->
                    <div class="cart-table-footer">
                        <a href="<?php echo $basePath; ?>index.php?page=shop" class="continue-link">
                            &larr; Continue Shopping More Pieces
                        </a>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="cart-summary-sidebar">
                    <div class="order-summary-card">
                        <h3 class="summary-title">Order Summary</h3>

                        <div class="summary-line">
                            <span>Subtotal (2 Items)</span>
                            <span class="summary-val">GH₵ 315.00</span>
                        </div>

                        <div class="summary-line">
                            <span>Estimated Campus Delivery</span>
                            <span class="summary-val">GH₵ 25.00</span>
                        </div>

                        <div class="summary-divider"></div>

                        <div class="summary-line total-line">
                            <span>Estimated Total</span>
                            <span class="total-val">GH₵ 340.00</span>
                        </div>

                        <div class="summary-notes">
                            <p class="currency-note">All prices in Ghanaian Cedis (GH₵)</p>
                            <p class="commission-note">Includes marketplace buyer protection</p>
                        </div>

                        <button type="button" 
                                class="btn btn-lg btn-primary btn-block checkout-btn placeholder-action-btn"
                                data-action-name="Checkout" 
                                data-task-info="Task 9 Paystack Integration">
                            Proceed to Checkout (Placeholder) &rarr;
                        </button>

                        <div class="payment-badges-row">
                            <span class="pay-badge">🔒 Paystack Verified</span>
                            <span class="pay-badge">📱 MoMo Ready</span>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    </div>
</section>

<?php
// Include shared footer layout
require_once __DIR__ . '/layout/footer.php';
?>
