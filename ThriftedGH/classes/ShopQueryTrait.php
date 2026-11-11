<?php

require_once __DIR__ . '/../core/db_class.php';

trait ShopQueryTrait
{
    protected function conn()
    {
        if (!class_exists('Database')) {
            throw new RuntimeException('Database class is not available');
        }
        $connection = Database::getInstance();
        if (!$connection) {
            throw new RuntimeException('No database connection');
        }
        return $connection;
    }

    protected function run($sql, $types = '', array $params = [])
    {
        $connection = $this->conn();
        $stmt = $connection->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException($connection->error);
        }
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException($error);
        }
        return $stmt;
    }

    protected function fetchAll($sql, $types = '', array $params = [])
    {
        $stmt = $this->run($sql, $types, $params);
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    protected function fetchOne($sql, $types = '', array $params = [])
    {
        $rows = $this->fetchAll($sql, $types, $params);
        return $rows ? $rows[0] : null;
    }

    protected function write($sql, $types = '', array $params = [])
    {
        $stmt = $this->run($sql, $types, $params);
        $outcome = ['affected' => $stmt->affected_rows, 'id' => $stmt->insert_id];
        $stmt->close();
        return $outcome;
    }
}
