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

    // Cek apakah customer ini punya transaksi di barang_keluar
    $stmt_check = $koneksi->prepare("SELECT COUNT(*) FROM barang_keluar WHERE id_customer = ?");
    $stmt_check->bind_param("s", $id);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        // Kalau ada transaksi, jangan hapus, kasih pesan error
        header("Location: " . BASE_URL . "/router.php?page=customer&status=hapus_gagal");
        exit;
    } else {
        // Kalau tidak ada transaksi, hapus customer
        $stmt = $koneksi->prepare("DELETE FROM customer WHERE id_customer = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $stmt->close();

        // Untuk bagian alert hapus data
        header("Location: " . BASE_URL . "/router.php?page=customer&status=hapus_sukses");
        // Untuk bagian alert hapus data
        exit;
    }
} else {
    header("Location: " . BASE_URL . "/router.php?page=customer");
    exit;
}
?>