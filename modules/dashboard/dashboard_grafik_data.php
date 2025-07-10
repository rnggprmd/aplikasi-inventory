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

$response = ['labels' => [],'barang_masuk' => [],'barang_keluar' => []
];

// Ambil data barang masuk per tanggal
$sql_masuk = mysqli_query($koneksi, "SELECT DATE(waktu) AS tanggal, SUM(jumlah_masuk) AS total FROM barang_masuk GROUP BY DATE(waktu) ORDER BY tanggal ASC");

// Simpan data masuk sementara
$masuk_data = [];
while ($row = mysqli_fetch_assoc($sql_masuk)) {
  $tanggal = $row['tanggal'];
  $masuk_data[$tanggal] = $row['total'];
}

// Ambil data barang keluar per tanggal
$sql_keluar = mysqli_query($koneksi, "SELECT DATE(waktu) AS tanggal, SUM(jumlah_keluar) AS total FROM barang_keluar GROUP BY DATE(waktu) ORDER BY tanggal ASC");

// Simpan data keluar sementara
$keluar_data = [];
while ($row = mysqli_fetch_assoc($sql_keluar)) {
  $tanggal = $row['tanggal'];
  $keluar_data[$tanggal] = $row['total'];
}

// Gabungkan semua tanggal unik
$tanggal_all = array_unique(array_merge(array_keys($masuk_data), array_keys($keluar_data)));
sort($tanggal_all);

// Buat array final berdasarkan tanggal gabungan
foreach ($tanggal_all as $tanggal) {
  $response['labels'][] = $tanggal;
  $response['barang_masuk'][] = $masuk_data[$tanggal] ?? 0;
  $response['barang_keluar'][] = $keluar_data[$tanggal] ?? 0;
}

header('Content-Type: application/json');
echo json_encode($response);
?>
