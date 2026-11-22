<?php
if (PHP_SAPI!=='cli') {http_response_code(404);exit;}
$once=in_array('--once',$argv,true);
$stop=false;
if (function_exists('pcntl_async_signals')) {
    pcntl_async_signals(true);
    pcntl_signal(SIGTERM,function() use (&$stop) {$stop=true;});
    pcntl_signal(SIGINT,function() use (&$stop) {$stop=true;});
}
do {
    try { require __DIR__.'/reconcile_payments.php'; }
    catch (Throwable $e) {
        error_log('Payment worker: '.$e->getMessage());
        if($once) exit(1);
    }
    if($once || $stop) break;
    sleep(60);
} while(!$stop);
