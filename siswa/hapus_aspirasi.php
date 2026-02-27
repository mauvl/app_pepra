<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotSiswa();

if (!isset($_GET['id'])) {
    header('Location: history.php');
    exit;
}

$id = $_GET['id'];
$nis = $_SESSION['user_id'];

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pastikan aspirasi milik siswa dan masih berstatus Menunggu
$check = $conn->prepare("SELECT status FROM aspirasi WHERE id_aspirasi = ? AND nis = ?");
$check->bind_param("is", $id, $nis);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk menghapus aspirasi ini.";
    header('Location: history.php');
    exit;
}

$row = $result->fetch_assoc();
if ($row['status'] != 'Menunggu') {
    $_SESSION['error'] = "Aspirasi dengan status {$row['status']} tidak dapat dihapus.";
    header('Location: history.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM aspirasi WHERE id_aspirasi = ? AND nis = ?");
$stmt->bind_param("is", $id, $nis);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['success'] = "Aspirasi berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus aspirasi.";
}

header('Location: history.php');
exit;