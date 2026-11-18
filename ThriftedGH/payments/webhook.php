<?php
require_once __DIR__.'/../classes/OrderClass.php';
require_once __DIR__.'/PaystackService.php';
if (($_SERVER['REQUEST_METHOD']??'')!=='POST') { http_response_code(405); exit; }
$raw=file_get_contents('php://input');
if (strlen($raw)>1048576) { http_response_code(413); exit; }
$secret=PaystackService::config('PAYSTACK_SECRET_KEY');
if (strpos($secret,'sk_test_')!==0 || !PaystackService::validSignature($raw,$_SERVER['HTTP_PAYSTACK_SIGNATURE']??'',$_SERVER['HTTP_X_PAYSTACK_SIGNATURE']??'',$secret,PaystackService::config('PAYSTACK_WEBHOOK_SECRET'))) { http_response_code(401); exit; }
try {
    $event=json_decode($raw,true,512,JSON_THROW_ON_ERROR);
    if (($event['event']??'')==='charge.success') {
        $ref=$event['data']['reference']??null;
        if (!is_string($ref) || !preg_match('/^TGH-[a-f0-9]{32}$/D',$ref)) { http_response_code(200); exit; }
        $model=new OrderClass();
        if ($model->byReference($ref)) $model->settle($ref,(new PaystackService())->verify($ref));
    }
    http_response_code(200);
} catch (Throwable $e) { error_log('Paystack webhook: '.$e->getMessage()); http_response_code(500); }
