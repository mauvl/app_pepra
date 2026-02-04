<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotSiswa();

$kategori = getKategori();
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'nis' => $_SESSION['user_id'],
        'id_kategori' => $_POST['kategori'],
        'lokasi' => $_POST['lokasi'],
        'deskripsi' => $_POST['deskripsi']
    ];
    
    if (addAspirasi($data)) {
        $success = true;
    } else {
        $error = 'Gagal mengajukan aspirasi. Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Aspirasi - Pengaduan Sarana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation (sama seperti dashboard) -->
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="bi bi-plus-circle"></i> Ajukan Aspirasi Baru
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <h5><i class="bi bi-check-circle"></i> Aspirasi berhasil diajukan!</h5>
                                <p>Admin akan meninjau aspirasi Anda. Anda dapat melacak progress di halaman riwayat.</p>
                                <div class="mt-3">
                                    <a href="dashboard.php" class="btn btn-primary">
                                        <i class="bi bi-speedometer2"></i> Kembali ke Dashboard
                                    </a>
                                    <a href="history.php" class="btn btn-outline-primary">
                                        <i class="bi bi-clock-history"></i> Lihat Riwayat
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Nama Pengaju</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo $_SESSION['nama']; ?> (<?php echo $_SESSION['kelas']; ?>)" 
                                           disabled>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-select" id="kategori" name="kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($kategori as $kat): ?>
                                        <option value="<?php echo $kat['id_kategori']; ?>">
                                            <?php echo $kat['nama_kategori']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="lokasi" class="form-label">Lokasi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                           placeholder="Contoh: Ruang Lab Komputer 1, Toilet Putri Lantai 2" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi Masalah <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" 
                                              rows="5" placeholder="Jelaskan secara detail masalah yang ditemukan..." required></textarea>
                                    <div class="form-text">
                                        Jelaskan dengan jelas: apa masalahnya, kapan terjadi, dan dampaknya.
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="dashboard.php" class="btn btn-secondary me-md-2">
                                        <i class="bi bi-x-circle"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send-check"></i> Ajukan Aspirasi
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>