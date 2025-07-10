<?php
// koneksi ke database
include __DIR__ . '/../../koneksi.php';

// Mulai session jika belum
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/router.php?page=login");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Contoh cek tambahan jika dibutuhkan (tidak ada di user.php, jadi di-skip)
    // Langsung hapus user
    $stmt = $koneksi->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Redirect dengan status sukses
    header("Location: " . BASE_URL . "/router.php?page=user&status=hapus_sukses");
    exit;
} else {
    header("Location: " . BASE_URL . "/router.php?page=user");
    exit;
}
