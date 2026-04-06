<?php
session_start();
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: siswa/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengaduan Sarana Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Memastikan ikon pada tombol Login Admin berwarna putih */
        .btn-primary i {
            color: white !important;
        }
        /* Opsional: jika ingin ikon pada tombol outline tetap biru (tidak diubah) */
        .btn-outline-primary i {
            color: #0d6efd; /* warna biru default bootstrap */
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="assets/logo.png" alt="Logo" class="navbar-logo me-2">
                PEPRA
            </a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row align-items-center justify-content-center text-center text-md-start">
            <div class="col-md-6 mb-4 mb-md-0">
                <h1 class="display-4 fw-bold text-primary mb-4">
                    Sistem Pengaduan Sarana Sekolah
                </h1>
                <p class="lead mb-4">
                    Platform digital untuk menyampaikan aspirasi dan pengaduan 
                    terkait sarana dan prasarana sekolah secara efektif dan efisien.
                </p>
                <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                    <a href="login.php?role=admin" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-person-badge"></i> Login Admin
                    </a>
                    <a href="login.php?role=siswa" class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-person"></i> Login Siswa
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <img src="assets/college.png" 
                     alt="School Building" 
                     class="img-fluid rounded">
            </div>
        </div>

        <div class="row mt-5 pt-5 justify-content-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-chat-dots display-4 text-primary mb-3"></i>
                        <h4 class="card-title">Ajukan Aspirasi</h4>
                        <p class="card-text">Sampaikan keluhan atau masukan terkait fasilitas sekolah dengan mudah.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-clock-history display-4 text-primary mb-3"></i>
                        <h4 class="card-title">Pantau Progress</h4>
                        <p class="card-text">Lacak status pengaduan Anda dari awal hingga penyelesaian.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-check-circle display-4 text-primary mb-3"></i>
                        <h4 class="card-title">Feedback Langsung</h4>
                        <p class="card-text">Dapatkan respon dan tindak lanjut dari admin secara transparan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Sistem Pengaduan Sarana Sekolah. Maulida Safa Azzahra.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</body>
</html>