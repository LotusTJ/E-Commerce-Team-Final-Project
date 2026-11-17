<?php
if(getenv('DB_NAME')!=='thriftedgh_member4_test') exit(1);
require_once __DIR__.'/../classes/OrderClass.php';
if(DB_NAME!=='thriftedgh_member4_test') exit(1);
try {(new OrderClass())->create((int)$argv[1],'Concurrent test delivery address');echo 'reserved';}
catch(Throwable $e) {echo 'blocked';}
