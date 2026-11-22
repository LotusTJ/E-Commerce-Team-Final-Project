<?php
if (getenv('DB_NAME')!=='thriftedgh_member4_test') exit("Set DB_NAME=thriftedgh_member4_test for the isolated tests.\n");
require_once __DIR__.'/../classes/OrderClass.php';
require_once __DIR__.'/../classes/CartClass.php';
require_once __DIR__.'/../payments/PaystackService.php';
$db=Database::getInstance();
if(DB_NAME!=='thriftedgh_member4_test' || $db->query('SELECT DATABASE() n')->fetch_assoc()['n']!=='thriftedgh_member4_test') exit("Refusing to modify a non-test database.\n");
function sql($s) {global $db; return $db->query($s);}
function one($s) {return sql($s)->fetch_assoc();}
function check($value,$label) {if(!$value) throw new RuntimeException('FAIL: '.$label); echo "PASS: $label\n";}
function denied(callable $f,$label) {try {$f();} catch(DomainException $e) {check(true,$label);return;} throw new RuntimeException('FAIL: '.$label);}
function payment($order) {return ['reference'=>$order['order_reference'],'status'=>'success','amount'=>CommerceModel::cents($order['total_amount']),'currency'=>'GHS','domain'=>'test'];}
sql('SET FOREIGN_KEY_CHECKS=0');
foreach(['seller_earnings','payments','order_items','orders','cart_items','carts','product_measurements','product_garment_types','products','categories','seller_profiles','seller_applications','users'] as $t) sql('TRUNCATE TABLE '.$t);
sql('SET FOREIGN_KEY_CHECKS=1');
sql("INSERT INTO users (user_id,full_name,email,password_hash,role) VALUES (1,'Buyer','buyer@test.invalid','x','buyer'),(2,'Other buyer','other@test.invalid','x','buyer'),(3,'Seller A','a@test.invalid','x','seller'),(4,'Seller B','b@test.invalid','x','seller'),(5,'Admin','admin@test.invalid','x','admin')");
sql("INSERT INTO seller_applications(user_id,seller_type,shop_name,status) VALUES(3,'individual','Shop A','approved'),(4,'business','Shop B','approved')");
sql("INSERT INTO seller_profiles(seller_id,user_id,shop_name,seller_type) VALUES(1,3,'Shop A','individual'),(2,4,'Shop B','business')");
sql("INSERT INTO categories VALUES(1,'Tops')");
for($i=1;$i<=9;$i++) sql("INSERT INTO products(product_id,seller_id,category_id,title,price,item_condition) VALUES($i,".($i===2?2:1).",1,'Test $i',".($i===2?'120.00':'85.00').",'good')");
$cart=new CartClass();$orders=new OrderClass();
$cart->add(1,1);$cart->add(1,1);$cart->add(1,2);
check(count($cart->items(1))===2,'duplicate add keeps unique quantity one');
check(count($cart->items(2))===0,'carts separated by buyer');
$cart->remove(2,1);check(count($cart->items(1))===2,'other buyer cannot remove cart item');
$o=$orders->create(1,'Accra, test delivery address');
check($o['total_amount']==='211.15','mixed seller total calculated by server including 3 percent buyer fee');
check((int)one("SELECT COUNT(*) n FROM products WHERE status='reserved'")['n']===2,'checkout reserves all items');
denied(fn()=>$orders->create(1,'Accra, test address'),'duplicate checkout blocked');
denied(fn()=>$cart->add(2,1),'reserved inventory cannot be added');
$bad=payment($o);$bad['amount']=1;denied(fn()=>$orders->settle($o['order_reference'],$bad),'amount mismatch rejected');
$bad=payment($o);$bad['currency']='NGN';denied(fn()=>$orders->settle($o['order_reference'],$bad),'currency mismatch rejected');
$bad=payment($o);$bad['reference']='wrong';denied(fn()=>$orders->settle($o['order_reference'],$bad),'reference mismatch rejected');
$bad=payment($o);$bad['status']='failed';denied(fn()=>$orders->settle($o['order_reference'],$bad),'failed payment cannot settle');
$bad=payment($o);$bad['domain']='live';denied(fn()=>$orders->settle($o['order_reference'],$bad),'live payment rejected in test mode');
check(one('SELECT COUNT(*) n FROM seller_earnings')['n']==0,'unverified payment creates no earnings');
$paid=$orders->settle($o['order_reference'],payment($o));
$orders->settle($o['order_reference'],payment($o));
check($paid['payment_status']==='paid','verified payment marks order paid');
check(one('SELECT COUNT(*) n FROM seller_earnings')['n']==2,'duplicate callback and webhook create earnings once');
check(one('SELECT SUM(commission_amount) c FROM seller_earnings')['c']==='14.35','seller commission reconciles to 7 percent of item prices');
check(one('SELECT SUM(seller_amount) n FROM seller_earnings')['n']==='190.65','seller net reconciles');
check(one("SELECT COUNT(*) n FROM products WHERE status='sold' AND stock_quantity=0")['n']==2,'paid products sold and stock zero');
check(count($cart->items(1))===0,'purchased cart items removed');
$buyer=['user_id'=>1,'role'=>'buyer'];$other=['user_id'=>2,'role'=>'buyer'];$sellerA=['user_id'=>3,'role'=>'seller'];$admin=['user_id'=>5,'role'=>'admin'];
check($orders->detail($other,$o['order_id'],'buyer')===null,'other buyer order access denied');
check($orders->detail($other,$o['order_id'],'admin')===null,'forged admin scope denied');
$d=$orders->detail($sellerA,$o['order_id'],'seller');check(count($d['items'])===1 && !isset($d['total_amount']),'seller sees only own items and no other seller total');
check(count($orders->detail($admin,$o['order_id'],'admin')['items'])===2,'admin can see entire order');
$item=$d['items'][0]['order_item_id'];
denied(fn()=>$orders->fulfil(4,$item,'processing'),'other seller fulfilment denied');
denied(fn()=>$orders->fulfil(3,$item,'delivered'),'skipping fulfilment stages denied');
foreach(['processing','shipped','delivered'] as $s) $orders->fulfil(3,$item,$s);
check(one("SELECT fulfilment_status s FROM order_items WHERE order_item_id=$item")['s']==='delivered','seller completes authorised fulfilment');
$completed=array_column($orders->reconciliation(),'completed_gross','shop_name');
check($completed['Shop A']==='85.00','admin distinguishes delivered sales from other verified sales');
$cart->add(1,3);$failed=$orders->create(1,'Accra, test delivery address');$orders->fail($failed['order_reference']);
check(one('SELECT status FROM products WHERE product_id=3')['status']==='available','failed payment releases reservation');
check($orders->detail($sellerA,$failed['order_id'],'seller')===null,'unpaid order hidden from seller');
$cart->remove(1,3);$cart->add(1,4);$expired=$orders->create(1,'Accra, test delivery address');
sql("UPDATE orders SET reservation_expires_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 MINUTE) WHERE order_id=".$expired['order_id']);$orders->expire();
check(one('SELECT status FROM products WHERE product_id=4')['status']==='available','expired reservation released');
$cart->add(2,4);$new=$orders->create(2,'Kumasi, test delivery address');
$late=$orders->settle($expired['order_reference'],payment($expired));
check((int)$late['payment_review']===1 && $late['payment_status']==='failed','late paid expired order requires review');
check((int)one('SELECT reservation_order_id id FROM products WHERE product_id=4')['id']===(int)$new['order_id'],'late charge cannot steal new reservation');
check(one('SELECT COUNT(*) n FROM seller_earnings')['n']==2,'late payment creates no duplicate sale or earnings');
$orders->fail($new['order_reference']);$cart->remove(2,4);$cart->remove(1,4);

