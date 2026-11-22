<?php
require_once __DIR__.'/db_config.php';
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}
if (session_status()===PHP_SESSION_NONE) {
    $instance='ThriftedGH|'.DB_HOST.'|'.DB_PORT.'|'.DB_SOCKET.'|'.DB_NAME;
    session_name('tgh_'.substr(hash('sha256',$instance),0,16));
    ini_set('session.use_strict_mode','1');
    session_set_cookie_params([
        'lifetime'=>0,
        'path'=>'/',
        'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off',
        'httponly'=>true,
        'samesite'=>'Lax',
    ]);
    session_start();
}
