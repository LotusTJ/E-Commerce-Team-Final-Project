<?php
require_once __DIR__ . '/CommerceModel.php';
class CartClass extends CommerceModel {
    public function items($userId) {
        return $this->fetchAll("SELECT p.*, sp.shop_name AS seller_name,
            (p.status = 'available' AND p.stock_quantity = 1 AND " . ProductClass::APPROVED_CONDITION . ") AS purchasable
            FROM carts c JOIN cart_items ci ON ci.cart_id=c.cart_id
            JOIN products p ON p.product_id=ci.product_id
            JOIN seller_profiles sp ON sp.seller_id=p.seller_id
            WHERE c.user_id=? ORDER BY p.product_id", 'i', [$userId]);
    }
    public function add($userId, $productId) {
        $this->transaction(function () use ($userId, $productId) {
            $p = $this->fetchOne("SELECT p.product_id FROM products p
                JOIN seller_profiles sp ON sp.seller_id=p.seller_id
                WHERE p.product_id=? AND p.status='available' AND p.stock_quantity=1 AND " . ProductClass::APPROVED_CONDITION . " FOR UPDATE", 'i', [$productId]);
            if (!$p) throw new DomainException('This item is no longer available.');
            $this->write('INSERT INTO carts (user_id) VALUES (?) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id)', 'i', [$userId]);
            $this->write('INSERT INTO cart_items (cart_id,product_id) SELECT cart_id,? FROM carts WHERE user_id=? ON DUPLICATE KEY UPDATE product_id=VALUES(product_id)', 'ii', [$productId,$userId]);
        });
    }
    public function remove($userId, $productId) {
        $this->write('DELETE ci FROM cart_items ci JOIN carts c ON c.cart_id=ci.cart_id WHERE c.user_id=? AND ci.product_id=?', 'ii', [$userId,$productId]);
    }
}
