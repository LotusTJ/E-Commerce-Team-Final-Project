<?php
require_once __DIR__ . '/paths.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../classes/ProductClass.php';

function guardIsPageRequest()
{
    return strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/actions/') === false;
}

function guardDeny($code, $message)
{
    http_response_code($code);
    if (!guardIsPageRequest()) {
        header('Content-Type: text/plain; charset=UTF-8');
        exit($message);
    }
    $pageTitle = 'Access denied | ThriftedGH';
    $activeNav = '';
    require __DIR__ . '/../views/layout/header.php';
    echo '<section class="section"><div class="container"><div class="empty-state-card"><h1>' . ($code === 401 ? 'Please log in' : 'Access denied') . '</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p><a class="btn btn-primary" href="' . htmlspecialchars(tgh_url('views/public/home.php'), ENT_QUOTES, 'UTF-8') . '">Back to home</a></div></div></section>';
    require __DIR__ . '/../views/layout/footer.php';
    exit;
}

function guardRequireLogin()
{
    $user = authCurrentUser();
    if ($user === null) {
        if (!empty($_SESSION['user_id'])) {
            session_unset();
        }
        if (!guardIsPageRequest()) {
            guardDeny(401, 'Please log in first.');
        }
        header('Location: ' . tgh_url('views/auth/login.php'));
        exit;
    }
    return $user;
}

function guardRequireRole($role)
{
    $user = guardRequireLogin();
    if ($user['role'] !== $role) {
        guardDeny(403, 'This area is only for ' . ($role === 'admin' ? 'administrators' : 'approved ' . $role . 's') . '.');
    }
    return $user;
}

function guardRequireAdmin()
{
    return guardRequireRole('admin');
}

function guardRequireSeller()
{
    $user = guardRequireLogin();
    if ($user['role'] === 'admin') {
        guardDeny(403, 'Administrator accounts cannot use seller tools.');
    }
    $seller = (new ProductClass())->getApprovedSellerByUserId((int)$user['user_id']);
    if (!$seller) {
        guardDeny(403, 'You need an approved seller account to use this page. Apply from your account page.');
    }
    $user['seller'] = $seller;
    return $user;
}

function guardRequirePost()
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        guardDeny(405, 'This address only accepts form submissions.');
    }
}

function guardRequireCsrf($backUrl)
{
    if (!tgh_csrf_valid()) {
        tgh_flash_set('error', 'Your session expired. Please try again.');
        tgh_redirect($backUrl);
    }
}
