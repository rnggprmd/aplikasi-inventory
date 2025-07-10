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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_barangkeluar'], $_POST['jumlah_keluar'], $_POST['id_customer'], $_POST['waktu'])) {
        $id_barangkeluar = (int) $_POST['id_barangkeluar'];
        $jumlah_baru = (int) $_POST['jumlah_keluar'];
        $id_customer = $_POST['id_customer'];
        $waktu_input = $_POST['waktu'];
        $waktu_sql = date('Y-m-d H:i:s', strtotime($waktu_input));

        mysqli_begin_transaction($koneksi);

        try {
            // Ambil data lama dari barang_keluar
            $stmt = $koneksi->prepare("SELECT jumlah_keluar, idbarang FROM barang_keluar WHERE id_barangkeluar = ?");
            $stmt->bind_param("i", $id_barangkeluar);
            $stmt->execute();
            $result_old = $stmt->get_result();

            if ($result_old->num_rows === 0) {
                throw new Exception("Data lama tidak ditemukan.");
            }

            $old_data = $result_old->fetch_assoc();
            $jumlah_lama = (int) $old_data['jumlah_keluar'];
            $idbarang = $old_data['idbarang'];

            $selisih = $jumlah_lama - $jumlah_baru;

            // Update barang_keluar
            $stmt_update = $koneksi->prepare("UPDATE barang_keluar SET jumlah_keluar = ?, id_customer = ?, waktu = ? WHERE id_barangkeluar = ?");
            $stmt_update->bind_param("issi", $jumlah_baru, $id_customer, $waktu_sql, $id_barangkeluar);

            if (!$stmt_update->execute()) {
                throw new Exception("Gagal memperbarui data barang keluar.");
            }

            // Ambil stok lama
            $stmt_stok = $koneksi->prepare("SELECT stockbarang FROM databarang WHERE idbarang = ?");
            $stmt_stok->bind_param("s", $idbarang);
            $stmt_stok->execute();
            $result_stok = $stmt_stok->get_result();

            if ($result_stok->num_rows === 0) {
                throw new Exception("Data stok tidak ditemukan.");
            }

            $stok_data = $result_stok->fetch_assoc();
            $stok_lama = (int) $stok_data['stockbarang'];
            $stok_baru = $stok_lama + $selisih;

            if ($stok_baru < 0) {
                throw new Exception("Stok menjadi negatif setelah perubahan jumlah.");
            }

            // Update stok barang
            $stmt_update_stok = $koneksi->prepare("UPDATE databarang SET stockbarang = ? WHERE idbarang = ?");
            $stmt_update_stok->bind_param("is", $stok_baru, $idbarang);

            if (!$stmt_update_stok->execute()) {
                throw new Exception("Gagal memperbarui stok barang.");
            }

            mysqli_commit($koneksi);
            $_SESSION['success'] = "Data barang keluar berhasil diperbarui.";
            header("Location: " . BASE_URL . "/router.php?page=barangkeluar&status=edit_sukses");
            exit;

        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
            header("Location: " . BASE_URL . "/router.php?page=barangkeluar");
            exit;
        }

    } else {
        $_SESSION['error'] = "Input tidak lengkap.";
        header("Location: " . BASE_URL . "/router.php?page=barangkeluar");
        exit;
    }
} else {
    header("Location: " . BASE_URL . "/router.php?page=barangkeluar");
    exit;
}