$cart->add(1,5);$cart->add(1,6);sql("UPDATE products SET status='hidden' WHERE product_id=6");
denied(fn()=>$orders->create(1,'Accra, test address'),'checkout revalidates availability');
check(one('SELECT status FROM products WHERE product_id=5')['status']==='available','failed checkout rolls back whole reservation');
$cart->remove(1,5);$cart->remove(1,6);
sql("UPDATE seller_applications SET status='rejected' WHERE user_id=3");
denied(fn()=>$cart->add(1,7),'unapproved seller product blocked');
sql("UPDATE seller_applications SET status='approved' WHERE user_id=3");
check(CommerceModel::money(CommerceModel::cents('0.01'))==='0.01','money preserves smallest currency unit');
$body='{"event":"charge.success"}';$secret='sk_test_fixture';$signing='whsec_fixture';$now=time();
check(PaystackService::validSignature($body,'',hash_hmac('sha512',$body,$secret),$secret,''),'legacy webhook signature verified');
check(!PaystackService::validSignature($body.'x','',hash_hmac('sha512',$body,$secret),$secret,''),'tampered webhook rejected');
$sig='t='.$now.',v1=whk_test:'.hash_hmac('sha512',$now.'.'.$body,$signing);
check(PaystackService::validSignature($body,$sig,'',$secret,$signing,$now),'timestamped webhook signature verified');
check(!PaystackService::validSignature($body,$sig,'',$secret,$signing,$now+301),'old timestamped webhook rejected');
check(!PaystackService::validSignature($body,'',hash_hmac('sha512',$body,$secret),$secret,$signing),'configured modern webhook cannot downgrade');

