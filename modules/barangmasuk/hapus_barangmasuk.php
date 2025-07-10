<?php
// Koneksi ke database
include __DIR__ . '/../../koneksi.php';

// Define BASE_URL jika belum ada
if (!defined('BASE_URL')) {
    define('BASE_URL', '/abhipraya-cipta-bersama');
}

// Mulai session
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/router.php?page=login");
    exit;
}

if (isset($_GET['id_barangmasuk'])) {
    $id_barangmasuk = $_GET['id_barangmasuk'];

    // Ambil data barang masuk
    $stmt = $koneksi->prepare("SELECT jumlah_masuk, idbarang FROM barang_masuk WHERE id_barangmasuk = ?");
    $stmt->bind_param("i", $id_barangmasuk);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Data barang masuk tidak ditemukan.";
        header("Location: " . BASE_URL . "/router.php?page=barangmasuk");
        exit;
    }

    $data = $result->fetch_assoc();
    $jumlah_masuk = (int) $data['jumlah_masuk'];
    $idbarang = $data['idbarang'];

    // Cek apakah idbarang digunakan di barang_keluar
    $cek_keluar = $koneksi->prepare("SELECT COUNT(*) FROM barang_keluar WHERE idbarang = ?");
    $cek_keluar->bind_param("s", $idbarang);
    $cek_keluar->execute();
    $cek_keluar->bind_result($count_keluar);
    $cek_keluar->fetch();
    $cek_keluar->close();

    if ($count_keluar > 0) {
        header("Location: " . BASE_URL . "/router.php?page=barangmasuk&status=hapus_gagal");
        exit;
    }

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // Ambil stok lama
        $stmt_stok = $koneksi->prepare("SELECT stockbarang FROM databarang WHERE idbarang = ?");
        $stmt_stok->bind_param("s", $idbarang);
        $stmt_stok->execute();
        $stok_result = $stmt_stok->get_result();

        if ($stok_result->num_rows === 0) {
            throw new Exception("Stok barang tidak ditemukan.");
        }

        $stok_data = $stok_result->fetch_assoc();
        $stok_lama = (int) $stok_data['stockbarang'];
        $stok_baru = $stok_lama - $jumlah_masuk;

        if ($stok_baru < 0) {
            throw new Exception("Stok akan menjadi negatif.");
        }

        // Hapus data barang masuk
        $hapus = $koneksi->prepare("DELETE FROM barang_masuk WHERE id_barangmasuk = ?");
        $hapus->bind_param("i", $id_barangmasuk);
        $hapus->execute();

        // Update stok barang
        $update = $koneksi->prepare("UPDATE databarang SET stockbarang = ? WHERE idbarang = ?");
        $update->bind_param("is", $stok_baru, $idbarang);
        $update->execute();

        // Commit transaksi
        mysqli_commit($koneksi);

        $_SESSION['success'] = "Data barang masuk berhasil dihapus.";
        header("Location: " . BASE_URL . "/router.php?page=barangmasuk&status=hapus_sukses");
        exit;

    } catch (Exception $e) {
        // Rollback jika terjadi error
        mysqli_rollback($koneksi);
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        header("Location: " . BASE_URL . "/router.php?page=barangmasuk");
        exit;
    }

} else {
    $_SESSION['error'] = "ID barang masuk tidak ditemukan.";
    header("Location: " . BASE_URL . "/router.php?page=barangmasuk");
    exit;
}