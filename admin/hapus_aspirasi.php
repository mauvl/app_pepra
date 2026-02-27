<?php
require_once '../config/session.php';
require_once '../includes/functions.php';
redirectIfNotAdmin();

if (!isset($_GET['id'])) {
    header('Location: aspirasi.php');
    exit;
}

$id = $_GET['id'];

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("DELETE FROM aspirasi WHERE id_aspirasi = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $_SESSION['success'] = "Aspirasi berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus aspirasi.";
}

header('Location: aspirasi.php');
exit;