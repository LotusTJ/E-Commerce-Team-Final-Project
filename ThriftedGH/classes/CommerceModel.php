<?php
require_once __DIR__ . '/ProductClass.php';
abstract class CommerceModel {
    use ShopQueryTrait;
    protected function transaction(callable $work) {
        $db = $this->conn();
        $db->begin_transaction();
        try { $result = $work(); $db->commit(); return $result; }
        catch (Throwable $e) { $db->rollback(); throw $e; }
    }
    public static function cents($amount) {
        if (!is_scalar($amount) || !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', (string)$amount)) {
            throw new DomainException('Invalid price.');
        }
        $parts = explode('.', (string)$amount);
        return (int)$parts[0] * 100 + (int)str_pad($parts[1] ?? '', 2, '0');
    }
    public static function money($cents) { return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100); }
    public static function buyerFee($cents) { return intdiv($cents * 3 + 50, 100); }
    public static function sellerFee($cents) { return intdiv($cents * 7 + 50, 100); }
}
