<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotSiswa();

$functions = new Functions();
$kategori = $functions->getKategori();

$success = false;
$error = '';
$editMode = false;
$aspirasi = null;

// Cek apakah dalam mode edit
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    // Ambil data aspirasi dan pastikan milik siswa ini
    $aspirasi = $functions->getAspirasiById($id);
    if ($aspirasi && $aspirasi['nis'] == $_SESSION['user_id']) {
        // Hanya boleh edit jika status belum Selesai
        if ($aspirasi['status'] != 'Selesai') {
            $editMode = true;
        } else {
            $error = 'Aspirasi dengan status Selesai tidak dapat diedit.';
        }
    } else {
        $error = 'Aspirasi tidak ditemukan atau bukan milik Anda.';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Proses upload bukti awal (jika ada file baru)
    $bukti_awal = null;
    if (isset($_FILES['bukti_awal']) && $_FILES['bukti_awal']['error'] == 0) {
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['bukti_awal']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Validasi tipe file
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = $_FILES['bukti_awal']['type'];
        
        // Validasi ukuran (maks 5MB)
        if ($_FILES['bukti_awal']['size'] > 5 * 1024 * 1024) {
            $error = 'Ukuran file maksimal 5MB.';
        } elseif (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['bukti_awal']['tmp_name'], $target_file)) {
                $bukti_awal = $file_name;
            } else {
                $error = 'Gagal mengupload file.';
            }
        } else {
            $error = 'Tipe file tidak diizinkan. Hanya JPG/PNG.';
        }
    }

    // Jika mode edit, update data yang ada
    if (isset($_POST['id_aspirasi'])) {
        $id_aspirasi = intval($_POST['id_aspirasi']);
        // Pastikan aspirasi milik siswa dan boleh diedit
        $existing = $functions->getAspirasiById($id_aspirasi);
        if (!$existing || $existing['nis'] != $_SESSION['user_id'] || $existing['status'] == 'Selesai') {
            $error = 'Anda tidak diizinkan mengedit aspirasi ini.';
        } else {
            // Siapkan data update (tanpa id_aspirasi karena akan dikirim terpisah)
            $data = [
                'id_kategori'  => $_POST['kategori'],
                'lokasi'       => $_POST['lokasi'],
                'deskripsi'    => $_POST['deskripsi'],
                'bukti_awal'   => $bukti_awal
            ];
            // Jika tidak upload file baru, gunakan bukti lama
            if ($bukti_awal === null) {
                $data['bukti_awal'] = $existing['bukti_awal']; // pertahankan yang lama
            }
            
            // Panggil fungsi update dengan dua argumen: id dan data
            if ($functions->updateAspirasi($id_aspirasi, $data)) {
                // Jika sukses, redirect ke detail
                header("Location: detail.php?id=$id_aspirasi&updated=1");
                exit();
            } else {
                $error = 'Gagal mengupdate aspirasi. Silakan coba lagi.';
            }
        }
    } else {
        // Mode insert baru
        $data = [
            'nis'         => $_SESSION['user_id'],
            'id_kategori' => $_POST['kategori'],
            'lokasi'      => $_POST['lokasi'],
            'deskripsi'   => $_POST['deskripsi'],
            'bukti_awal'  => $bukti_awal
        ];
        
        if ($functions->addAspirasi($data)) {
            $success = true;
        } else {
            $error = 'Gagal mengajukan aspirasi. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editMode ? 'Edit Aspirasi' : 'Ajukan Aspirasi'; ?> - Pengaduan Sarana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="bi <?php echo $editMode ? 'bi-pencil-square' : 'bi-plus-circle'; ?>"></i>
                            <?php echo $editMode ? 'Edit Aspirasi' : 'Ajukan Aspirasi Baru'; ?>
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
                            
                            <form method="POST" action="" enctype="multipart/form-data">
                                <?php if ($editMode): ?>
                                    <input type="hidden" name="id_aspirasi" value="<?php echo $aspirasi['id_aspirasi']; ?>">
                                <?php endif; ?>
                                
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
                                        <option value="<?php echo $kat['id_kategori']; ?>" 
                                            <?php if ($editMode && $aspirasi['id_kategori'] == $kat['id_kategori']) echo 'selected'; ?>>
                                            <?php echo $kat['nama_kategori']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="lokasi" class="form-label">Lokasi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                           placeholder="Contoh: Ruang Lab Komputer 1, Toilet Putri Lantai 2" required
                                           value="<?php echo $editMode ? htmlspecialchars($aspirasi['lokasi']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi Masalah <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="deskripsi" name="deskripsi" 
                                              rows="5" placeholder="Jelaskan secara detail masalah yang ditemukan..." required><?php echo $editMode ? htmlspecialchars($aspirasi['deskripsi']) : ''; ?></textarea>
                                    <div class="form-text">
                                        Jelaskan dengan jelas: apa masalahnya, kapan terjadi, dan dampaknya.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="bukti_awal" class="form-label">Bukti Foto <?php echo $editMode ? '(Kosongkan jika tidak diganti)' : '(Opsional)'; ?></label>
                                    <input type="file" class="form-control" id="bukti_awal" name="bukti_awal" accept="image/jpeg,image/png,image/jpg">
                                    <?php if ($editMode && !empty($aspirasi['bukti_awal'])): ?>
                                        <div class="mt-2">
                                            <small>Bukti saat ini: </small>
                                            <a href="../uploads/<?php echo $aspirasi['bukti_awal']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-file-earmark-image"></i> Lihat
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="form-text">Upload foto sebagai bukti pendukung (format: JPG, PNG, maksimal 5MB).</div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="<?php echo $editMode ? 'detail.php?id='.$aspirasi['id_aspirasi'] : 'dashboard.php'; ?>" class="btn btn-secondary me-md-2">
                                        <i class="bi bi-x-circle"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi <?php echo $editMode ? 'bi-save' : 'bi-send-check'; ?>"></i>
                                        <?php echo $editMode ? 'Simpan Perubahan' : 'Ajukan Aspirasi'; ?>
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>