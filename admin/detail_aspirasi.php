<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

if (!isset($_GET['id'])) {
    header('Location: aspirasi.php');
    exit();
}

$id = intval($_GET['id']);
// Use getInstance() if Database uses singleton pattern
$db = Database::getInstance();
$conn = $db->getConnection();

// Get aspirasi data
$sql = "SELECT a.*, k.nama_kategori, s.nama, s.kelas, s.nis 
        FROM aspirasi a 
        JOIN kategori k ON a.id_kategori = k.id_kategori
        JOIN siswa s ON a.nis = s.nis 
        WHERE a.id_aspirasi = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$aspirasi = $result->fetch_assoc();

if (!$aspirasi) {
    header('Location: aspirasi.php');
    exit();
}

// Get progress history (termasuk bukti_progres)
$sql_progres = "SELECT * FROM progres 
                WHERE id_aspirasi = ? 
                ORDER BY tanggal ASC";
$stmt_progres = $conn->prepare($sql_progres);
$stmt_progres->bind_param("i", $id);
$stmt_progres->execute();
$result_progres = $stmt_progres->get_result();
$progres = [];
while ($row = $result_progres->fetch_assoc()) {
    $progres[] = $row;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $db->escape($_POST['status']);
    $feedback = isset($_POST['feedback']) ? $db->escape($_POST['feedback']) : null;
    
    // Handle file upload untuk progres
    $bukti_progres = null;
    if (isset($_FILES['bukti_file']) && $_FILES['bukti_file']['error'] == 0) {
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['bukti_file']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Check file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $file_type = $_FILES['bukti_file']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['bukti_file']['tmp_name'], $target_file)) {
                $bukti_progres = $file_name;
            }
        }
    }
    
    // Siapkan keterangan untuk progres
    $keterangan = "Status diubah menjadi: $status";
    if (!empty($feedback)) {
        $keterangan .= " | Feedback: " . substr($feedback, 0, 100);
    }
    
    // Insert ke tabel progres (dengan bukti_progres jika ada)
    $sql_progres_insert = "INSERT INTO progres (id_aspirasi, status, keterangan, dibuat_oleh, bukti_progres) 
                           VALUES (?, ?, ?, 'Admin', ?)";
    $stmt_progres_insert = $conn->prepare($sql_progres_insert);
    $stmt_progres_insert->bind_param("isss", $id, $status, $keterangan, $bukti_progres);
    
    if ($stmt_progres_insert->execute()) {
        // Update status dan feedback di tabel aspirasi (tanpa mengubah bukti_selesai)
        if (!empty($feedback)) {
            $sql_update = "UPDATE aspirasi SET status = ?, feedback = ? WHERE id_aspirasi = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssi", $status, $feedback, $id);
        } else {
            $sql_update = "UPDATE aspirasi SET status = ? WHERE id_aspirasi = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $status, $id);
        }
        
        if ($stmt_update->execute()) {
            header("Location: detail_aspirasi.php?id=$id&success=1");
            exit();
        } else {
            $error_message = "Gagal mengupdate status aspirasi: " . $conn->error;
        }
    } else {
        $error_message = "Gagal mencatat progres: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aspirasi - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-8">
                <!-- Detail Aspirasi -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i> Detail Aspirasi
                            <span class="badge bg-light text-dark ms-2">
                                #<?php echo str_pad($aspirasi['id_aspirasi'], 4, '0', STR_PAD_LEFT); ?>
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">NIS</th>
                                        <td><?php echo $aspirasi['nis']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Nama Siswa</th>
                                        <td><?php echo $aspirasi['nama']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Kelas</th>
                                        <td><?php echo $aspirasi['kelas']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Kategori</th>
                                        <td><?php echo $aspirasi['nama_kategori']; ?></td>
                                    </tr>
                                    <!-- Tampilkan Bukti Awal -->
                                    <tr>
                                        <th>Bukti Awal</th>
                                        <td>
                                            <?php if (!empty($aspirasi['bukti_awal'])): ?>
                                                <a href="../uploads/<?php echo $aspirasi['bukti_awal']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-file-earmark-image"></i> Lihat Bukti
                                                </a>
                                            <?php else: ?>
                                                Tidak ada
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Lokasi</th>
                                        <td><?php echo $aspirasi['lokasi']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($aspirasi['status']); ?>">
                                                <?php echo $aspirasi['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal</th>
                                        <td><?php echo date('d/m/Y H:i', strtotime($aspirasi['tanggal_dibuat'])); ?></td>
                                    </tr>
                                    <?php if (!empty($aspirasi['bukti_selesai'])): ?>
                                    <tr>
                                        <th>Bukti Lama</th>
                                        <td>
                                            <a href="../uploads/<?php echo $aspirasi['bukti_selesai']; ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-warning">
                                                <i class="bi bi-file-earmark"></i> Lihat
                                            </a>
                                            <small class="d-block text-muted">(Bukti dari sistem lama)</small>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <label class="form-label"><strong>Deskripsi Masalah:</strong></label>
                            <div class="alert alert-light border">
                                <?php echo nl2br($aspirasi['deskripsi']); ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($aspirasi['feedback'])): ?>
                        <div class="mt-3">
                            <label class="form-label"><strong>Feedback Terakhir:</strong></label>
                            <div class="alert alert-warning">
                                <?php echo nl2br($aspirasi['feedback']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Progress Timeline -->
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-activity"></i> Timeline Progres
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php if (count($progres) > 0): ?>
                                <?php foreach ($progres as $p): ?>
                                <div class="timeline-item mb-3">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1"><?php echo $p['status']; ?></h6>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($p['tanggal'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?php echo $p['keterangan']; ?></p>
                                    <?php if (!empty($p['bukti_progres'])): ?>
                                        <div class="mt-2">
                                            <a href="../uploads/<?php echo $p['bukti_progres']; ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-file-earmark-image"></i> Lihat Bukti Progres
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        Oleh: <?php echo $p['dibuat_oleh']; ?>
                                    </small>
                                    <hr class="my-2">
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center text-muted py-3">Belum ada riwayat progres.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Update Status Form -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square"></i> Update Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">
                                Status berhasil diupdate!
                            </div>
                        <?php elseif (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="Menunggu" <?php echo ($aspirasi['status'] == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                                    <option value="Proses" <?php echo ($aspirasi['status'] == 'Proses') ? 'selected' : ''; ?>>Proses</option>
                                    <option value="Selesai" <?php echo ($aspirasi['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Feedback (Opsional)</label>
                                <textarea name="feedback" class="form-control" rows="4" 
                                          placeholder="Berikan feedback untuk siswa..."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Upload Bukti (Opsional)</label>
                                <input type="file" name="bukti_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                <small class="text-muted">Format: JPG, PNG, PDF (Max: 5MB) - Bukti ini akan muncul di timeline progres.</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>
                                <a href="aspirasi.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-lightning"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <small>
                                <strong>Status Menunggu:</strong> Aspirasi baru, belum ditinjau<br>
                                <strong>Status Proses:</strong> Sedang dalam penanganan<br>
                                <strong>Status Selesai:</strong> Sudah ditangani atau tidak valid
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>