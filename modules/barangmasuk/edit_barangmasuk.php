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
    if (isset($_POST['id_barangmasuk'], $_POST['jumlah_masuk'], $_POST['waktu'])) {
        $id_barangmasuk = (int) $_POST['id_barangmasuk'];
        $jumlah_baru = (int) $_POST['jumlah_masuk'];
        $waktu_input = $_POST['waktu'];
        $waktu_sql = date('Y-m-d H:i:s', strtotime($waktu_input));

        mysqli_begin_transaction($koneksi);

        try {
            // Ambil data lama barang_masuk
            $stmt = $koneksi->prepare("SELECT jumlah_masuk, idbarang FROM barang_masuk WHERE id_barangmasuk = ?");
            $stmt->bind_param("i", $id_barangmasuk);
            $stmt->execute();
            $result_old = $stmt->get_result();

            if ($result_old->num_rows === 0) {
                throw new Exception("Data lama tidak ditemukan.");
            }

            $old_data = $result_old->fetch_assoc();
            $jumlah_lama = (int) $old_data['jumlah_masuk'];
            $idbarang = $old_data['idbarang'];

            $selisih = $jumlah_baru - $jumlah_lama;

            // Update barang_masuk
            $stmt_update = $koneksi->prepare("UPDATE barang_masuk SET jumlah_masuk = ?, waktu = ? WHERE id_barangmasuk = ?");
            $stmt_update->bind_param("isi", $jumlah_baru, $waktu_sql, $id_barangmasuk);
            if (!$stmt_update->execute()) {
                throw new Exception("Gagal memperbarui jumlah barang masuk: " . $stmt_update->error);
            }

            // Ambil stok lama
            $result_stok = mysqli_query($koneksi, "SELECT stockbarang FROM databarang WHERE idbarang = '$idbarang'");
            if (!$result_stok || mysqli_num_rows($result_stok) == 0) {
                throw new Exception("Data stok tidak ditemukan.");
            }

            $stok_data = mysqli_fetch_assoc($result_stok);
            $stok_lama = (int) $stok_data['stockbarang'];
            $stok_baru = $stok_lama + $selisih;

            if ($stok_baru < 0) {
                throw new Exception("Stok menjadi negatif setelah perubahan jumlah.");
            }

            // Update stok barang
            $stmt_stok = $koneksi->prepare("UPDATE databarang SET stockbarang = ? WHERE idbarang = ?");
            $stmt_stok->bind_param("is", $stok_baru, $idbarang);
            if (!$stmt_stok->execute()) {
                throw new Exception("Gagal memperbarui stok barang: " . $stmt_stok->error);
            }

            mysqli_commit($koneksi);
            $_SESSION['success'] = "Data barang masuk berhasil diperbarui.";
            header("Location: " . BASE_URL . "/router.php?page=barangmasuk&status=edit_sukses");
            exit;

        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
            header("Location: " . BASE_URL . "/router.php?page=barangmasuk");
            exit;
        }
    } else {
        $_SESSION['error'] = "Input tidak lengkap.";
        header("Location: " . BASE_URL . "/router.php?page=barangmasuk");
        exit;
    }
} else {
    header("Location: " . BASE_URL . "/router.php?page=barangmasuk");
    exit;
}
