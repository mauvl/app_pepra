<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pengaduan_sarana');
define('DB_PORT', '3306');

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            if ($this->conn->connect_error) {
                throw new Exception("Koneksi database gagal: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
            
            // Set timezone
            $this->conn->query("SET time_zone = '+07:00'");
            
        } catch (Exception $e) {
            die(json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]));
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            $types = '';
            $bind_params = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                $bind_params[] = $param;
            }
            
            array_unshift($bind_params, $types);
            call_user_func_array([$stmt, 'bind_param'], $this->refValues($bind_params));
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        
        $stmt->close();
        return $rows;
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $stmt->close();
        return $row ?: null;
    }
    
    public function insert($table, $data) {
        $keys = array_keys($data);
        $values = array_values($data);
        
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        $sql = "INSERT INTO $table (" . implode(',', $keys) . ") VALUES ($placeholders)";
        
        $stmt = $this->query($sql, $values);
        $insert_id = $stmt->insert_id;
        
        $stmt->close();
        return $insert_id;
    }
    
    public function update($table, $data, $where) {
        $setClause = [];
        $whereClause = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $setClause[] = "$key = ?";
            $params[] = $value;
        }
        
        foreach ($where as $key => $value) {
            $whereClause[] = "$key = ?";
            $params[] = $value;
        }
        
        $sql = "UPDATE $table SET " . implode(', ', $setClause) . 
               " WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->query($sql, $params);
        $affected_rows = $stmt->affected_rows;
        
        $stmt->close();
        return $affected_rows;
    }
    
    public function delete($table, $where) {
        $whereClause = [];
        $params = [];
        
        foreach ($where as $key => $value) {
            $whereClause[] = "$key = ?";
            $params[] = $value;
        }
        
        $sql = "DELETE FROM $table WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->query($sql, $params);
        $affected_rows = $stmt->affected_rows;
        
        $stmt->close();
        return $affected_rows;
    }
    
    public function escape($value) {
        return $this->conn->real_escape_string($value);
    }
    
    public function getLastId() {
        return $this->conn->insert_id;
    }
    
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }
    
    public function commit() {
        $this->conn->commit();
    }
    
    public function rollback() {
        $this->conn->rollback();
    }
    
    private function refValues($arr) {
        $refs = [];
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Helper function untuk mendapatkan instance database
function db() {
    return Database::getInstance();
}
?>