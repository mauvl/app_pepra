<?php
require_once __DIR__ . '/../config/database.php';

class Functions {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Authentication Functions
    public function loginAdmin($username, $password) {
        $sql = "SELECT * FROM admin WHERE username = ?";
        $admin = $this->db->fetchOne($sql, [$username]);
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id_admin'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['nama'] = $admin['nama_lengkap'];
            $_SESSION['role'] = 'admin';
            $_SESSION['email'] = $admin['email'];
            
            // Update last login
            $this->db->update('admin', 
                ['last_login' => date('Y-m-d H:i:s')], 
                ['id_admin' => $admin['id_admin']]
            );
            
            return true;
        }
        return false;
    }
    
    public function loginSiswa($nis, $password) {
        $sql = "SELECT * FROM siswa WHERE nis = ?";
        $siswa = $this->db->fetchOne($sql, [$nis]);
        
        if ($siswa && password_verify($password, $siswa['password'])) {
            $_SESSION['user_id'] = $siswa['nis'];
            $_SESSION['username'] = $siswa['nis'];
            $_SESSION['nama'] = $siswa['nama'];
            $_SESSION['kelas'] = $siswa['kelas'];
            $_SESSION['role'] = 'siswa';
            $_SESSION['email'] = $siswa['email'];
            
            // Update last login
            $this->db->update('siswa', 
                ['last_login' => date('Y-m-d H:i:s')], 
                ['nis' => $siswa['nis']]
            );
            
            return true;
        }
        return false;
    }
    
    public function registerSiswa($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->db->insert('siswa', $data);
    }
    
    // Category Functions
    public function getKategori() {
        $sql = "SELECT * FROM kategori ORDER BY nama_kategori";
        return $this->db->fetchAll($sql);
    }
    
    public function getKategoriById($id) {
        $sql = "SELECT * FROM kategori WHERE id_kategori = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    // Aspirasi Functions
    public function addAspirasi($data) {
        $this->db->beginTransaction();
        
        try {
            $id_aspirasi = $this->db->insert('aspirasi', $data);
            
            // Add initial progress
            $progres_data = [
                'id_aspirasi' => $id_aspirasi,
                'status' => 'Menunggu',
                'keterangan' => 'Aspirasi diajukan oleh siswa',
                'dibuat_oleh' => 'Sistem'
            ];
            $this->db->insert('progres', $progres_data);
            
            $this->db->commit();
            return $id_aspirasi;
            
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    public function updateAspirasi($id, $data) {
        return $this->db->update('aspirasi', $data, ['id_aspirasi' => $id]);
    }
    
    public function updateStatusAspirasi($id, $status, $feedback = null, $bukti = null) {
        $data = ['status' => $status];
        
        if ($feedback) {
            $data['feedback'] = $feedback;
        }
        
        if ($bukti) {
            $data['bukti_selesai'] = $bukti;
        }
        
        $updated = $this->db->update('aspirasi', $data, ['id_aspirasi' => $id]);
        
        if ($updated) {
            // Add progress history
            $keterangan = "Status diubah menjadi: $status";
            if ($feedback) {
                $keterangan .= " | Feedback: " . substr($feedback, 0, 100) . "...";
            }
            
            $progres_data = [
                'id_aspirasi' => $id,
                'status' => $status,
                'keterangan' => $keterangan,
                'dibuat_oleh' => $_SESSION['role'] == 'admin' ? 'Admin' : 'Siswa'
            ];
            $this->db->insert('progres', $progres_data);
        }
        
        return $updated;
    }
    
    public function deleteAspirasi($id) {
        return $this->db->delete('aspirasi', ['id_aspirasi' => $id]);
    }
    
    public function getAspirasiById($id) {
        $sql = "SELECT a.*, k.nama_kategori, k.icon, s.nama, s.kelas, s.nis 
                FROM aspirasi a 
                JOIN kategori k ON a.id_kategori = k.id_kategori
                JOIN siswa s ON a.nis = s.nis 
                WHERE a.id_aspirasi = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getAspirasiBySiswa($nis) {
        $sql = "SELECT a.*, k.nama_kategori, k.icon,
                (SELECT COUNT(*) FROM aspirasi WHERE nis = ?) as total,
                (SELECT COUNT(*) FROM aspirasi WHERE nis = ? AND status = 'Selesai') as selesai
                FROM aspirasi a 
                JOIN kategori k ON a.id_kategori = k.id_kategori 
                WHERE a.nis = ? 
                ORDER BY a.tanggal_dibuat DESC";
        return $this->db->fetchAll($sql, [$nis, $nis, $nis]);
    }
    
    public function getAllAspirasi($filters = []) {
        $sql = "SELECT a.*, k.nama_kategori, k.icon, s.nama, s.kelas 
                FROM aspirasi a 
                JOIN kategori k ON a.id_kategori = k.id_kategori
                JOIN siswa s ON a.nis = s.nis 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['kategori'])) {
            $sql .= " AND a.id_kategori = ?";
            $params[] = $filters['kategori'];
        }
        
        if (!empty($filters['tanggal'])) {
            $sql .= " AND DATE(a.tanggal_dibuat) = ?";
            $params[] = $filters['tanggal'];
        }
        
        if (!empty($filters['bulan'])) {
            $sql .= " AND DATE_FORMAT(a.tanggal_dibuat, '%Y-%m') = ?";
            $params[] = $filters['bulan'];
        }
        
        if (!empty($filters['nis'])) {
            $sql .= " AND a.nis LIKE ?";
            $params[] = '%' . $filters['nis'] . '%';
        }
        
        $sql .= " ORDER BY a.tanggal_dibuat DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    // Progress Functions
    public function getProgresAspirasi($id_aspirasi) {
        $sql = "SELECT * FROM progres 
                WHERE id_aspirasi = ? 
                ORDER BY tanggal ASC";
        return $this->db->fetchAll($sql, [$id_aspirasi]);
    }
    
    // Statistics Functions
    public function getStats() {
        $stats = [];
        
        // Overall stats
        $sql_total = "SELECT COUNT(*) as total FROM aspirasi";
        $stats['total'] = $this->db->fetchOne($sql_total)['total'];
        
        // Status stats
        $sql_status = "SELECT status, COUNT(*) as jumlah FROM aspirasi GROUP BY status";
        $status_data = $this->db->fetchAll($sql_status);
        
        foreach ($status_data as $row) {
            $stats[$row['status']] = $row['jumlah'];
        }
        
        // Category stats
        $sql_cat = "SELECT k.nama_kategori, COUNT(a.id_aspirasi) as jumlah 
                   FROM kategori k 
                   LEFT JOIN aspirasi a ON k.id_kategori = a.id_kategori 
                   GROUP BY k.id_kategori";
        $stats['kategori'] = $this->db->fetchAll($sql_cat);
        
        // Monthly stats
        $sql_monthly = "SELECT DATE_FORMAT(tanggal_dibuat, '%Y-%m') as bulan, 
                       COUNT(*) as jumlah 
                       FROM aspirasi 
                       GROUP BY DATE_FORMAT(tanggal_dibuat, '%Y-%m') 
                       ORDER BY bulan DESC 
                       LIMIT 6";
        $stats['monthly'] = $this->db->fetchAll($sql_monthly);
        
        return $stats;
    }
    
    // Utility Functions
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }
    
    public function generateRandomPassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($chars), 0, $length);
    }
    
    public function formatDate($date, $format = 'd F Y H:i') {
        return date($format, strtotime($date));
    }
    
    public function paginate($sql, $params = [], $page = 1, $per_page = 10) {
        $offset = ($page - 1) * $per_page;
        $sql .= " LIMIT ? OFFSET ?";
        
        $params[] = $per_page;
        $params[] = $offset;
        
        $data = $this->db->fetchAll($sql, $params);
        
        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM (" . str_replace(['LIMIT ? OFFSET ?'], '', $sql) . ") as count_table";
        $total = $this->db->fetchOne($count_sql, array_slice($params, 0, -2))['total'];
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ];
    }

        // ==================== MANAJEMEN SISWA ====================
    public function getAllSiswa($order = 'nama') {
        $sql = "SELECT * FROM siswa ORDER BY $order";
        return $this->db->fetchAll($sql);
    }

    public function getSiswaByNis($nis) {
        $sql = "SELECT * FROM siswa WHERE nis = ?";
        return $this->db->fetchOne($sql, [$nis]);
    }

    public function addSiswa($data) {
        // data: nis, nama, kelas, password, email (opsional), foto (opsional)
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->db->insert('siswa', $data);
    }

    public function updateSiswa($nis, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        return $this->db->update('siswa', $data, ['nis' => $nis]);
    }

    public function deleteSiswa($nis) {
        return $this->db->delete('siswa', ['nis' => $nis]);
    }

    public function resetPasswordSiswa($nis, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->update('siswa', ['password' => $hash], ['nis' => $nis]);
    }

    // ==================== MANAJEMEN ADMIN ====================
    public function getAllAdmin() {
        $sql = "SELECT * FROM admin ORDER BY nama_lengkap";
        return $this->db->fetchAll($sql);
    }

    public function getAdminById($id) {
        $sql = "SELECT * FROM admin WHERE id_admin = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function addAdmin($data) {
        // data: username, password, nama_lengkap, email (opsional)
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->db->insert('admin', $data);
    }

    public function updateAdmin($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }
        return $this->db->update('admin', $data, ['id_admin' => $id]);
    }

    public function deleteAdmin($id) {
        return $this->db->delete('admin', ['id_admin' => $id]);
    }

    public function resetPasswordAdmin($id, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->db->update('admin', ['password' => $hash], ['id_admin' => $id]);
    }
}

// Global helper function
function functions() {
    static $instance = null;
    if ($instance === null) {
        $instance = new Functions();
    }
    return $instance;
}
?>