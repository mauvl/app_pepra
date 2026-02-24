<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$nama = isset($_SESSION['nama']) ? $_SESSION['nama'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengaduan Sarana Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand text-warning fw-bold" href="<?php echo ($role == 'admin') ? 'admin/dashboard.php' : (($role == 'siswa') ? 'siswa/dashboard.php' : 'index.php'); ?>">
                <i class="bi bi-building"></i> Pengaduan Sarana
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($role == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../admin/aspirasi.php">
                                <i class="bi bi-list-check"></i> Aspirasi
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo $nama; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                        
                    <?php elseif ($role == 'siswa'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../siswa/dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../siswa/input_aspirasi.php">
                                <i class="bi bi-plus-circle"></i> Ajukan Aspirasi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="siswa/history.php">
                                <i class="bi bi-clock-history"></i> Riwayat
                            </a>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">
                                <i class="bi bi-person-circle"></i> <?php echo $nama; ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                        
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php?role=admin">Login Admin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php?role=siswa">Login Siswa</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">