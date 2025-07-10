<?php
// Koneksi ke database
include __DIR__ . '/../../koneksi.php';

// Define BASE_URL jika belum
if (!defined('BASE_URL')) {
    define('BASE_URL', '/abhipraya-cipta-bersama');
}

// Mulai session
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/router.php?page=login");
    exit;
}

if (isset($_GET['id_barangkeluar'])) {
    $id_barangkeluar = (int) $_GET['id_barangkeluar'];

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // Ambil data barang keluar
        $stmt = $koneksi->prepare("SELECT idbarang, jumlah_keluar FROM barang_keluar WHERE id_barangkeluar = ?");
        $stmt->bind_param("i", $id_barangkeluar);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Data barang keluar tidak ditemukan.");
        }

        $data = $result->fetch_assoc();
        $idbarang = $data['idbarang'];
        $jumlah_keluar = (int) $data['jumlah_keluar'];

        // Update stok barang (tambah kembali jumlah_keluar)
        $stmt_update = $koneksi->prepare("UPDATE databarang SET stockbarang = stockbarang + ? WHERE idbarang = ?");
        $stmt_update->bind_param("is", $jumlah_keluar, $idbarang);
        if (!$stmt_update->execute()) {
            throw new Exception("Gagal memperbarui stok barang.");
        }

        // Hapus data barang keluar
        $stmt_delete = $koneksi->prepare("DELETE FROM barang_keluar WHERE id_barangkeluar = ?");
        $stmt_delete->bind_param("i", $id_barangkeluar);
        if (!$stmt_delete->execute()) {
            throw new Exception("Gagal menghapus data barang keluar.");
        }

        // Commit transaksi
        mysqli_commit($koneksi);

        $_SESSION['success'] = "Data barang keluar berhasil dihapus.";
        header("Location: " . BASE_URL . "/router.php?page=barangkeluar&status=hapus_sukses");
        exit;

    } catch (Exception $e) {
        // Rollback jika terjadi error
        mysqli_rollback($koneksi);
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        header("Location: " . BASE_URL . "/router.php?page=barangkeluar");
        exit;
    }

} else {
    $_SESSION['error'] = "ID barang keluar tidak ditemukan.";
    header("Location: " . BASE_URL . "/router.php?page=barangkeluar");
    exit;
}
