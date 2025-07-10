<?php
// Jangan akses langsung file ini dari URL
if (!defined('BASE_URL')) {
  die('Akses langsung tidak diizinkan');
}

// koneksi ke database
include __DIR__ . '/../../koneksi.php';

// Cek session sudah dilakukan di router.php, jadi di sini kamu bisa yakin sudah login
// Sudah login karena masuk lewat router.php
$username = $_SESSION['username'] ?? null;

if (!$username) {
  // Fail safe jika session habis
  header("Location: " . BASE_URL . "/router.php?page=login");
  exit;
}

// Inisialisasi array untuk respon
$response = [];

// Query menghitung jumlah data
$sql1 = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM databarang");
$sql2 = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM barang_masuk");
$sql3 = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM barang_keluar");
$sql4 = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM customer");

// Ambil hasil query
$data_barang   = mysqli_fetch_assoc($sql1);
$barang_masuk  = mysqli_fetch_assoc($sql2);
$barang_keluar = mysqli_fetch_assoc($sql3);
$customer      = mysqli_fetch_assoc($sql4);

// Masukkan data ke dalam array respon
$response['databarang']    = $data_barang['total'];
$response['barangmasuk']   = $barang_masuk['total'];
$response['barangkeluar']  = $barang_keluar['total'];
$response['customer']      = $customer['total'];

// Kembalikan data dalam format JSON
header('Content-Type: application/json');
echo json_encode($response);
?>


