<?php
// Jangan akses langsung file ini dari URL
if (!defined('BASE_URL')) {
  die('Akses langsung tidak diizinkan');
}

// Koneksi ke database
include __DIR__ . '/../../../koneksi.php';

// Cek session login
$username = $_SESSION['username'] ?? null;
if (!$username) {
  header("Location: " . BASE_URL . "/router.php?page=login");
  exit;
}

// Header Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_data_barang_" . date('Ymd') . ".xls");

// =====================
// KOP / HEADER
// =====================
echo "<table border='0' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><td colspan='5' style='font-weight: bold; font-size: 16px; text-align: center;'>PT. ABHIPRAYA CIPTA BERSAMA</td></tr>";
echo "<tr><td colspan='5' style='text-align: center;'>Jl. Contoh Alamat No.123, Jakarta, Indonesia</td></tr>";
echo "<tr><td colspan='5' style='text-align: center;'>Telp: (021) 123456 | Email: info@perusahaan.com</td></tr>";
echo "<tr><td colspan='5'><hr></td></tr>";
echo "<tr><td colspan='5' style='text-align: right;'>Tanggal Cetak: " . date('d-m-Y') . "</td></tr>";
echo "<tr><td colspan='5' style='font-weight: bold; font-size: 14px; text-align: center;'>Laporan Data Barang</td></tr>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
echo "</table>";

// =====================
// TABEL DATA BARANG
// =====================
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<thead>
<tr style='font-weight: bold; background-color: #f2f2f2; text-align: center;'>
  <th>No</th>
  <th>ID Barang</th>
  <th>Nama Barang</th>
  <th>Stok</th>
  <th>Harga</th>
</tr>
</thead>
<tbody>";

$no = 1;
$totalBarang = 0;
$query = "SELECT * FROM databarang ORDER BY idbarang ASC";
$result = mysqli_query($koneksi, $query);

while ($row = mysqli_fetch_assoc($result)) {
  echo "<tr>
    <td style='text-align: center;'>{$no}</td>
    <td>{$row['idbarang']}</td>
    <td>{$row['namabarang']}</td>
    <td style='text-align: center;'>{$row['stockbarang']}</td>
    <td>Rp. " . number_format($row['harga'], 0, ',', '.') . "</td>
  </tr>";
  $no++;
  $totalBarang++;
}

// =====================
// TOTAL DAN CATATAN
// =====================
// Baris total
echo "<tr style='font-weight: bold; background-color: #e0f7fa;'>
  <td colspan='3' style='text-align: right; padding: 5px;'>Total Jumlah Data Barang</td>
  <td style='text-align: center; padding: 5px;'>{$totalBarang} item</td>
  <td></td>
</tr>";

echo "<tr>
  <td colspan='5' style='color: red; font-style: italic;'>Note: Total di atas ini terhubung dengan tabel yang ditampilkan.</td>
</tr>";

echo "</tbody></table>";
?>
