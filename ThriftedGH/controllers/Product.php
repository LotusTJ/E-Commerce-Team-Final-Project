<?php

require_once __DIR__ . '/../classes/ProductClass.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function tgh_e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function tgh_current_user_id()
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
}

function tgh_is_logged_in()
{
    return tgh_current_user_id() > 0;
}

function tgh_redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function tgh_require_post()
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        exit('Method not allowed');
    }
}

function tgh_require_login($loginUrl)
{
    if (!tgh_is_logged_in()) {
        tgh_redirect($loginUrl);
    }
}

function tgh_csrf_token()
{
    if (empty($_SESSION['tgh_csrf'])) {
        $_SESSION['tgh_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['tgh_csrf'];
}

function tgh_csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . tgh_e(tgh_csrf_token()) . '">';
}

function tgh_csrf_valid()
{
    $sent = $_POST['csrf_token'] ?? '';
    return is_string($sent)
        && !empty($_SESSION['tgh_csrf'])
        && hash_equals($_SESSION['tgh_csrf'], $sent);
}

function tgh_flash_set($type, $messages)
{
    $_SESSION['tgh_flash'] = ['type' => $type, 'messages' => (array)$messages];
}

function tgh_render_flash()
{
    if (empty($_SESSION['tgh_flash'])) {
        return;
    }
    $flash = $_SESSION['tgh_flash'];
    unset($_SESSION['tgh_flash']);
    $class = $flash['type'] === 'success' ? 'm3-alert m3-alert-success' : 'm3-alert m3-alert-error';
    echo '<div class="' . $class . '" role="alert"><ul>';
    foreach ($flash['messages'] as $message) {
        echo '<li>' . tgh_e($message) . '</li>';
    }
    echo '</ul></div>';
}

function tgh_old_input_set(array $post, array $keys)
{
    $old = [];
    foreach ($keys as $key) {
        if (isset($post[$key]) && is_string($post[$key])) {
            $old[$key] = $post[$key];
        }
    }
    $_SESSION['tgh_old_input'] = $old;
}

function tgh_old_input_take()
{
    $old = $_SESSION['tgh_old_input'] ?? [];
    unset($_SESSION['tgh_old_input']);
    return $old;
}

function tgh_format_price($amount)
{
    return 'GH₵ ' . number_format((float)$amount, 2);
}

function tgh_login_url($prefix)
{
    $buyerLogin = __DIR__ . '/../views/buyer/login.php';
    return $prefix . (is_file($buyerLogin) ? 'buyer/login.php' : 'login.php');
}

function tgh_load_card_component()
{
    $component = __DIR__ . '/../views/components/product_card.php';
    if (is_file($component)) {
        require_once $component;
    }
}

function tgh_product_card(array $product, $root)
{
    if (function_exists('renderProductCard')) {
        renderProductCard($product, $root);
        return;
    }
    $image = isset($product['image_path']) ? $root . $product['image_path'] : '';
    echo '<article class="m3-card"><a class="m3-card-link" href="' . tgh_e($root) . 'index.php?page=product&amp;id=' . (int)$product['product_id'] . '">';
    echo '<img src="' . tgh_e($image) . '" alt="' . tgh_e($product['title']) . '" loading="lazy">';
    echo '<h3>' . tgh_e($product['title']) . '</h3>';
    echo '<p class="m3-price">' . tgh_e(tgh_format_price($product['price'])) . '</p>';
    echo '<p class="m3-muted">Size ' . tgh_e($product['size']) . '</p>';
    echo '</a></article>';
}

function tgh_layout_start($title, $root, $activeNav = '')
{
    $pageTitle = $title;
    $header = __DIR__ . '/../views/layout/header.php';
    if (is_file($header)) {
        include $header;
    } else {
        echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>' . tgh_e($title) . ' | ThriftedGH</title></head><body>';
    }
    echo '<div class="container m3-wrap">';
}

function tgh_layout_end()
{
    echo '</div>';
    $footer = __DIR__ . '/../views/layout/footer.php';
    if (is_file($footer)) {
        include $footer;
    } else {
        echo '</body></html>';
    }
}

const SELLER_IMAGE_MAX_BYTES = 2097152;
const SELLER_IMAGE_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp'
];

function seller_product_image_dir()
{
    return __DIR__ . '/../images/products/';
}

function seller_find_approved_ctr($userId)
{
    return (new ProductClass())->getApprovedSellerByUserId($userId);
}

function seller_form_categories_ctr()
{
    return (new ProductClass())->getCategories();
}

function seller_dashboard_products_ctr($sellerId)
{
    return (new ProductClass())->getProductsBySeller($sellerId);
}

function seller_owned_product_ctr($productId, $sellerId)
{
    return (new ProductClass())->getOwnedProduct($productId, $sellerId);
}

function tgh_input_string(array $source, $key)
{
    return isset($source[$key]) && is_string($source[$key]) ? $source[$key] : '';
}

