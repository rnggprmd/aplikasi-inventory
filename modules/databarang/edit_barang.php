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
    if (isset($_POST['idbarang'], $_POST['namabarang'], $_POST['harga'])) {
        $idbarang = trim($_POST['idbarang']);
        $namabarang = trim($_POST['namabarang']);
        $harga = (int) $_POST['harga'];

        if (empty($idbarang) || empty($namabarang) || $harga < 0) {
            $_SESSION['error'] = "Data tidak valid. Harap periksa input.";
            header("Location: " . BASE_URL . "/router.php?page=databarang");
            exit;
        }

        // Update dengan prepared statement
        $stmt = $koneksi->prepare("UPDATE databarang SET namabarang = ?, harga = ? WHERE idbarang = ?");
        if (!$stmt) {
            $_SESSION['error'] = "Gagal mempersiapkan query.";
            header("Location: " . BASE_URL . "/router.php?page=databarang");
            exit;
        }

        $stmt->bind_param("sis", $namabarang, $harga, $idbarang);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data barang berhasil diperbarui.";
        } else {
            $_SESSION['error'] = "Gagal memperbarui data barang.";
        }

        $stmt->close();
        $koneksi->close();

        header("Location: " . BASE_URL . "/router.php?page=databarang&status=edit_sukses");
        exit;
    } else {
        header("Location: " . BASE_URL . "/router.php?page=databarang");
        exit;
    }
} else {
    header("Location: " . BASE_URL . "/router.php?page=databarang");
    exit;
}
