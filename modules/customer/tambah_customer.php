<?php
// koneksi ke database
include __DIR__ . '/../../koneksi.php';

// Mulai session jika belum
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/router.php?page=login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_customer = trim($_POST['id_customer']);
    $nama = trim($_POST['nama_customer']);
    $alamat = trim($_POST['alamat']);
    $no_telp = trim($_POST['no_telp']);

    // Validasi sederhana, cek field kosong
    if (empty($id_customer) || empty($nama) || empty($alamat) || empty($no_telp)) {
        header("Location: " . BASE_URL . "/router.php?page=customer");
        exit;
    }

    // Cek apakah ID sudah ada
    $cek = $koneksi->prepare("SELECT id_customer FROM customer WHERE id_customer = ?");
    $cek->bind_param("s", $id_customer);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        $cek->close();
        header("Location: " . BASE_URL . "/router.php?page=customer");
        exit;
    }

    // Insert data dengan prepared statement
    $stmt = $koneksi->prepare("INSERT INTO customer (id_customer, nama_customer, alamat, no_telp) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $id_customer, $nama, $alamat, $no_telp);

    if ($stmt->execute()) {
        // Insert sukses
        $stmt->close();
        $cek->close();
        $koneksi->close();

        header("Location: " . BASE_URL . "/router.php?page=customer&status=sukses");
        exit;
    } else {
        // Insert gagal
        $stmt->close();
        $cek->close();
        $koneksi->close();

        header("Location: " . BASE_URL . "/router.php?page=customer");
        exit;
    }
} else {
    // Jika bukan POST, langsung redirect ke customer
    header("Location: " . BASE_URL . "/router.php?page=customer");
    exit;
}