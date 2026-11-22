<?php
require_once __DIR__ . '/../core/db_class.php';

class AdminClass
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function count($sql)
    {
        $result = $this->db->query($sql);
        $row = $result ? $result->fetch_row() : [0];
        return (int)$row[0];
    }

    public function overview()
    {
        return [
            'pending_applications' => $this->count("SELECT COUNT(*) FROM seller_applications WHERE status = 'pending'"),
            'approved_sellers' => $this->count("SELECT COUNT(*) FROM seller_profiles"),
            'buyers' => $this->count("SELECT COUNT(*) FROM users WHERE role = 'buyer'"),
            'available_products' => $this->count("SELECT COUNT(*) FROM products WHERE status = 'available'"),
            'paid_orders' => $this->count("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid'"),
            'orders_in_review' => $this->count("SELECT COUNT(*) FROM orders WHERE payment_review = 1")
        ];
    }
}
