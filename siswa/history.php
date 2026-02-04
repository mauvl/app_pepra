<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotSiswa();

$nis = $_SESSION['user_id'];

// Get aspirasi for current siswa using Functions helper
$aspirasi = functions()->getAspirasiBySiswa($nis);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Aspirasi - Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-warning">
                <i class="bi bi-clock-history"></i> Riwayat Aspirasi
            </h2>
            <a href="input_aspirasi.php" class="btn btn-warning">
                <i class="bi bi-plus-circle"></i> Ajukan Baru
            </a>
        </div>
        
        <?php if (count($aspirasi) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Feedback</th>
                            <th>Bukti</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aspirasi as $row): ?>
                        <tr>
                            <td>#<?php echo str_pad($row['id_aspirasi'], 4, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['tanggal_dibuat'])); ?></td>
                            <td><?php echo $row['nama_kategori']; ?></td>
                            <td><?php echo $row['lokasi']; ?></td>
                            <td>
                                <small><?php echo substr($row['deskripsi'], 0, 50); ?>...</small>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['feedback']): ?>
                                    <button type="button" class="btn btn-sm btn-outline-info" 
                                            data-bs-toggle="popover" 
                                            title="Feedback Admin"
                                            data-bs-content="<?php echo htmlspecialchars($row['feedback']); ?>">
                                        <i class="bi bi-chat-text"></i> Lihat
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['bukti_selesai']): ?>
                                    <a href="../uploads/<?php echo $row['bukti_selesai']; ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-image"></i> Lihat
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="detail.php?id=<?php echo $row['id_aspirasi']; ?>" 
                                   class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <h4 class="mt-3">Belum ada riwayat aspirasi</h4>
                <p class="text-muted">Anda belum pernah mengajukan aspirasi.</p>
                <a href="input_aspirasi.php" class="btn btn-warning mt-2">
                    <i class="bi bi-plus-circle"></i> Ajukan Aspirasi Pertama
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Progress Legend -->
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle"></i> Keterangan Status
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-menunggu me-2">Menunggu</span>
                            <span>Aspirasi baru, belum ditinjau admin</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-proses me-2">Proses</span>
                            <span>Sedang dalam penanganan</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-selesai me-2">Selesai</span>
                            <span>Sudah ditangani atau tidak valid</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    <script>
        // Initialize popovers
        document.addEventListener('DOMContentLoaded', function() {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
    </script>
</body>
</html>