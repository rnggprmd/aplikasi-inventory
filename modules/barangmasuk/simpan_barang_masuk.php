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
    $idbarangArr = $_POST['idbarang'] ?? [];
    $jumlahArr = $_POST['jumlah_masuk'] ?? [];
    $waktuArr = $_POST['waktu'] ?? [];

    // Validasi array
    if (
        !is_array($idbarangArr) || !is_array($jumlahArr) || !is_array($waktuArr) ||
        count($idbarangArr) !== count($jumlahArr) || count($idbarangArr) !== count($waktuArr)
    ) {
        $_SESSION['error'] = "Data tidak valid atau tidak lengkap.";
        header("Location: " . BASE_URL . "/router.php?page=barangmasuk");
        exit;
    }

    mysqli_begin_transaction($koneksi);

    try {
        for ($i = 0; $i < count($idbarangArr); $i++) {
            $idbarang = $idbarangArr[$i];
            $jumlah_masuk = intval($jumlahArr[$i]);
            $waktu_input = $waktuArr[$i];

            if (empty($idbarang) || $jumlah_masuk <= 0 || empty($waktu_input)) {
                throw new Exception("Data pada baris ke-" . ($i + 1) . " tidak lengkap atau tidak valid.");
            }

            $waktu = str_replace('T', ' ', $waktu_input) . ':00';

            if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $waktu)) {
                throw new Exception("Format waktu tidak valid di baris ke-" . ($i + 1));
            }

            // Ambil stok lama
            $stmt = $koneksi->prepare("SELECT stockbarang FROM databarang WHERE idbarang = ?");
            if (!$stmt) throw new Exception("Gagal query SELECT stok");

            $stmt->bind_param("s", $idbarang);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result || $result->num_rows === 0) throw new Exception("Barang ID $idbarang tidak ditemukan.");

            $stok_lama = $result->fetch_assoc()['stockbarang'];
            $stok_baru = $stok_lama + $jumlah_masuk;

            // Insert barang masuk
            $stmt_insert = $koneksi->prepare("INSERT INTO barang_masuk (idbarang, jumlah_masuk, waktu) VALUES (?, ?, ?)");
            if (!$stmt_insert) throw new Exception("Gagal query INSERT");

            $stmt_insert->bind_param("sis", $idbarang, $jumlah_masuk, $waktu);
            $stmt_insert->execute();

            // Update stok
            $stmt_update = $koneksi->prepare("UPDATE databarang SET stockbarang = ? WHERE idbarang = ?");
            if (!$stmt_update) throw new Exception("Gagal query UPDATE stok");

            $stmt_update->bind_param("is", $stok_baru, $idbarang);
            $stmt_update->execute();
        }

        mysqli_commit($koneksi);
        $_SESSION['success'] = "Semua data barang masuk berhasil disimpan.";
        header("Location: " . BASE_URL . "/router.php?page=barangmasuk&status=sukses");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $_SESSION['error'] = "Gagal menyimpan data: " . $e->getMessage();
        header("Location: " . BASE_URL . "/router.php?page=barangmasuk");
        exit;
    }

} else {
    header("Location: " . BASE_URL . "/router.php?page=barangmasuk");
    exit;
}
