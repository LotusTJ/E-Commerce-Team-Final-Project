<?php
require_once __DIR__ . '/../core/db_class.php';

class DocumentClass
{
    const TYPES = [
        'student_id' => 'Student ID',
        'national_id' => 'National ID or passport',
        'business_registration' => 'Business registration',
        'other' => 'Other supporting document'
    ];

    const MAX_PER_APPLICATION = 5;

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

    public function forApplication($applicationId)
    {
        return $this->fetch(
            'SELECT document_id, application_id, user_id, doc_type, original_name, mime_type, size_bytes, uploaded_at
             FROM seller_documents WHERE application_id = ? ORDER BY document_id ASC',
            'i',
            [(int)$applicationId]
        );
    }

    public function countForApplication($applicationId)
    {
        $rows = $this->fetch('SELECT COUNT(*) AS total FROM seller_documents WHERE application_id = ?', 'i', [(int)$applicationId]);
        return (int)$rows[0]['total'];
    }

    public function find($documentId)
    {
        $rows = $this->fetch('SELECT * FROM seller_documents WHERE document_id = ? LIMIT 1', 'i', [(int)$documentId]);
        return $rows ? $rows[0] : null;
    }

    public function add($applicationId, $userId, $type, $originalName, $storedName, $mime, $size)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO seller_documents (application_id, user_id, doc_type, original_name, stored_name, mime_type, size_bytes)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $applicationId = (int)$applicationId;
        $userId = (int)$userId;
        $size = (int)$size;
        $stmt->bind_param('iissssi', $applicationId, $userId, $type, $originalName, $storedName, $mime, $size);
        $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    public function remove($documentId)
    {
        $stmt = $this->db->prepare('DELETE FROM seller_documents WHERE document_id = ?');
        $documentId = (int)$documentId;
        $stmt->bind_param('i', $documentId);
        $stmt->execute();
        $stmt->close();
    }
}
