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
    $id_barang = $_GET['id'];

    // Hapus relasi terkait di barang_keluar
    $stmt = $koneksi->prepare("DELETE FROM barang_keluar WHERE idbarang = ?");
    $stmt->bind_param("s", $id_barang);
    $stmt->execute();
    $stmt->close();

    // Hapus relasi terkait di barang_masuk (jika diperlukan)
    $stmt = $koneksi->prepare("DELETE FROM barang_masuk WHERE idbarang = ?");
    $stmt->bind_param("s", $id_barang);
    $stmt->execute();
    $stmt->close();

    // Hapus dari tabel utama databarang
    $stmt = $koneksi->prepare("DELETE FROM databarang WHERE idbarang = ?");
    $stmt->bind_param("s", $id_barang);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $_SESSION['success'] = "Barang berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus barang.";
    }

    $stmt->close();

    header("Location: " . BASE_URL . "/router.php?page=databarang&status=hapus_sukses");
    exit;
} else {
    $_SESSION['error'] = "ID barang tidak ditemukan.";
    header("Location: " . BASE_URL . "/router.php?page=databarang");
    exit;
}
?>
