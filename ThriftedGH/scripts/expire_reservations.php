<?php
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
require_once __DIR__.'/../classes/OrderClass.php';
(new OrderClass())->expire();
echo "Expired reservations released.\n";
