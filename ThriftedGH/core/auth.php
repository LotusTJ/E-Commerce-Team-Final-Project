<?php
require_once __DIR__ . '/core.php';
require_once __DIR__ . '/db_class.php';

function authCurrentUser()
{
    static $cache = [];
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($userId < 1) {
        return null;
    }
    if (!array_key_exists($userId, $cache)) {
        $stmt = Database::getInstance()->prepare('SELECT user_id, full_name, email, phone, role FROM users WHERE user_id = ?');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $cache[$userId] = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
    }
    return $cache[$userId];
}

function authIsAdmin()
{
    $user = authCurrentUser();
    return $user !== null && $user['role'] === 'admin';
}

function authRequireAdmin()
{
    checkLogin();
    if (!authIsAdmin()) {
        http_response_code(403);
        exit('Access denied');
    }
}