class FakePaystack extends PaystackService {
    public $path; public $payload; public $response=['authorization_url'=>'https://checkout.paystack.com/test-fixture'];
    protected function request($path,$payload=null) {$this->path=$path;$this->payload=$payload;return $this->response;}
}
putenv('PAYSTACK_SECRET_KEY=sk_test_fixture');putenv('APP_URL=http://localhost/ThriftedGH');
$provider=new FakePaystack();
check($provider->initialize($o)==='https://checkout.paystack.com/test-fixture','provider initialization returns trusted checkout address');
check($provider->payload['amount']===21115 && $provider->payload['currency']==='GHS','provider receives server amount in pesewas and correct currency');
check($provider->payload['reference']===$o['order_reference'],'provider receives generated unique order reference');
check($provider->payload['callback_url']==='http://localhost/ThriftedGH/payments/callback.php','provider receives configured callback URL');
$provider->response=['authorization_url'=>'https://untrusted.invalid/payment'];
try {$provider->initialize($o);throw new LogicException('Untrusted payment URL accepted');} catch(RuntimeException $e) {check(true,'untrusted provider redirect rejected');}
putenv('PAYSTACK_SECRET_KEY=sk_live_fixture');
try {new PaystackService();throw new LogicException('Live key accepted');} catch(RuntimeException $e) {check(true,'live secret key rejected');}
putenv('PAYSTACK_SECRET_KEY');

$cart->add(1,8);$cart->add(2,8);
$worker=__DIR__.'/checkout_worker.php';$pipes=[];$processes=[];
for($i=1;$i<=2;$i++) {$p=[];$processes[]=proc_open([PHP_BINARY,$worker,(string)$i],[1=>['pipe','w'],2=>['pipe','w']],$p);$pipes[]=$p;}
$out=[];foreach($processes as $k=>$process) {$out[]=trim(stream_get_contents($pipes[$k][1]));fclose($pipes[$k][1]);$err=stream_get_contents($pipes[$k][2]);fclose($pipes[$k][2]);proc_close($process);if($err) echo $err;}
sort($out);check($out===['blocked','reserved'],'simultaneous buyers cannot reserve same item');
echo "All commerce checks passed.\n";
