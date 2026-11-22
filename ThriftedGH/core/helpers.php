<?php
require_once __DIR__.'/session.php';
require_once __DIR__.'/paths.php';
require_once __DIR__.'/icons.php';

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

function tgh_login_url()
{
    return tgh_url('views/auth/login.php');
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
    echo '<article class="m3-card"><a class="m3-card-link" href="' . tgh_e(tgh_url('views/public/product.php')) . '?id=' . (int)$product['product_id'] . '">';
    echo '<img src="' . tgh_e($image) . '" alt="' . tgh_e($product['title']) . '" loading="lazy">';
    echo '<h3>' . tgh_e($product['title']) . '</h3>';
    echo '<p class="m3-price">' . tgh_e(tgh_format_price($product['price'])) . '</p>';
    echo '<p class="m3-muted">Size ' . tgh_e($product['size']) . '</p>';
    echo '</a></article>';
}

function tgh_layout_start($title, $activeNav = '')
{
    $pageTitle = $title . ' | ThriftedGH';
    include __DIR__ . '/../views/layout/header.php';
    echo '<div class="container m3-wrap">';
}

function tgh_layout_end()
{
    echo '</div>';
    include __DIR__ . '/../views/layout/footer.php';
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
