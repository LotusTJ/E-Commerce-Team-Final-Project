<?php
require_once __DIR__.'/../../core/guard.php';

$user = guardRequireLogin();
$scope = 'buyer';
require __DIR__.'/../components/orders_page.php';
