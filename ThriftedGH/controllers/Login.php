<?php
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../classes/UserClass.php';

function login_client_ip()
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

function login_attempt_ctr($portal, $email, $password)
{
    $roles = ['buyer' => 'buyer', 'seller' => 'seller', 'admin' => 'admin'];
    $expected = $roles[$portal] ?? 'buyer';
    $email = trim((string)$email);
    $mismatch = 'That email and password do not match an account.';

    if ($email === '' || (string)$password === '') {
        return ['ok' => false, 'error' => 'Enter your email and password.'];
    }

    $users = new UserClass();
    $ip = login_client_ip();

    if ($users->isLockedOut($email, $ip)) {
        return ['ok' => false, 'error' => 'Too many failed attempts. Please wait ' . UserClass::LOCK_MINUTES . ' minutes and try again.'];
    }

    $user = $users->loginUser($email);
    if (!$user || !password_verify((string)$password, $user['password_hash'])) {
        $users->recordFailure($email, $ip);
        return ['ok' => false, 'error' => $mismatch];
    }

    if ($user['role'] !== $expected) {
        if ($portal === 'admin') {
            $users->recordFailure($email, $ip);
            return ['ok' => false, 'error' => $mismatch];
        }
        if ($user['role'] === 'admin') {
            return ['ok' => false, 'error' => $mismatch];
        }
        if ($portal === 'buyer') {
            return ['ok' => false, 'error' => 'This is a seller account. Use the seller login instead.'];
        }
        return ['ok' => false, 'error' => 'This account is not an approved seller yet. Use the buyer login, or apply to sell from your account page.'];
    }

    $users->clearFailures($email);
    return ['ok' => true, 'user' => $user];
}

function login_start_session(array $user)
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
}

function login_handle($portal)
{
    $page = tgh_login_url($portal);
    guardRequirePost();
    guardRequireCsrf($page);

    $result = login_attempt_ctr($portal, tgh_input_string($_POST, 'email'), tgh_input_string($_POST, 'password'));
    if (!$result['ok']) {
        tgh_flash_set('error', $result['error']);
        tgh_redirect($page);
    }

    login_start_session($result['user']);
    tgh_redirect(tgh_role_home($result['user']['role']));
}
