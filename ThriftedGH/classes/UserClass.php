<?php
require_once __DIR__ . '/../core/db_class.php';

class UserClass {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function registerUser($fullName, $email, $passwordHash, $phone) {
        $stmt = $this->db->prepare("INSERT INTO users (full_name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, 'buyer')");
        $stmt->bind_param("ssss", $fullName, $email, $passwordHash, $phone);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function loginUser($email) {
        $stmt = $this->db->prepare("SELECT user_id, full_name, email, password_hash, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    const MAX_FAILURES = 5;
    const MAX_FAILURES_PER_IP = 25;
    const LOCK_MINUTES = 15;

    public function isLockedOut($email, $ip) {
        $email = strtolower($email);
        $window = self::LOCK_MINUTES;
        $stmt = @$this->db->prepare("SELECT SUM(email = ?) AS by_email, SUM(ip_address = ?) AS by_ip FROM login_attempts WHERE attempted_at > (NOW() - INTERVAL $window MINUTE)");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ss", $email, $ip);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['by_email'] ?? 0) >= self::MAX_FAILURES || (int)($row['by_ip'] ?? 0) >= self::MAX_FAILURES_PER_IP;
    }

    public function recordFailure($email, $ip) {
        $email = strtolower($email);
        $stmt = @$this->db->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)");
        if (!$stmt) {
            return;
        }
        $stmt->bind_param("ss", $email, $ip);
        $stmt->execute();
        $stmt->close();
    }

    public function clearFailures($email) {
        $email = strtolower($email);
        $stmt = @$this->db->prepare("DELETE FROM login_attempts WHERE email = ?");
        if (!$stmt) {
            return;
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
    }
}
