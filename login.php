<?php
session_start();
require_once 'includes/functions.php';

$error = '';
$role = isset($_GET['role']) ? $_GET['role'] : 'siswa';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($role == 'admin') {
        if (functions()->loginAdmin($_POST['username'], $_POST['password'])) {
            header('Location: admin/dashboard.php');
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        if (functions()->loginSiswa($_POST['nis'], $_POST['password'])) {
            header('Location: siswa/dashboard.php');
            exit();
        } else {
            $error = 'NIS atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pengaduan Sarana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h4 class="mb-0">
                            <i class="bi bi-lock"></i> Login 
                            <?php echo $role == 'admin' ? 'Admin' : 'Siswa'; ?>
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <?php if ($role == 'admin'): ?>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <label for="nis" class="form-label">NIS</label>
                                    <input type="text" class="form-control" id="nis" name="nis" required>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Login
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-house"></i> Kembali
                                </a>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Login sebagai 
                                <?php if ($role == 'admin'): ?>
                                    <a href="login.php?role=siswa">Siswa</a>
                                <?php else: ?>
                                    <a href="login.php?role=admin">Admin</a>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</body>
</html>