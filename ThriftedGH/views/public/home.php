<?php
require_once __DIR__ . '/../../controllers/Cart.php';
require_once __DIR__ . '/../../classes/CatalogueRepository.php';
require_once __DIR__ . '/../components/product_card.php';

$products = [];
try {
    $products = (new CatalogueRepository())->browse([], 60);
} catch (Throwable $e) {
    error_log('Home catalogue: ' . $e->getMessage());
}

$newIn = array_slice($products, 0, 8);

$tiles = [];
foreach ($products as $item) {
    $key = (int)($item['category_id'] ?? 0);
    if ($key < 1) {
        continue;
    }
    if (!isset($tiles[$key])) {
        $tiles[$key] = ['name' => $item['category_name'], 'image' => $item['image_path'], 'from' => (float)$item['price'], 'count' => 0];
    }
    $tiles[$key]['count']++;
    if ((float)$item['price'] < $tiles[$key]['from']) {
        $tiles[$key]['from'] = (float)$item['price'];
    }
}
uasort($tiles, function ($a, $b) {
    return $b['count'] <=> $a['count'];
});
$tiles = array_slice($tiles, 0, 4, true);

$topsId = 0;
foreach ($products as $item) {
    if (strcasecmp((string)$item['category_name'], 'Tops') === 0) {
        $topsId = (int)$item['category_id'];
        break;
    }
}
$shopUrl = tgh_url('views/public/shop.php');
$looks = [
    ['file' => 'look-1.jpg', 'label' => 'Sweatshirts', 'href' => $topsId ? $shopUrl . '?category=' . $topsId : $shopUrl],
    ['file' => 'look-2.jpg', 'label' => 'Graphic tees', 'href' => $topsId ? $shopUrl . '?category=' . $topsId : $shopUrl],
    ['file' => 'look-3.jpg', 'label' => 'Outerwear', 'href' => $shopUrl],
];
$looks = array_values(array_filter($looks, function ($look) {
    return is_file(__DIR__ . '/../../images/home/' . $look['file']);
}));
$sellPhoto = is_file(__DIR__ . '/../../images/home/sell.jpg') ? 'images/home/sell.jpg' : null;

$heroPhoto = is_file(__DIR__ . '/../../images/home/hero.jpg') ? 'images/home/hero.jpg' : null;
$heroImages = [];
foreach (array_slice($products, 0, 3) as $item) {
    $heroImages[] = ['src' => tgh_catalogue_image($item['image_path']), 'alt' => $item['title']];
}

$pageTitle = 'ThriftedGH | Second-hand fashion from Ghana';
$activeNav = 'home';
require __DIR__ . '/../layout/header.php';
?>
<section class="home-hero">
    <div class="container home-hero-grid">
        <div class="home-hero-copy">
            <p class="home-eyebrow">New pieces added daily</p>
            <h1 class="home-hero-title">Thrifted.<br>One of one.</h1>
            <p class="home-hero-sub">Second-hand fashion from Ghana's student sellers and thrift shops.</p>
            <div class="home-hero-actions">
                <a href="<?php echo $basePath; ?>views/public/shop.php" class="btn btn-lg btn-accent">Shop new in</a>
                <a href="<?php echo $basePath; ?>views/seller/apply.php" class="btn btn-lg btn-outline-light">Start selling</a>
            </div>
        </div>
        <div class="home-hero-media">
            <?php if ($heroPhoto): ?>
                <img class="home-hero-photo" src="<?php echo tgh_e($basePath . $heroPhoto); ?>" alt="Featured look">
            <?php else: ?>
                <?php foreach ($heroImages as $i => $img): ?>
                    <img class="home-hero-tile tile-<?php echo (int)$i; ?>" src="<?php echo tgh_e($basePath . $img['src']); ?>" alt="<?php echo tgh_e($img['alt']); ?>">
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($looks): ?>
<section class="home-section">
    <div class="container">
        <div class="home-head">
            <h2 class="home-h2">Shop the look</h2>
            <a class="home-link" href="<?php echo tgh_e($shopUrl); ?>">View all <?php echo tgh_icon('arrow-right'); ?></a>
        </div>
        <div class="look-grid">
            <?php foreach ($looks as $look): ?>
                <a class="cat-tile look-tile" href="<?php echo tgh_e($look['href']); ?>">
                    <img src="<?php echo tgh_e($basePath . 'images/home/' . $look['file']); ?>" alt="<?php echo tgh_e($look['label']); ?>" loading="lazy">
                    <span class="cat-bar">
                        <span class="cat-name"><?php echo tgh_e($look['label']); ?></span>
                        <span class="cat-from">Shop now</span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($tiles): ?>
<section class="home-section">
    <div class="container">
        <div class="home-head">
            <h2 class="home-h2">Shop by category</h2>
        </div>
        <div class="cat-grid">
            <?php foreach ($tiles as $id => $tile): ?>
                <a class="cat-tile" href="<?php echo tgh_e(tgh_url('views/public/shop.php')); ?>?category=<?php echo (int)$id; ?>">
                    <img src="<?php echo tgh_e($basePath . tgh_catalogue_image($tile['image'])); ?>" alt="" loading="lazy">
                    <span class="cat-bar">
                        <span class="cat-name"><?php echo tgh_e($tile['name']); ?></span>
                        <span class="cat-from">From GH₵ <?php echo number_format($tile['from'], 0); ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="home-section">
    <div class="container">
        <div class="home-head">
            <h2 class="home-h2">New in</h2>
            <a class="home-link" href="<?php echo $basePath; ?>views/public/shop.php">View all <?php echo tgh_icon('arrow-right'); ?></a>
        </div>
        <div class="product-grid">
            <?php if (!$newIn): ?>
                <p>No pieces are listed right now. Check back soon.</p>
            <?php endif; ?>
            <?php foreach ($newIn as $item) { renderProductCard($item, $basePath); } ?>
        </div>
    </div>
</section>

<section class="home-strip">
    <div class="container home-strip-grid">
        <div class="strip-item"><?php echo tgh_icon('tag'); ?><span>Every piece is one of one</span></div>
        <div class="strip-item"><?php echo tgh_icon('shield'); ?><span>Sellers checked by our team</span></div>
        <div class="strip-item"><?php echo tgh_icon('receipt'); ?><span>Pay by card or mobile money</span></div>
    </div>
</section>

<section class="home-sell">
    <div class="home-sell-grid<?php echo $sellPhoto ? ' has-photo' : ''; ?>">
        <div class="home-sell-copy">
            <h2 class="home-sell-title">Got clothes to sell?</h2>
            <p class="home-sell-sub">Open your shop and keep 90% of every sale.</p>
            <a href="<?php echo $basePath; ?>views/seller/apply.php" class="btn btn-lg btn-light">Start selling</a>
        </div>
        <?php if ($sellPhoto): ?>
            <img class="home-sell-photo" src="<?php echo tgh_e($basePath . $sellPhoto); ?>" alt="" loading="lazy">
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/../layout/footer.php'; ?>