function tgh_parse_measurement($raw, $min, $max)
{
    $raw = is_string($raw) ? trim($raw) : '';
    if ($raw === '') {
        return ['empty' => true, 'value' => null, 'valid' => true];
    }
    if (!preg_match('/^\d{1,3}(\.\d)?$/', $raw)) {
        return ['empty' => false, 'value' => null, 'valid' => false];
    }
    $number = (float)$raw;
    if ($number < $min || $number > $max) {
        return ['empty' => false, 'value' => null, 'valid' => false];
    }
    return ['empty' => false, 'value' => number_format($number, 1, '.', ''), 'valid' => true];
}

function seller_validate_product_post(array $post, array $categories)
{
    $errors = [];
    $clean = [];
    $measurements = [];

    $title = trim(tgh_input_string($post, 'title'));
    $titleLength = mb_strlen($title);
    if ($titleLength < 3 || $titleLength > 150) {
        $errors[] = 'Title must be between 3 and 150 characters.';
    }
    $clean['title'] = $title;

    $description = trim(tgh_input_string($post, 'description'));
    $descriptionLength = mb_strlen($description);
    if ($descriptionLength < 10 || $descriptionLength > 2000) {
        $errors[] = 'Description must be between 10 and 2000 characters.';
    }
    $clean['description'] = $description;

    $priceRaw = trim(tgh_input_string($post, 'price'));
    if (!preg_match('/^\d{1,6}(\.\d{1,2})?$/', $priceRaw) || (float)$priceRaw <= 0 || (float)$priceRaw > 100000) {
        $errors[] = 'Price must be a number above 0 and up to 100000, with at most 2 decimals.';
        $clean['price'] = '0.00';
    } else {
        $clean['price'] = number_format((float)$priceRaw, 2, '.', '');
    }

    $categoryId = isset($post['category_id']) && is_string($post['category_id']) ? (int)$post['category_id'] : 0;
    $categoryIds = array_map('intval', array_column($categories, 'category_id'));
    if (!in_array($categoryId, $categoryIds, true)) {
        $errors[] = 'Choose a valid category.';
    }
    $clean['category_id'] = $categoryId;

    $size = tgh_input_string($post, 'size');
    if (!in_array($size, ProductClass::SIZES, true)) {
        $errors[] = 'Choose a valid size.';
    }
    $clean['size'] = $size;

    $condition = tgh_input_string($post, 'item_condition');
    if (!array_key_exists($condition, ProductClass::CONDITIONS)) {
        $errors[] = 'Choose a valid condition.';
    }
    $clean['item_condition'] = $condition;

    $garmentType = tgh_input_string($post, 'garment_type');
    if ($garmentType !== '' && !array_key_exists($garmentType, ProductClass::GARMENT_TYPES)) {
        $errors[] = 'Choose a valid garment type.';
        $garmentType = '';
    }

    if ($garmentType !== '') {
        $required = ProductClass::REQUIRED_MEASUREMENTS[$garmentType];
        $optional = ProductClass::OPTIONAL_MEASUREMENTS[$garmentType];
        foreach (array_merge($required, $optional) as $dimension) {
            $min = $dimension === 'inseam' ? 15 : 30;
            $max = $dimension === 'inseam' ? 120 : 250;
            $parsed = tgh_parse_measurement($post[$dimension] ?? '', $min, $max);
            if (!$parsed['valid']) {
                $errors[] = ucfirst($dimension) . ' must be a number between ' . $min . ' and ' . $max . ' cm.';
            } elseif ($parsed['empty'] && in_array($dimension, $required, true)) {
                $errors[] = ucfirst($dimension) . ' measurement is required for this garment type.';
            } else {
                $measurements[$dimension] = $parsed['value'];
            }
        }
    }

    return [
        'errors' => $errors,
        'clean' => $clean,
        'garment_type' => $garmentType,
        'measurements' => $measurements
    ];
}

