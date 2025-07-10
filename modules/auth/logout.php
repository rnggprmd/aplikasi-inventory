<?php
// Cegah akses langsung jika BASE_URL belum didefinisikan
if (!defined('BASE_URL')) {
    die('Akses langsung tidak diizinkan');
}

// Mulai session kalau belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_start();
$_SESSION['toast'] = "Anda berhasil logout.";
// Hancurkan session semua data login
session_destroy();

// Redirect ke halaman login via router.php dan BASE_URL
header("Location: " . BASE_URL . "/router.php?page=login");
exit;
