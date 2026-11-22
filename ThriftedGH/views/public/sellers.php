<?php
require_once __DIR__ . '/../../controllers/Product.php';

$sellers = storefront_directory_ctr();

$pageTitle = 'Sellers | ThriftedGH';
$activeNav = 'sellers';
require __DIR__ . '/../layout/header.php';
?>
<section class="page-banner sellers-banner">
    <div class="container">
        <div class="page-banner-content">
            <h1 class="page-title">Our sellers</h1>
            <p class="page-subtitle">Student sellers and thrift shops that have been approved to list on ThriftedGH.</p>
        </div>
    </div>
</section>
<section class="section sellers-section">
    <div class="container">

        <div class="section-intro-text">
            <p>Every seller submits identity documents and is approved by an administrator before listing anything.</p>
        </div>
        <?php if ($sellers === null): ?>
            <div class="empty-state-card">
                <h2>Sellers are unavailable right now</h2>
                <p>We could not load the seller directory. Please try again shortly.</p>
            </div>
        <?php elseif (!$sellers): ?>
            <div class="empty-state-card">
                <h2>No approved sellers yet</h2>
                <p>Approved sellers will appear here as soon as they are verified.</p>
            </div>
        <?php else: ?>
        <div class="sellers-grid">
            <?php foreach ($sellers as $s): ?>
                <article class="seller-card">
                    <div class="seller-card-top">
                        <div class="seller-avatar-large">
                            <?php echo tgh_icon($s['seller_type'] === 'business' ? 'store' : 'graduation'); ?>
                        </div>
                        <div class="seller-title-box">
                            <span class="seller-type-pill <?php echo ($s['seller_type'] === 'business') ? 'type-business' : 'type-individual'; ?>">
                                <?php echo ($s['seller_type'] === 'business') ? 'Business' : 'Individual'; ?>
                            </span>
                            <h3 class="seller-shop-title"><?php echo htmlspecialchars($s['shop_name']); ?></h3>
                            <div class="seller-location-text"><?php echo tgh_icon('map-pin'); ?> <?php echo htmlspecialchars($s['location'] ?: 'Location not provided'); ?></div>
                        </div>
                    </div>

                    <p class="seller-bio-text">
                        <?php echo htmlspecialchars($s['description'] ?: 'This seller has not added a description yet.'); ?>
                    </p>

                    <div class="seller-stats-bar">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo (int)$s['item_count']; ?></span>
                            <span class="stat-name">Items for sale</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo htmlspecialchars(date('M Y', strtotime($s['created_at']))); ?></span>
                            <span class="stat-name">Joined</span>
                        </div>
                    </div>

                    <div class="seller-card-action">
                        <a href="<?php echo $basePath; ?>views/public/storefront.php?seller_id=<?php echo (int)$s['seller_id']; ?>"
                           class="btn btn-primary btn-block">
                            Visit storefront
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="seller-join-box">
            <div class="join-icon"><?php echo tgh_icon('rocket'); ?></div>
            <div class="join-content">
                <h3>Want to sell on ThriftedGH?</h3>
                <p>Create an account, then submit a seller application with your documents. An administrator will review it.</p>
                <div class="join-buttons">
                    <a href="<?php echo $basePath; ?>views/seller/apply.php" class="btn btn-accent">
                        Apply to sell
                    </a>
                    <a href="<?php echo $basePath; ?>views/auth/login.php" class="btn btn-outline">
                        Sign in
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
