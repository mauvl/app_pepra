<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotSiswa();

$nis = $_SESSION['user_id'];
$aspirasi = getAspirasiBySiswa($nis);

// Hitung statistik
$total = $aspirasi[0]['total'] ?? 0;
$selesai = $aspirasi[0]['selesai'] ?? 0;
$proses = 0;
$menunggu = 0;

foreach ($aspirasi as $a) {
    if ($a['status'] == 'Proses') $proses++;
    if ($a['status'] == 'Menunggu') $menunggu++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - Pengaduan Sarana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-building"></i> Siswa Panel
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="input_aspirasi.php">
                            <i class="bi bi-plus-circle"></i> Ajukan Aspirasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="history.php">
                            <i class="bi bi-clock-history"></i> Riwayat
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['nama']; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h3>Dashboard Siswa</h3>
                <p class="text-muted">Selamat datang, <?php echo $_SESSION['nama']; ?> (Kelas: <?php echo $_SESSION['kelas']; ?>)</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="input_aspirasi.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Ajukan Aspirasi Baru
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card total">
                    <i class="bi bi-inbox"></i>
                    <div class="number"><?php echo $total; ?></div>
                    <div>Total Aspirasi</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card menunggu">
                    <i class="bi bi-clock"></i>
                    <div class="number"><?php echo $menunggu; ?></div>
                    <div>Menunggu</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card proses">
                    <i class="bi bi-gear"></i>
                    <div class="number"><?php echo $proses; ?></div>
                    <div>Proses</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card selesai">
                    <i class="bi bi-check-circle"></i>
                    <div class="number"><?php echo $selesai; ?></div>
                    <div>Selesai</div>
                </div>
            </div>
        </div>

        <!-- Recent Aspirations -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Aspirasi Terbaru
            </div>
            <div class="card-body">
                <?php if (count($aspirasi) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kategori</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($aspirasi, 0, 5) as $item): ?>
                                <tr>
                                    <td>#<?php echo str_pad($item['id_aspirasi'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo $item['nama_kategori']; ?></td>
                                    <td><?php echo $item['lokasi']; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($item['status']); ?>">
                                            <?php echo $item['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($item['tanggal_dibuat'])); ?></td>
                                    <td>
                                        <a href="detail.php?id=<?php echo $item['id_aspirasi']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($aspirasi) > 5): ?>
                        <div class="text-center mt-3">
                            <a href="history.php" class="btn btn-outline-secondary">
                                Lihat Semua Aspirasi
                            </a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <h4 class="mt-3">Belum ada aspirasi</h4>
                        <p class="text-muted">Ajukan aspirasi pertama Anda terkait sarana sekolah</p>
                        <a href="input_aspirasi.php" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-circle"></i> Ajukan Aspirasi
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</body>
</html>