function seller_validate_product_image($file, $required)
{
    if (!is_array($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $required
            ? ['error' => 'Please upload a photo of the item.']
            : ['error' => null, 'tmp' => null];
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['error' => 'The photo is too large. The limit is 2 MB.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'The photo could not be uploaded. Please try again.'];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['error' => 'The photo could not be verified. Please try again.'];
    }
    if ($file['size'] > SELLER_IMAGE_MAX_BYTES || filesize($file['tmp_name']) > SELLER_IMAGE_MAX_BYTES) {
        return ['error' => 'The photo is too large. The limit is 2 MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset(SELLER_IMAGE_TYPES[$mime])) {
        return ['error' => 'The photo must be a JPG, PNG or WEBP image.'];
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false || $info['mime'] !== $mime || $info[0] < 1 || $info[1] < 1 || $info[0] > 10000 || $info[1] > 10000) {
        return ['error' => 'The photo file looks damaged or is not a real image.'];
    }

    return ['error' => null, 'tmp' => $file['tmp_name'], 'ext' => SELLER_IMAGE_TYPES[$mime]];
}

function seller_store_product_image(array $validated)
{
    $name = bin2hex(random_bytes(16)) . '.' . $validated['ext'];
    $target = seller_product_image_dir() . $name;
    if (!move_uploaded_file($validated['tmp'], $target)) {
        return null;
    }
    chmod($target, 0644);
    return 'images/products/' . $name;
}

function seller_delete_product_image($stored)
{
    if (is_string($stored) && preg_match('#^(?:images/products/)?([a-f0-9]{32}\.(?:jpg|png|webp))$#', $stored, $match)) {
        $path = seller_product_image_dir() . $match[1];
        if (is_file($path)) {
            unlink($path);
        }
    }
}

function seller_add_product_ctr($userId, array $post, $file)
{
    $seller = seller_find_approved_ctr($userId);
    if (!$seller) {
        return ['ok' => false, 'errors' => ['Your seller account is not approved, so you cannot publish products.']];
    }

    $checked = seller_validate_product_post($post, seller_form_categories_ctr());
    $errors = $checked['errors'];
    $image = seller_validate_product_image($file, true);
    if ($image['error']) {
        $errors[] = $image['error'];
    }
    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $stored = seller_store_product_image($image);
    if ($stored === null) {
        return ['ok' => false, 'errors' => ['The photo could not be saved. Please try again.']];
    }

    $result = (new ProductClass())->addProduct(
        $seller['seller_id'],
        $checked['clean'],
        $checked['garment_type'],
        $checked['measurements'],
        $stored
    );

    if (!$result['ok']) {
        seller_delete_product_image($stored);
        $message = $result['error'] === 'not_approved'
            ? 'Your seller account is not approved, so you cannot publish products.'
            : 'Something went wrong while saving the product. Please try again.';
        return ['ok' => false, 'errors' => [$message]];
    }

    return ['ok' => true, 'id' => $result['id']];
}

function seller_update_product_ctr($userId, $productId, array $post, $file)
{
    $seller = seller_find_approved_ctr($userId);
    if (!$seller) {
        return ['ok' => false, 'errors' => ['Your seller account is not approved, so you cannot edit products.']];
    }

    $existing = seller_owned_product_ctr($productId, $seller['seller_id']);
    if (!$existing || $existing['status'] !== 'available') {
        return ['ok' => false, 'errors' => ['That listing cannot be edited.']];
    }

    $checked = seller_validate_product_post($post, seller_form_categories_ctr());
    $errors = $checked['errors'];
    $image = seller_validate_product_image($file, false);
    if ($image['error']) {
        $errors[] = $image['error'];
    }
    if ($errors) {
        return ['ok' => false, 'errors' => $errors];
    }

    $stored = null;
    if (!empty($image['tmp'])) {
        $stored = seller_store_product_image($image);
        if ($stored === null) {
            return ['ok' => false, 'errors' => ['The photo could not be saved. Please try again.']];
        }
    }

    $result = (new ProductClass())->updateProduct(
        $productId,
        $seller['seller_id'],
        $checked['clean'],
        $checked['garment_type'],
        $checked['measurements'],
        $stored
    );

    if (!$result['ok']) {
        if ($stored !== null) {
            seller_delete_product_image($stored);
        }
        $message = $result['error'] === 'not_editable'
            ? 'That listing cannot be edited.'
            : 'Something went wrong while saving the product. Please try again.';
        return ['ok' => false, 'errors' => [$message]];
    }

    if (!empty($result['old_image'])) {
        seller_delete_product_image($result['old_image']);
    }
    return ['ok' => true];
}

function seller_remove_product_ctr($userId, $productId)
{
    $seller = seller_find_approved_ctr($userId);
    if (!$seller) {
        return ['ok' => false, 'errors' => ['Your seller account is not approved.']];
    }
    $result = (new ProductClass())->removeProduct($productId, $seller['seller_id']);
    if (!$result['ok']) {
        return ['ok' => false, 'errors' => ['That listing cannot be removed. Only your own available listings can be removed.']];
    }
    return ['ok' => true];
}

const STOREFRONT_PAGE_SIZE = 24;

function storefront_page_ctr($sellerId, $page)
{
    if (!is_scalar($sellerId) || !preg_match('/^\d{1,10}$/', (string)$sellerId) || (int)$sellerId < 1) {
        return null;
    }
    $sellerId = (int)$sellerId;
    if (is_array($page)) {
        $page = 1;
    }

    $seller = (new ProductClass())->getApprovedSellerById($sellerId);
    if (!$seller) {
        return null;
    }

    $products = new ProductClass();
    $total = $products->countAvailableBySeller($sellerId);
    $pages = max(1, (int)ceil($total / STOREFRONT_PAGE_SIZE));
    $page = min(max(1, (int)$page), $pages);

    return [
        'seller' => $seller,
        'products' => $products->getAvailableBySeller($sellerId, STOREFRONT_PAGE_SIZE, ($page - 1) * STOREFRONT_PAGE_SIZE),
        'total' => $total,
        'page' => $page,
        'pages' => $pages
    ];
}
