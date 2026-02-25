<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function isSiswa() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'siswa';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Anda harus login terlebih dahulu!';
        header('Location: ../login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error'] = 'Akses ditolak! Halaman ini hanya untuk admin.';
        header('Location: ../index.php');
        exit();
    }
}

function requireSiswa() {
    requireLogin();
    if (!isSiswa()) {
        $_SESSION['error'] = 'Akses ditolak! Halaman ini hanya untuk siswa.';
        header('Location: ../index.php');
        exit();
    }
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function showFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        
        unset($_SESSION['flash']);
    }
}
?>