<?php
require_once __DIR__ . '/CommerceModel.php';
class OrderClass extends CommerceModel {
    public function create($userId, $address) {
        if (!is_string($address) || strlen(trim($address)) < 10 || strlen($address)>2000) throw new DomainException('Enter a delivery address between 10 and 2,000 characters.');
        return $this->transaction(function () use ($userId,$address) {
            // Serialise duplicate checkout submissions for this account.
            $this->fetchOne('SELECT user_id FROM users WHERE user_id=? FOR UPDATE','i',[$userId]);
            $pending=$this->fetchOne("SELECT order_reference FROM orders WHERE buyer_id=? AND payment_status='pending' AND reservation_expires_at>UTC_TIMESTAMP() LIMIT 1",'i',[$userId]);
            if ($pending) throw new DomainException('You already have a pending checkout. View your orders to check its payment status.');
            $items=$this->fetchAll("SELECT p.*, sp.user_id AS seller_user_id FROM carts c
                JOIN cart_items ci ON ci.cart_id=c.cart_id JOIN products p ON p.product_id=ci.product_id
                JOIN seller_profiles sp ON sp.seller_id=p.seller_id
                WHERE c.user_id=? ORDER BY p.product_id FOR UPDATE",'i',[$userId]);
            if (!$items) throw new DomainException('Your cart is empty.');
            $total=0;
            foreach ($items as $p) {
                $approved=$this->fetchOne("SELECT application_id FROM seller_applications WHERE user_id=? AND status='approved' LIMIT 1",'i',[$p['seller_user_id']]);
                if (!$approved || $p['status']!=='available' || (int)$p['stock_quantity']!==1) throw new DomainException('An item is unavailable. Remove it from your cart and try again.');
                $total+=self::cents($p['price']);
            }
            if ($total<1 || $total>9999999999) throw new DomainException('The order total must be greater than zero and within the payment limit.');
            $ref='TGH-'.bin2hex(random_bytes(16));
            $id=$this->write("INSERT INTO orders (buyer_id,order_reference,total_amount,delivery_address,reservation_expires_at) VALUES (?,?,?,?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 15 MINUTE))",'isss',[$userId,$ref,self::money($total),trim($address)])['id'];
            foreach ($items as $p) {
                $commission=intdiv(self::cents($p['price'])+5,10);
                $this->write('INSERT INTO order_items (order_id,product_id,seller_id,price_at_purchase,commission_amount,title_at_purchase) VALUES (?,?,?,?,?,?)','iiisss',[$id,$p['product_id'],$p['seller_id'],$p['price'],self::money($commission),$p['title']]);
                $this->write("UPDATE products SET status='reserved',reservation_order_id=? WHERE product_id=?",'ii',[$id,$p['product_id']]);
            }
            $this->write('INSERT INTO payments (order_id,provider_reference,amount) VALUES (?,?,?)','iss',[$id,$ref,self::money($total)]);
            return $this->byReference($ref);
        });
    }
    public function byReference($ref) {
        return $this->fetchOne('SELECT o.*,u.email FROM orders o JOIN users u ON u.user_id=o.buyer_id WHERE order_reference=?','s',[$ref]);
    }
    public function pendingReferences() {
        return $this->fetchAll("SELECT order_reference FROM orders WHERE payment_status='pending' ORDER BY order_id LIMIT 200");
    }
    public function expire() {
        $rows=$this->fetchAll("SELECT order_reference FROM orders WHERE payment_status='pending' AND reservation_expires_at<=UTC_TIMESTAMP() ORDER BY order_id");
        foreach ($rows as $row) $this->fail($row['order_reference'], true);
    }
    public function fail($ref, $expiredOnly=false) {
        $this->transaction(function () use ($ref,$expiredOnly) {
            $o=$this->fetchOne('SELECT *, reservation_expires_at<=UTC_TIMESTAMP() AS expired FROM orders WHERE order_reference=? FOR UPDATE','s',[$ref]);
            if (!$o || $o['payment_status']!=='pending' || ($expiredOnly && !$o['expired'])) return;
            $this->write("UPDATE products SET status='available',reservation_order_id=NULL WHERE reservation_order_id=? AND status='reserved'",'i',[$o['order_id']]);
            $this->write("UPDATE orders SET payment_status='failed' WHERE order_id=?",'i',[$o['order_id']]);
            $this->write("UPDATE payments SET status='failed' WHERE order_id=? AND status='pending'",'i',[$o['order_id']]);
        });
    }
    public function settle($ref, array $data) {
        return $this->transaction(function () use ($ref,$data) {
            $o=$this->fetchOne('SELECT * FROM orders WHERE order_reference=? FOR UPDATE','s',[$ref]);
            if (!$o) throw new DomainException('Unknown payment reference.');
            if (($data['reference']??null)!==$ref || ($data['status']??null)!=='success'
                || ($data['currency']??null)!==$o['currency'] || !is_int($data['amount']??null)
                || $data['amount']!==self::cents($o['total_amount']) || ($data['domain']??null)!=='test') {
                throw new DomainException('Payment verification did not match this order.');
            }
            if ($o['payment_status']==='paid' || (int)$o['payment_review']===1) return $o;
            $items=$this->fetchAll('SELECT oi.*,p.status,p.reservation_order_id FROM order_items oi JOIN products p ON p.product_id=oi.product_id WHERE oi.order_id=? ORDER BY p.product_id FOR UPDATE','i',[$o['order_id']]);
            $owned=$o['payment_status']==='pending' && count($items)>0;
            foreach ($items as $p) $owned=$owned && $p['status']==='reserved' && (int)$p['reservation_order_id']===(int)$o['order_id'];
            $this->write("UPDATE payments SET status='success',verified_at=UTC_TIMESTAMP() WHERE order_id=?",'i',[$o['order_id']]);
            if (!$owned) {
                // A late charge must never take inventory belonging to another checkout.
                $this->write('UPDATE orders SET payment_review=1 WHERE order_id=?','i',[$o['order_id']]);
                return $this->byReference($ref);
            }
            foreach ($items as $p) {
                $this->write("UPDATE products SET status='sold',stock_quantity=0,reservation_order_id=NULL WHERE product_id=?",'i',[$p['product_id']]);
                $net=self::cents($p['price_at_purchase'])-self::cents($p['commission_amount']);
                $this->write('INSERT INTO seller_earnings (order_item_id,seller_id,gross_amount,commission_amount,seller_amount) VALUES (?,?,?,?,?)','iisss',[$p['order_item_id'],$p['seller_id'],$p['price_at_purchase'],$p['commission_amount'],self::money($net)]);
                $this->write('DELETE ci FROM cart_items ci JOIN carts c ON c.cart_id=ci.cart_id WHERE c.user_id=? AND ci.product_id=?','ii',[$o['buyer_id'],$p['product_id']]);
            }
            $this->write("UPDATE orders SET payment_status='paid',paid_at=UTC_TIMESTAMP() WHERE order_id=?",'i',[$o['order_id']]);
            return $this->byReference($ref);
        });
    }
    public function listing($user, $scope) {
        if ($scope==='admin' && $user['role']==='admin') return $this->fetchAll('SELECT * FROM orders ORDER BY order_id DESC LIMIT 200');
        if ($scope==='seller') return $this->fetchAll("SELECT DISTINCT o.order_id,o.order_reference,o.payment_status,o.created_at FROM orders o JOIN order_items oi ON oi.order_id=o.order_id JOIN seller_profiles sp ON sp.seller_id=oi.seller_id WHERE sp.user_id=? AND o.payment_status='paid' ORDER BY o.order_id DESC LIMIT 200",'i',[$user['user_id']]);
        return $this->fetchAll("SELECT o.*,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id=o.order_id) AS item_count,
            (SELECT GROUP_CONCAT(p.image_path SEPARATOR '|') FROM order_items oi JOIN products p ON p.product_id=oi.product_id WHERE oi.order_id=o.order_id) AS thumbs,
            (SELECT oi.title_at_purchase FROM order_items oi WHERE oi.order_id=o.order_id ORDER BY oi.order_item_id LIMIT 1) AS first_title
            FROM orders o WHERE o.buyer_id=? ORDER BY o.order_id DESC LIMIT 200",'i',[$user['user_id']]);
    }
    public function detail($user,$id,$scope) {
        $o=$this->fetchOne('SELECT * FROM orders WHERE order_id=?','i',[$id]);
        if (!$o) return null;
        $admin=$scope==='admin' && $user['role']==='admin';
        if ($scope==='seller') {
            if ($o['payment_status']!=='paid') return null;
            $items=$this->fetchAll('SELECT oi.*,sp.shop_name,p.image_path FROM order_items oi JOIN seller_profiles sp ON sp.seller_id=oi.seller_id LEFT JOIN products p ON p.product_id=oi.product_id WHERE oi.order_id=? AND sp.user_id=?','ii',[$id,$user['user_id']]);
            if (!$items) return null;
            unset($o['total_amount'],$o['buyer_id'],$o['payment_review']);
        } else {
            if (!$admin && (int)$o['buyer_id']!==(int)$user['user_id']) return null;
            $items=$this->fetchAll('SELECT oi.*,sp.shop_name,p.image_path FROM order_items oi JOIN seller_profiles sp ON sp.seller_id=oi.seller_id LEFT JOIN products p ON p.product_id=oi.product_id WHERE oi.order_id=?','i',[$id]);
        }
        $o['items']=$items; return $o;
    }
    public function fulfil($userId,$itemId,$next) {
        $allowed=['pending'=>'processing','processing'=>'shipped','shipped'=>'delivered'];
        return $this->transaction(function () use ($userId,$itemId,$next,$allowed) {
            $i=$this->fetchOne("SELECT oi.* FROM order_items oi JOIN orders o ON o.order_id=oi.order_id JOIN seller_profiles sp ON sp.seller_id=oi.seller_id WHERE oi.order_item_id=? AND sp.user_id=? AND o.payment_status='paid' FOR UPDATE",'ii',[$itemId,$userId]);
            if (!$i || ($allowed[$i['fulfilment_status']]??null)!==$next) throw new DomainException('This fulfilment update is not allowed.');
            $this->write('UPDATE order_items SET fulfilment_status=? WHERE order_item_id=?','si',[$next,$itemId]);
        });
    }
    public function reconciliation() {
        return $this->fetchAll("SELECT sp.shop_name, SUM(se.gross_amount) AS gross, SUM(se.commission_amount) AS commission, SUM(se.seller_amount) AS net, SUM(CASE WHEN oi.fulfilment_status='delivered' THEN se.gross_amount ELSE 0 END) AS completed_gross FROM seller_earnings se JOIN seller_profiles sp ON sp.seller_id=se.seller_id JOIN order_items oi ON oi.order_item_id=se.order_item_id JOIN orders o ON o.order_id=oi.order_id WHERE o.payment_status='paid' GROUP BY sp.seller_id,sp.shop_name");
    }
}
