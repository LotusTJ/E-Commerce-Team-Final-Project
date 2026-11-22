<?php
require_once __DIR__ . '/../../core/guard.php';

$user = guardRequireAdmin();
$scope = 'admin';
require __DIR__ . '/../components/orders_page.php';
