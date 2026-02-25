<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

$functions = functions();
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

if (!$action || !$type) {
    $_SESSION['error'] = 'Aksi tidak valid.';
    header('Location: kelola_akun.php');
    exit();
}

// Fungsi bantu untuk membersihkan input
function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

switch ($action) {
    case 'add':
        if ($type == 'siswa') {
            $nis = cleanInput($_POST['nis'] ?? '');
            $nama = cleanInput($_POST['nama'] ?? '');
            $kelas = cleanInput($_POST['kelas'] ?? '');
            $email = !empty($_POST['email']) ? cleanInput($_POST['email']) : null;
            $password = $_POST['password'] ?? '';

            // Validasi panjang kelas (maksimal 20 karakter, sesuai struktur baru)
            if (strlen($kelas) > 20) {
                $_SESSION['error'] = 'Kelas maksimal 20 karakter.';
                header('Location: kelola_akun.php');
                exit();
            }

            if (empty($nis) || empty($nama) || empty($kelas) || empty($password)) {
                $_SESSION['error'] = 'Semua field wajib diisi.';
                header('Location: kelola_akun.php');
                exit();
            }

            if (!is_numeric($nis)) {
                $_SESSION['error'] = 'NIS harus berupa angka.';
                header('Location: kelola_akun.php');
                exit();
            }

            if ($functions->getSiswaByNis($nis)) {
                $_SESSION['error'] = 'NIS sudah terdaftar.';
                header('Location: kelola_akun.php');
                exit();
            }

            $data = [
                'nis' => $nis,
                'nama' => $nama,
                'kelas' => $kelas,
                'email' => $email,
                'password' => $password
            ];

            if ($functions->addSiswa($data)) {
                $_SESSION['success'] = 'Siswa berhasil ditambahkan.';
            } else {
                $_SESSION['error'] = 'Gagal menambahkan siswa.';
            }
        } elseif ($type == 'admin') {
            $username = cleanInput($_POST['username'] ?? '');
            $nama_lengkap = cleanInput($_POST['nama_lengkap'] ?? '');
            $email = !empty($_POST['email']) ? cleanInput($_POST['email']) : null;
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($nama_lengkap) || empty($password)) {
                $_SESSION['error'] = 'Semua field wajib diisi.';
                header('Location: kelola_akun.php');
                exit();
            }

            // Cek username unik
            $admins = $functions->getAllAdmin();
            foreach ($admins as $a) {
                if ($a['username'] == $username) {
                    $_SESSION['error'] = 'Username sudah digunakan.';
                    header('Location: kelola_akun.php');
                    exit();
                }
            }

            $data = [
                'username' => $username,
                'nama_lengkap' => $nama_lengkap,
                'email' => $email,
                'password' => $password
            ];

            if ($functions->addAdmin($data)) {
                $_SESSION['success'] = 'Admin berhasil ditambahkan.';
            } else {
                $_SESSION['error'] = 'Gagal menambahkan admin.';
            }
        }
        break;

    case 'edit':
        if ($type == 'siswa') {
            $old_nis = cleanInput($_POST['old_nis'] ?? '');
            $nis = cleanInput($_POST['nis'] ?? '');
            $nama = cleanInput($_POST['nama'] ?? '');
            $kelas = cleanInput($_POST['kelas'] ?? '');
            $email = !empty($_POST['email']) ? cleanInput($_POST['email']) : null;
            $password = $_POST['password'] ?? '';

            // Validasi panjang kelas
            if (strlen($kelas) > 20) {
                $_SESSION['error'] = 'Kelas maksimal 20 karakter.';
                header('Location: kelola_akun.php');
                exit();
            }

            if (empty($old_nis) || empty($nama) || empty($kelas)) {
                $_SESSION['error'] = 'Data tidak lengkap.';
                header('Location: kelola_akun.php');
                exit();
            }

            $data = [
                'nama' => $nama,
                'kelas' => $kelas,
                'email' => $email
            ];
            if (!empty($password)) {
                $data['password'] = $password;
            }

            if ($functions->updateSiswa($old_nis, $data)) {
                $_SESSION['success'] = 'Data siswa berhasil diupdate.';
            } else {
                $_SESSION['error'] = 'Gagal mengupdate data siswa.';
            }
        } elseif ($type == 'admin') {
            $old_id = cleanInput($_POST['old_id'] ?? '');
            $username = cleanInput($_POST['username'] ?? '');
            $nama_lengkap = cleanInput($_POST['nama_lengkap'] ?? '');
            $email = !empty($_POST['email']) ? cleanInput($_POST['email']) : null;
            $password = $_POST['password'] ?? '';

            if (empty($old_id) || empty($username) || empty($nama_lengkap)) {
                $_SESSION['error'] = 'Data tidak lengkap.';
                header('Location: kelola_akun.php');
                exit();
            }

            $data = [
                'username' => $username,
                'nama_lengkap' => $nama_lengkap,
                'email' => $email
            ];
            if (!empty($password)) {
                $data['password'] = $password;
            }

            if ($functions->updateAdmin($old_id, $data)) {
                $_SESSION['success'] = 'Data admin berhasil diupdate.';
            } else {
                $_SESSION['error'] = 'Gagal mengupdate data admin.';
            }
        }
        break;

    case 'delete':
        $id = cleanInput($_GET['id'] ?? '');
        if (empty($id)) {
            $_SESSION['error'] = 'ID tidak valid.';
            header('Location: kelola_akun.php');
            exit();
        }

        if ($type == 'siswa') {
            if ($functions->deleteSiswa($id)) {
                $_SESSION['success'] = 'Siswa berhasil dihapus.';
            } else {
                $_SESSION['error'] = 'Gagal menghapus siswa.';
            }
        } elseif ($type == 'admin') {
            if ($id == $_SESSION['user_id']) {
                $_SESSION['error'] = 'Tidak dapat menghapus akun sendiri.';
                header('Location: kelola_akun.php');
                exit();
            }
            if ($functions->deleteAdmin($id)) {
                $_SESSION['success'] = 'Admin berhasil dihapus.';
            } else {
                $_SESSION['error'] = 'Gagal menghapus admin.';
            }
        }
        break;

    case 'reset':
        $id = cleanInput($_GET['id'] ?? '');
        if (empty($id)) {
            $_SESSION['error'] = 'ID tidak valid.';
            header('Location: kelola_akun.php');
            exit();
        }

        $newPassword = $functions->generateRandomPassword(8);

        if ($type == 'siswa') {
            if ($functions->resetPasswordSiswa($id, $newPassword)) {
                $_SESSION['success'] = "Password siswa berhasil direset. Password baru: <strong>$newPassword</strong>";
            } else {
                $_SESSION['error'] = 'Gagal mereset password.';
            }
        } elseif ($type == 'admin') {
            if ($id == $_SESSION['user_id']) {
                $_SESSION['error'] = 'Tidak dapat mereset password sendiri. Gunakan fitur ganti password di profil.';
                header('Location: kelola_akun.php');
                exit();
            }
            if ($functions->resetPasswordAdmin($id, $newPassword)) {
                $_SESSION['success'] = "Password admin berhasil direset. Password baru: <strong>$newPassword</strong>";
            } else {
                $_SESSION['error'] = 'Gagal mereset password.';
            }
        }
        break;

    default:
        $_SESSION['error'] = 'Aksi tidak dikenali.';
        break;
}

header('Location: kelola_akun.php');
exit();