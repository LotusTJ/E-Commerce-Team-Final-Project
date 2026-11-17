<?php
require_once __DIR__.'/Product.php';
require_once __DIR__.'/../core/auth.php';
require_once __DIR__.'/../classes/CartClass.php';
function commerce_user() {
    $user=authCurrentUser();
    if (!$user) {
        $script=$_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $root=preg_replace('~/(?:views|actions|payments)/.*$~','',$script);
        if ($root===$script) $root=dirname($script);
        tgh_redirect(rtrim($root,'/').'/index.php?page=login');
    }
    return $user;
}
function commerce_post() {
    tgh_require_post();
    if (!tgh_csrf_valid()) { http_response_code(403); exit('Your form expired. Reload the page and try again.'); }
}
function commerce_id($value) {
    if (!is_scalar($value) || !preg_match('/^[1-9][0-9]{0,9}$/D',(string)$value) || (int)$value>2147483647) throw new DomainException('Invalid item.');
    return (int)$value;
}
function commerce_error(Throwable $e) {
    error_log('Commerce: '.$e->getMessage());
    tgh_flash_set('error',$e instanceof DomainException ? $e->getMessage() : 'We could not complete that request. Please try again.');
}
