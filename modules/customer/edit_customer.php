<?php
// koneksi ke database
include __DIR__ . '/../../koneksi.php';

// Mulai session jika belum
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/router.php?page=login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_customer'], $_POST['nama_customer'], $_POST['alamat'], $_POST['no_telp'])) {
        $id = trim($_POST['id_customer']);
        $nama = trim($_POST['nama_customer']);
        $alamat = trim($_POST['alamat']);
        $no_telp = trim($_POST['no_telp']);

        if (empty($id) || empty($nama) || empty($alamat) || empty($no_telp)) {
            header("Location: " . BASE_URL . "/router.php?page=customer");
            exit;
        }

        // Update dengan prepared statement
        $stmt = $koneksi->prepare("UPDATE customer SET nama_customer = ?, alamat = ?, no_telp = ? WHERE id_customer = ?");
        if (!$stmt) {
            header("Location: " . BASE_URL . "/router.php?page=customer");
            exit;
        }

        $stmt->bind_param("ssss", $nama, $alamat, $no_telp, $id);
        $stmt->execute();
        $stmt->close();
        $koneksi->close();

        // Untuk bagian alert edit data
        header("Location: " . BASE_URL . "/router.php?page=customer&status=edit_sukses");
        // Untuk bagian alert edit data
        exit;
    } else {
        header("Location: " . BASE_URL . "/router.php?page=customer");
        exit;
    }
} else {
    header("Location: " . BASE_URL . "/router.php?page=customer");
    exit;
}
?>
