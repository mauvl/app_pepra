<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotSiswa();

if (!isset($_GET['id'])) {
    header('Location: history.php');
    exit();
}

$id = intval($_GET['id']);
$nis = $_SESSION['user_id'];

// Fetch aspirasi using the Functions helper and ensure ownership
$aspirasi = functions()->getAspirasiById($id);

if (!$aspirasi || $aspirasi['nis'] != $nis) {
    header('Location: history.php');
    exit();
}

if (!$aspirasi) {
    header('Location: history.php');
    exit();
}

// Get progress history
$sql_progres = "SELECT * FROM progres WHERE id_aspirasi = ? ORDER BY tanggal ASC";
$progres = db()->fetchAll($sql_progres, [$id]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Aspirasi - Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-warning">
                <i class="bi bi-info-circle"></i> Detail Aspirasi
                <span class="badge bg-warning text-dark ms-2">
                    #<?php echo str_pad($aspirasi['id_aspirasi'], 4, '0', STR_PAD_LEFT); ?>
                </span>
            </h2>
            <a href="history.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <!-- Aspirasi Details -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-card-text"></i> Informasi Aspirasi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Tanggal</th>
                                        <td><?php echo date('d F Y H:i', strtotime($aspirasi['tanggal_dibuat'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Kategori</th>
                                        <td><?php echo $aspirasi['nama_kategori']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Lokasi</th>
                                        <td><?php echo $aspirasi['lokasi']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Status</th>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($aspirasi['status']); ?>">
                                                <?php echo $aspirasi['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Terakhir Diupdate</th>
                                        <td><?php echo date('d/m/Y H:i', strtotime($aspirasi['tanggal_diupdate'])); ?></td>
                                    </tr>
                                    <?php if ($aspirasi['bukti_selesai']): ?>
                                    <tr>
                                        <th>Bukti</th>
                                        <td>
                                            <a href="../uploads/<?php echo $aspirasi['bukti_selesai']; ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-image"></i> Lihat Bukti
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <label class="form-label"><strong>Deskripsi Masalah:</strong></label>
                            <div class="alert alert-light border p-3">
                                <?php echo nl2br($aspirasi['deskripsi']); ?>
                            </div>
                        </div>
                        
                        <?php if ($aspirasi['feedback']): ?>
                        <div class="mt-3">
                            <label class="form-label"><strong>Feedback dari Admin:</strong></label>
                            <div class="alert alert-warning">
                                <i class="bi bi-chat-quote"></i>
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
                                <div class="timeline-item">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1 text-primary"><?php echo $p['status']; ?></h6>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($p['tanggal'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?php echo $p['keterangan']; ?></p>
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i> Oleh: <?php echo $p['dibuat_oleh']; ?>
                                    </small>
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
                <!-- Status Information -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle"></i> Informasi Status
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge badge-menunggu me-2">Menunggu</span>
                                <span class="small">Aspirasi Anda sedang menunggu ditinjau oleh admin.</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge badge-proses me-2">Proses</span>
                                <span class="small">Aspirasi Anda sedang dalam penanganan tim sarana.</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge badge-selesai me-2">Selesai</span>
                                <span class="small">Aspirasi Anda sudah selesai ditangani.</span>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="bi bi-lightbulb"></i> Tips:</h6>
                            <small>
                                <ul class="mb-0">
                                    <li>Pantau progres aspirasi Anda di timeline</li>
                                    <li>Periksa feedback dari admin secara berkala</li>
                                    <li>Jika sudah selesai, lihat bukti penyelesaian</li>
                                    <li>Ajukan aspirasi baru jika ada masalah lain</li>
                                </ul>
                            </small>
                        </div>
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
                        <div class="d-grid gap-2">
                            <?php if ($aspirasi['status'] != 'Selesai'): ?>
                                <a href="input_aspirasi.php?edit=<?php echo $aspirasi['id_aspirasi']; ?>" 
                                   class="btn btn-outline-warning">
                                    <i class="bi bi-pencil"></i> Edit Aspirasi
                                </a>
                            <?php endif; ?>
                            
                            <a href="history.php" class="btn btn-outline-secondary">
                                <i class="bi bi-clock-history"></i> Lihat Riwayat
                            </a>
                            
                            <a href="input_aspirasi.php" class="btn btn-warning">
                                <i class="bi bi-plus-circle"></i> Ajukan Baru
                            </a>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> Diperbarui: 
                                <?php echo date('d/m/Y H:i', strtotime($aspirasi['tanggal_diupdate'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Admin -->
                <div class="card mt-4">
                    <div class="card-header bg-warning text-white">
                        <h6 class="mb-0">
                            <i class="bi bi-headset"></i> Butuh Bantuan?
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">
                            Jika ada pertanyaan terkait aspirasi ini, hubungi admin:
                        </p>
                        <div class="d-grid gap-2">
                            <a href="mailto:admin@pengaduan.sch.id?subject=Pertanyaan Aspirasi #<?php echo str_pad($aspirasi['id_aspirasi'], 4, '0', STR_PAD_LEFT); ?>" 
                               class="btn btn-outline-warning btn-sm">
                                <i class="bi bi-envelope"></i> Email Admin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
