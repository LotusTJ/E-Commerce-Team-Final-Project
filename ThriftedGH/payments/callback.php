<?php
require_once __DIR__.'/../controllers/Order.php';
try {
    // The callback query is never proof of payment; query Paystack from the server.
    commerce_verify($_GET['reference']??null);
    tgh_flash_set('success','Payment checked. Your order shows the verified result.');
} catch (Throwable $e) { commerce_error($e); }
if (isset($_GET['reference']) && is_string($_GET['reference']) && preg_match('/^TGH-[a-f0-9]{32}$/D',$_GET['reference'])) {
    tgh_redirect('../index.php?page=payment_result&reference='.rawurlencode($_GET['reference']));
}
tgh_redirect('../index.php?page=orders');
