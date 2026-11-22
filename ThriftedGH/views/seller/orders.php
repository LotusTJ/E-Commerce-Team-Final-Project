<?php
require_once __DIR__ . '/../../core/guard.php';

$user = guardRequireSeller();
$scope = 'seller';
require __DIR__ . '/../components/orders_page.php';
