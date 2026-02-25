<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

$functions = functions();
$siswa_list = $functions->getAllSiswa();
$admin_list = $functions->getAllAdmin();

$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-warning">
                <i class="bi bi-people"></i> Kelola Akun
            </h2>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show"><?php echo $success; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>

        <!-- Nav tabs -->
        <ul class="nav nav-tabs" id="akunTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="siswa-tab" data-bs-toggle="tab" data-bs-target="#siswa" type="button" role="tab">Akun Siswa</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab">Akun Admin</button>
            </li>
        </ul>

        <div class="tab-content" id="akunTabContent">
            <!-- Tab Siswa -->
            <div class="tab-pane fade show active" id="siswa" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Siswa</h5>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalSiswa" onclick="resetFormSiswa()">
                            <i class="bi bi-plus-circle"></i> Tambah Siswa
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>NIS</th>
                                        <th>Nama</th>
                                        <th>Kelas</th>
                                        <th>Email</th>
                                        <th>Terdaftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($siswa_list as $s): ?>
                                    <tr>
                                        <td><?php echo $s['nis']; ?></td>
                                        <td><?php echo $s['nama']; ?></td>
                                        <td><?php echo $s['kelas']; ?></td>
                                        <td><?php echo $s['email'] ?? '-'; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($s['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning" onclick="editSiswa(<?php echo htmlspecialchars(json_encode($s)); ?>)" data-bs-toggle="modal" data-bs-target="#modalSiswa">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <a href="aksi_akun.php?action=reset&type=siswa&id=<?php echo $s['nis']; ?>" class="btn btn-sm btn-outline-info" onclick="return confirm('Reset password untuk siswa ini? Password baru akan ditampilkan.');">
                                                <i class="bi bi-arrow-repeat"></i> Reset
                                            </a>
                                            <a href="aksi_akun.php?action=delete&type=siswa&id=<?php echo $s['nis']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus akun siswa ini?');">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Admin -->
            <div class="tab-pane fade" id="admin" role="tabpanel">
                <div class="card mt-3">
                    <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Admin</h5>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalAdmin" onclick="resetFormAdmin()">
                            <i class="bi bi-plus-circle"></i> Tambah Admin
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Last Login</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admin_list as $a): ?>
                                    <tr>
                                        <td><?php echo $a['id_admin']; ?></td>
                                        <td><?php echo $a['username']; ?></td>
                                        <td><?php echo $a['nama_lengkap']; ?></td>
                                        <td><?php echo $a['email'] ?? '-'; ?></td>
                                        <td><?php echo $a['last_login'] ? date('d/m/Y H:i', strtotime($a['last_login'])) : '-'; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-warning" onclick="editAdmin(<?php echo htmlspecialchars(json_encode($a)); ?>)" data-bs-toggle="modal" data-bs-target="#modalAdmin">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <a href="aksi_akun.php?action=reset&type=admin&id=<?php echo $a['id_admin']; ?>" class="btn btn-sm btn-outline-info" onclick="return confirm('Reset password admin ini? Password baru akan ditampilkan.');">
                                                <i class="bi bi-arrow-repeat"></i> Reset
                                            </a>
                                            <a href="aksi_akun.php?action=delete&type=admin&id=<?php echo $a['id_admin']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus akun admin ini?');">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Siswa -->
    <div class="modal fade" id="modalSiswa" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="aksi_akun.php">
                    <input type="hidden" name="action" id="siswa_action" value="add">
                    <input type="hidden" name="type" value="siswa">
                    <input type="hidden" name="old_nis" id="siswa_old_nis" value="">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="modalSiswaTitle">Tambah Siswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">NIS <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nis" id="siswa_nis" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="siswa_nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="kelas" id="siswa_kelas" placeholder="Contoh: XII RPL 1" maxlength="20" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="siswa_email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger" id="siswa_password_required">*</span></label>
                            <input type="password" class="form-control" name="password" id="siswa_password">
                            <small class="text-muted" id="siswa_password_note">Kosongkan jika tidak ingin mengubah (saat edit).</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Admin -->
    <div class="modal fade" id="modalAdmin" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="aksi_akun.php">
                    <input type="hidden" name="action" id="admin_action" value="add">
                    <input type="hidden" name="type" value="admin">
                    <input type="hidden" name="old_id" id="admin_old_id" value="">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="modalAdminTitle">Tambah Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" id="admin_username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_lengkap" id="admin_nama_lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="admin_email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger" id="admin_password_required">*</span></label>
                            <input type="password" class="form-control" name="password" id="admin_password">
                            <small class="text-muted" id="admin_password_note">Kosongkan jika tidak ingin mengubah (saat edit).</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function resetFormSiswa() {
            document.getElementById('siswa_action').value = 'add';
            document.getElementById('modalSiswaTitle').innerText = 'Tambah Siswa';
            document.getElementById('siswa_old_nis').value = '';
            document.getElementById('siswa_nis').value = '';
            document.getElementById('siswa_nis').readOnly = false;
            document.getElementById('siswa_nama').value = '';
            document.getElementById('siswa_kelas').value = '';
            document.getElementById('siswa_email').value = '';
            document.getElementById('siswa_password').value = '';
            document.getElementById('siswa_password').required = true;
            document.getElementById('siswa_password_required').innerText = '*';
            document.getElementById('siswa_password_note').innerText = 'Kosongkan jika tidak ingin mengubah (saat edit).';
        }

        function editSiswa(data) {
            document.getElementById('siswa_action').value = 'edit';
            document.getElementById('modalSiswaTitle').innerText = 'Edit Siswa';
            document.getElementById('siswa_old_nis').value = data.nis;
            document.getElementById('siswa_nis').value = data.nis;
            document.getElementById('siswa_nis').readOnly = true; // NIS tidak diubah
            document.getElementById('siswa_nama').value = data.nama;
            document.getElementById('siswa_kelas').value = data.kelas;
            document.getElementById('siswa_email').value = data.email || '';
            document.getElementById('siswa_password').value = '';
            document.getElementById('siswa_password').required = false;
            document.getElementById('siswa_password_required').innerText = '';
            document.getElementById('siswa_password_note').innerText = 'Kosongkan jika tidak ingin mengubah password.';
        }

        function resetFormAdmin() {
            document.getElementById('admin_action').value = 'add';
            document.getElementById('modalAdminTitle').innerText = 'Tambah Admin';
            document.getElementById('admin_old_id').value = '';
            document.getElementById('admin_username').value = '';
            document.getElementById('admin_nama_lengkap').value = '';
            document.getElementById('admin_email').value = '';
            document.getElementById('admin_password').value = '';
            document.getElementById('admin_password').required = true;
            document.getElementById('admin_password_required').innerText = '*';
            document.getElementById('admin_password_note').innerText = 'Kosongkan jika tidak ingin mengubah (saat edit).';
        }

        function editAdmin(data) {
            document.getElementById('admin_action').value = 'edit';
            document.getElementById('modalAdminTitle').innerText = 'Edit Admin';
            document.getElementById('admin_old_id').value = data.id_admin;
            document.getElementById('admin_username').value = data.username;
            document.getElementById('admin_nama_lengkap').value = data.nama_lengkap;
            document.getElementById('admin_email').value = data.email || '';
            document.getElementById('admin_password').value = '';
            document.getElementById('admin_password').required = false;
            document.getElementById('admin_password_required').innerText = '';
            document.getElementById('admin_password_note').innerText = 'Kosongkan jika tidak ingin mengubah password.';
        }
    </script>
    <?php include '../includes/footer.php'; ?>
</body>
</html>