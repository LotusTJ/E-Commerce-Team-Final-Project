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
}
?>