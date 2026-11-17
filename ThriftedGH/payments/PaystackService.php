<?php
class PaystackService {
    private $key;
    public static function config($name) {
        static $local=null;
        if ($local===null) {
            $path=__DIR__.'/../core/payment.local.php';
            $local=is_file($path) ? require $path : [];
            if (!is_array($local)) throw new RuntimeException('Invalid private payment configuration.');
        }
        $env=getenv($name);
        return $env!==false && $env!=='' ? $env : ($local[$name] ?? '');
    }
    public function __construct() {
        $this->key=self::config('PAYSTACK_SECRET_KEY');
        if (strpos($this->key,'sk_test_')!==0) throw new RuntimeException('Paystack test mode has not been configured.');
    }
    protected function request($path, $payload=null) {
        $ch=curl_init('https://api.paystack.co'.$path);
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_TIMEOUT=>30,
            CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$this->key,'Content-Type: application/json']]);
        if ($payload!==null) curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($payload,JSON_THROW_ON_ERROR)]);
        $raw=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
        if ($raw===false || $status<200 || $status>=300) throw new RuntimeException('Payment provider unavailable. Check your order before trying again.');
        $result=json_decode($raw,true,512,JSON_THROW_ON_ERROR);
        if (($result['status']??false)!==true || !is_array($result['data']??null)) throw new RuntimeException('Payment provider could not process this request.');
        return $result['data'];
    }
    public function callbackUrl() {
        $base=rtrim(self::config('APP_URL'), '/');
        if (!filter_var($base,FILTER_VALIDATE_URL) || !in_array(parse_url($base,PHP_URL_SCHEME),['http','https'],true)) throw new RuntimeException('Configure APP_URL before checkout.');
        return $base.'/payments/callback.php';
    }
    public function initialize(array $order) {
        $data=$this->request('/transaction/initialize',['email'=>$order['email'],'amount'=>CommerceModel::cents($order['total_amount']),
            'currency'=>'GHS','reference'=>$order['order_reference'],'callback_url'=>$this->callbackUrl()]);
        $url=$data['authorization_url']??'';
        if (parse_url($url,PHP_URL_SCHEME)!=='https' || parse_url($url,PHP_URL_HOST)!=='checkout.paystack.com') throw new RuntimeException('Unexpected checkout address.');
        return $url;
    }
    public function verify($ref) { return $this->request('/transaction/verify/'.rawurlencode($ref)); }
    public static function validSignature($body,$modern,$legacy,$secret,$signingSecret,$now=null) {
        if ($signingSecret!=='') {
            // When configured, require the timestamped signature; never downgrade.
            $timestamp=null; $signatures=[];
            foreach (explode(',',$modern) as $part) {
                $kv=explode('=',trim($part),2);
                if (count($kv)!==2) continue;
                if ($kv[0]==='t' && ctype_digit($kv[1])) $timestamp=(int)$kv[1];
                if ($kv[0]==='v1') { $s=explode(':',$kv[1],2); if(count($s)===2) $signatures[]=$s[1]; }
            }
            if ($timestamp===null || abs(($now??time())-$timestamp)>300) return false;
            $expected=hash_hmac('sha512',$timestamp.'.'.$body,$signingSecret);
            foreach ($signatures as $signature) if (hash_equals($expected,$signature)) return true;
            return false;
        }
        return strpos($secret,'sk_test_')===0 && $legacy!=='' && hash_equals(hash_hmac('sha512',$body,$secret),$legacy);
    }
}
