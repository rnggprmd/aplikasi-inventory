<?php
include __DIR__ . '/../../koneksi.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', '/abhipraya-cipta-bersama');
}

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/router.php?page=login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idbarangs = $_POST['idbarang'] ?? [];
    $jumlahs = $_POST['jumlah_keluar'] ?? [];
    $hargas = $_POST['harga'] ?? [];
    $waktus = $_POST['waktu'] ?? [];
    $customers = $_POST['id_customer'] ?? [];

    // Validasi jumlah data
    if (
        count($idbarangs) === 0 ||
        count($idbarangs) !== count($jumlahs) ||
        count($jumlahs) !== count($waktus) ||
        count($waktus) !== count($customers)
    ) {
        $_SESSION['error'] = "Data tidak lengkap atau jumlah data tidak sesuai.";
        header("Location: " . BASE_URL . "/router.php?page=barangkeluar");
        exit;
    }

    mysqli_begin_transaction($koneksi);

    try {
        for ($i = 0; $i < count($idbarangs); $i++) {
            $idbarang = $idbarangs[$i];
            $jumlah_keluar = intval($jumlahs[$i]);
            $id_customer = $customers[$i];
            $waktu_input = $waktus[$i];
            $waktu = str_replace('T', ' ', $waktu_input) . ':00';

            if (empty($idbarang) || $jumlah_keluar <= 0 || empty($waktu_input) || empty($id_customer)) {
                throw new Exception("Baris ke-" . ($i + 1) . " memiliki data tidak lengkap atau tidak valid.");
            }

            // Cek stok
            $stmt = $koneksi->prepare("SELECT stockbarang FROM databarang WHERE idbarang = ?");
            if (!$stmt) throw new Exception("Query stok gagal (baris " . ($i + 1) . ")");
            $stmt->bind_param("s", $idbarang);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result || $result->num_rows === 0) {
                throw new Exception("Barang tidak ditemukan (ID: $idbarang)");
            }

            $stok_lama = $result->fetch_assoc()['stockbarang'];
            if ($stok_lama < $jumlah_keluar) {
                throw new Exception("Stok tidak cukup untuk barang $idbarang (baris " . ($i + 1) . ")");
            }

            $stok_baru = $stok_lama - $jumlah_keluar;

            // Simpan ke barang_keluar
            $stmt_insert = $koneksi->prepare("INSERT INTO barang_keluar (idbarang, jumlah_keluar, id_customer, waktu) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("siss", $idbarang, $jumlah_keluar, $id_customer, $waktu);
            $stmt_insert->execute();

            // Update stok
            $stmt_update = $koneksi->prepare("UPDATE databarang SET stockbarang = ? WHERE idbarang = ?");
            $stmt_update->bind_param("is", $stok_baru, $idbarang);
            $stmt_update->execute();
        }

        mysqli_commit($koneksi);
        $_SESSION['success'] = "Semua data barang keluar berhasil disimpan.";
        header("Location: " . BASE_URL . "/router.php?page=barangkeluar&status=sukses");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $_SESSION['error'] = "Gagal menyimpan data: " . $e->getMessage();
        header("Location: " . BASE_URL . "/router.php?page=barangkeluar");
        exit;
    }
} else {
    header("Location: " . BASE_URL . "/router.php?page=barangkeluar");
    exit;
}
