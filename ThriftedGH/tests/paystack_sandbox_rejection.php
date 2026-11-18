<?php
// Optional real-provider contract test. It submits an invalid email, never a valid charge.
if (PHP_SAPI!=='cli' || getenv('RUN_PAYSTACK_SANDBOX_TESTS')!=='yes') {
    exit("Set RUN_PAYSTACK_SANDBOX_TESTS=yes only after configuring your private Paystack test key.\n");
}
require_once __DIR__.'/../classes/CommerceModel.php';
require_once __DIR__.'/../payments/PaystackService.php';
$service=new PaystackService();
try {
    $service->initialize([
        'email'=>'not-an-email',
        'total_amount'=>'1.00',
        'order_reference'=>'TGH-'.bin2hex(random_bytes(16)),
    ]);
    throw new LogicException('Expected Paystack to reject the invalid email.');
} catch (PaystackRequestException $e) {
    if (!$e->definitelyRejected) throw new RuntimeException('Expected a definitive rejection, not an ambiguous network failure.');
    echo "PASS: real Paystack test API validation rejection is classified as safe to release.\n";
}
