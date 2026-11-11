<?php

require_once __DIR__ . '/ShopQueryTrait.php';

class StorefrontClass
{
    use ShopQueryTrait;

    const APPROVED_CONDITION = "EXISTS (SELECT 1 FROM seller_applications sa WHERE sa.user_id = sp.user_id AND sa.status = 'approved')";

    public function getApprovedSellerByUserId($userId)
    {
        try {
            return $this->fetchOne(
                "SELECT sp.seller_id, sp.user_id, sp.shop_name, sp.seller_type, sp.description, sp.location
                 FROM seller_profiles sp
                 WHERE sp.user_id = ? AND " . self::APPROVED_CONDITION . "
                 LIMIT 1",
                'i',
                [(int)$userId]
            );
        } catch (Throwable $error) {
            error_log('StorefrontClass::getApprovedSellerByUserId ' . $error->getMessage());
            return null;
        }
    }

    public function getApprovedSellerById($sellerId)
    {
        try {
            return $this->fetchOne(
                "SELECT sp.seller_id, sp.shop_name, sp.seller_type, sp.description, sp.location
                 FROM seller_profiles sp
                 WHERE sp.seller_id = ? AND " . self::APPROVED_CONDITION . "
                 LIMIT 1",
                'i',
                [(int)$sellerId]
            );
        } catch (Throwable $error) {
            error_log('StorefrontClass::getApprovedSellerById ' . $error->getMessage());
            return null;
        }
    }
}
