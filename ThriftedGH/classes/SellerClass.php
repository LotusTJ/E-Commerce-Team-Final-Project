<?php
require_once __DIR__ . '/../core/db_class.php';

class SellerClass
{
    const TYPES = [
        'individual' => 'Individual',
        'business' => 'Business'
    ];

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function fetch($sql, $types = '', array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    private function execute($sql, $types, array $params)
    {
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    public function getLatestApplication($userId)
    {
        $rows = $this->fetch(
            'SELECT application_id, seller_type, shop_name, location, business_details, status, created_at, reviewed_at
             FROM seller_applications WHERE user_id = ? ORDER BY application_id DESC LIMIT 1',
            'i',
            [(int)$userId]
        );
        return $rows ? $rows[0] : null;
    }

    public function shopNameTaken($shopName, $userId)
    {
        $profile = $this->fetch('SELECT 1 FROM seller_profiles WHERE shop_name = ? AND user_id <> ? LIMIT 1', 'si', [$shopName, (int)$userId]);
        if ($profile) {
            return true;
        }
        $open = $this->fetch(
            "SELECT 1 FROM seller_applications WHERE shop_name = ? AND user_id <> ? AND status IN ('pending','approved') LIMIT 1",
            'si',
            [$shopName, (int)$userId]
        );
        return (bool)$open;
    }

    public function submitApplication($userId, $type, $shopName, $location, $details, $phone)
    {
        $userId = (int)$userId;
        $this->db->begin_transaction();
        try {
            $user = $this->fetch('SELECT role FROM users WHERE user_id = ? FOR UPDATE', 'i', [$userId]);
            if (!$user) {
                $this->db->rollback();
                return ['ok' => false, 'error' => 'Account not found.'];
            }
            if ($user[0]['role'] === 'admin') {
                $this->db->rollback();
                return ['ok' => false, 'error' => 'Administrator accounts cannot apply to sell.'];
            }
            $open = $this->fetch("SELECT status FROM seller_applications WHERE user_id = ? AND status IN ('pending','approved') LIMIT 1", 'i', [$userId]);
            if ($open) {
                $this->db->rollback();
                return ['ok' => false, 'error' => $open[0]['status'] === 'approved' ? 'You are already an approved seller.' : 'You already have an application waiting for review.'];
            }
            $this->execute(
                "INSERT INTO seller_applications (user_id, seller_type, shop_name, location, business_details, status) VALUES (?, ?, ?, ?, ?, 'pending')",
                'issss',
                [$userId, $type, $shopName, $location, $details]
            );
            if ($phone !== '') {
                $this->execute('UPDATE users SET phone = ? WHERE user_id = ?', 'si', [$phone, $userId]);
            }
            $this->db->commit();
            return ['ok' => true];
        } catch (Throwable $error) {
            $this->db->rollback();
            error_log('SellerClass::submitApplication ' . $error->getMessage());
            return ['ok' => false, 'error' => 'Your application could not be saved. Please try again.'];
        }
    }

    public function getPendingApplications()
    {
        return $this->fetch(
            "SELECT a.application_id, a.seller_type, a.shop_name, a.location, a.business_details, a.created_at,
                    u.full_name, u.email, u.phone
             FROM seller_applications a
             JOIN users u ON u.user_id = a.user_id
             WHERE a.status = 'pending'
             ORDER BY a.application_id ASC"
        );
    }

    public function getRecentDecisions($limit)
    {
        return $this->fetch(
            "SELECT a.application_id, a.shop_name, a.status, a.reviewed_at, u.full_name
             FROM seller_applications a
             JOIN users u ON u.user_id = a.user_id
             WHERE a.status IN ('approved','rejected')
             ORDER BY a.reviewed_at DESC, a.application_id DESC
             LIMIT ?",
            'i',
            [(int)$limit]
        );
    }

    public function review($applicationId, $adminId, $decision)
    {
        $this->db->begin_transaction();
        try {
            $rows = $this->fetch('SELECT * FROM seller_applications WHERE application_id = ? FOR UPDATE', 'i', [(int)$applicationId]);
            if (!$rows || $rows[0]['status'] !== 'pending') {
                $this->db->rollback();
                return ['ok' => false, 'error' => 'This application was already reviewed or does not exist.'];
            }
            $application = $rows[0];
            if ($decision === 'approve') {
                $this->execute(
                    'INSERT INTO seller_profiles (user_id, shop_name, seller_type, location, description) VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE shop_name = VALUES(shop_name), seller_type = VALUES(seller_type),
                     location = VALUES(location), description = VALUES(description)',
                    'issss',
                    [(int)$application['user_id'], $application['shop_name'], $application['seller_type'], $application['location'], $application['business_details']]
                );
                $this->execute("UPDATE users SET role = 'seller' WHERE user_id = ? AND role = 'buyer'", 'i', [(int)$application['user_id']]);
                $status = 'approved';
            } else {
                $status = 'rejected';
            }
            $this->execute(
                'UPDATE seller_applications SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE application_id = ?',
                'sii',
                [$status, (int)$adminId, (int)$applicationId]
            );
            $this->db->commit();
            return ['ok' => true, 'status' => $status, 'shop_name' => $application['shop_name']];
        } catch (Throwable $error) {
            $this->db->rollback();
            error_log('SellerClass::review ' . $error->getMessage());
            return ['ok' => false, 'error' => 'The decision could not be saved. Please try again.'];
        }
    }
}
