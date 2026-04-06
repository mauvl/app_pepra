<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

$functions = new Functions();
$stats = $functions->getStats();

// Filter dan urutkan kategori hanya yang memiliki aspirasi (>0)
$kategoriTerfilter = array_filter($stats['kategori'], function($k) {
    return $k['jumlah'] > 0;
});
usort($kategoriTerfilter, function($a, $b) {
    return $b['jumlah'] - $a['jumlah'];
});

// Hitung insight tambahan
$totalMenungguProses = ($stats['Menunggu'] ?? 0) + ($stats['Proses'] ?? 0);
$persenSelesai = $stats['total'] > 0 ? round(($stats['Selesai'] ?? 0) / $stats['total'] * 100, 1) : 0;
$kategoriTerbanyak = !empty($kategoriTerfilter) ? $kategoriTerfilter[0] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Pengaduan Sarana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="../assets/logo.png" alt="Logo" class="navbar-logo me-2">
                PEPRA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="aspirasi.php">
                            <i class="bi-list-check"></i> Aspirasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kelola_akun.php">
                            <i class="bi bi-people"></i> Kelola Akun
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['nama']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h2 class="mb-4">Dashboard Admin</h2>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card total">
                    <i class="bi bi-inbox"></i>
                    <div class="number"><?php echo $stats['total']; ?></div>
                    <div>Total Aspirasi</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card menunggu">
                    <i class="bi bi-clock"></i>
                    <div class="number"><?php echo $stats['Menunggu'] ?? 0; ?></div>
                    <div>Menunggu</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card proses">
                    <i class="bi bi-gear"></i>
                    <div class="number"><?php echo $stats['Proses'] ?? 0; ?></div>
                    <div>Proses</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card selesai">
                    <i class="bi bi-check-circle"></i>
                    <div class="number"><?php echo $stats['Selesai'] ?? 0; ?></div>
                    <div>Selesai</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Kiri: Tabel Kategori (hanya yang ada data) + Grafik -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-graph-up"></i> Statistik per Kategori
                        <span class="badge bg-secondary ms-2">Hanya kategori dengan aspirasi</span>
                    </div>
                    <div class="card-body">
                        <?php if (count($kategoriTerfilter) > 0): ?>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kategori</th>
                                        <th>Jumlah Aspirasi</th>
                                        <th>Persentase</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kategoriTerfilter as $kategori): 
                                        $persentase = $stats['total'] > 0 ? 
                                            round(($kategori['jumlah'] / $stats['total']) * 100, 1) : 0;
                                    ?>
                                        <tr>
                                            <td>
                                                <?php echo $kategori['nama_kategori']; ?>
                                                <?php if ($kategoriTerbanyak && $kategori['nama_kategori'] === $kategoriTerbanyak['nama_kategori']): ?>
                                                    <span class="badge bg-warning text-dark ms-2">Terbanyak</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $kategori['jumlah']; ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: <?php echo $persentase; ?>%">
                                                        <?php echo $persentase; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="mt-4">
                                <h6><i class="bi bi-bar-chart-steps"></i> Visualisasi Jumlah Aspirasi</h6>
                                <div style="max-height: 280px;">
                                    <canvas id="kategoriChart"></canvas>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-info-circle"></i> Belum ada aspirasi yang masuk di semua kategori.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Kanan: Card dengan urutan: 1. Tips Admin, 2. Informasi Status, 3. Ringkasan Cepat -->
            <div class="col-md-4">
                <!-- Tips Admin -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <i class="bi bi-chat-dots"></i> Tips untuk Admin
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li>Segera tindak lanjuti aspirasi berstatus <strong>Menunggu</strong>.</li>
                            <li>Gunakan fitur <strong>Kelola Akun</strong> untuk menambah admin baru.</li>
                            <li>Pastikan feedback diberikan pada aspirasi yang selesai.</li>
                            <?php if ($totalMenungguProses > 5): ?>
                                <li class="text-warning">⚠️ Ada <?php echo $totalMenungguProses; ?> aspirasi aktif, prioritaskan penanganan.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Informasi Status -->
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> Informasi Status
                    </div>
                    <div class="card-body">
                        <small>
                            <p><strong>Status Menunggu:</strong> Aspirasi baru, belum ditinjau</p>
                            <p><strong>Status Proses:</strong> Sedang dalam penanganan</p>
                            <p><strong>Status Selesai:</strong> Sudah ditangani atau tidak valid</p>
                            <p>Klik pada status di halaman <a href="aspirasi.php">Aspirasi</a> untuk melihat detail dan memberikan feedback.</p>
                        </small>
                    </div>
                </div>

                <!-- Ringkasan Cepat (warna netral, tidak mencolok) -->
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-lightbulb"></i> Ringkasan Cepat
                    </div>
                    <div class="card-body">
                        <p><strong>📊 Kategori Terbanyak:</strong><br>
                        <?php if ($kategoriTerbanyak): ?>
                            <span class="fs-6 fw-bold"><?php echo $kategoriTerbanyak['nama_kategori']; ?></span> 
                            (<?php echo $kategoriTerbanyak['jumlah']; ?> aspirasi)
                        <?php else: ?>-<?php endif; ?>
                        </p>
                        <p><strong>⏳ Aspirasi Aktif:</strong><br>
                        <?php echo $totalMenungguProses; ?> aspirasi (Menunggu + Proses)</p>
                        <p><strong>✅ Tingkat Penyelesaian:</strong><br>
                        <?php echo $persenSelesai; ?>% dari total aspirasi telah selesai</p>
                        <a href="aspirasi.php" class="btn btn-primary mt-2">
                            <i class="bi bi-list-check"></i> Kelola Semua Aspirasi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if (count($kategoriTerfilter) > 0): ?>
        const ctx = document.getElementById('kategoriChart').getContext('2d');
        const labels = <?php echo json_encode(array_column($kategoriTerfilter, 'nama_kategori')); ?>;
        const dataValues = <?php echo json_encode(array_column($kategoriTerfilter, 'jumlah')); ?>;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Aspirasi',
                    data: dataValues,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.raw} aspirasi` } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, title: { display: true, text: 'Jumlah' } },
                    x: { title: { display: true, text: 'Kategori' } }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>