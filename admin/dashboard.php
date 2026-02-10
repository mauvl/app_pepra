<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

$functions = new Functions();
$stats = $functions->getStats();
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
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-building"></i> Admin Panel
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
                        <a class="nav-link" href="filter.php">
                            <i class="bi bi-funnel"></i> Filter
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

    <!-- Main Content -->
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

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-graph-up"></i> Statistik per Kategori
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Jumlah Aspirasi</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['kategori'] as $kategori): 
                                    $persentase = $stats['total'] > 0 ? 
                                        round(($kategori['jumlah'] / $stats['total']) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><?php echo $kategori['nama_kategori']; ?></td>
                                    <td><?php echo $kategori['jumlah']; ?></td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" 
                                                 style="width: <?php echo $persentase; ?>%">
                                                <?php echo $persentase; ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mt-3">
                    <div class="card-header">
                        <i class="bi bi-info-circle"></i> Informasi
                    </div>
                    <div class="card-body">
                        <small>
                            <p><strong>Status Menunggu:</strong> Aspirasi baru, belum ditinjau</p>
                            <p><strong>Status Proses:</strong> Sedang dalam penanganan</p>
                            <p><strong>Status Selesai:</strong> Sudah ditangani atau tidak valid</p>
                            <p>Klik pada status untuk melihat detail dan memberikan feedback</p>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>