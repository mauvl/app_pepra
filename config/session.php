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

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

function redirectIfNotSiswa() {
    if (!isSiswa()) {
        header('Location: ../index.php');
        exit();
    }
}
?>