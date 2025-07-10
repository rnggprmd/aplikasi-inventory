<?php
if (!defined('BASE_URL')) {
  die('Akses langsung tidak diizinkan');
}

include __DIR__ . '/../../../koneksi.php';

$username = $_SESSION['username'] ?? null;
if (!$username) {
  header("Location: " . BASE_URL . "/router.php?page=login");
  exit;
}

error_reporting(0); // Hindari warning sebelum header output

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_barang_masuk_" . date('Ymd') . ".xls");

// Ambil filter
$tanggalMulai = $_GET['tanggalMulai'] ?? '';
$tanggalAkhir = $_GET['tanggalAkhir'] ?? '';
$keyword = $_GET['keyword'] ?? '';

// HEADER / KOP
echo "<table border='0' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;'>";
echo "<tr><td colspan='5' style='font-weight: bold; font-size: 16px; text-align: center;'>PT. ABHIPRAYA CIPTA BERSAMA</td></tr>";
echo "<tr><td colspan='5' style='text-align: center;'>Jl. Contoh Alamat No.123, Jakarta, Indonesia</td></tr>";
echo "<tr><td colspan='5' style='text-align: center;'>Telp: (021) 123456 | Email: info@perusahaan.com</td></tr>";
echo "<tr><td colspan='5'><hr></td></tr>";
echo "<tr><td colspan='5' style='text-align: right;'>Tanggal Cetak: " . date('d-m-Y') . "</td></tr>";

$periode = (!empty($tanggalMulai) ? date('d-m-Y', strtotime($tanggalMulai)) : '-') . ' s/d ' . (!empty($tanggalAkhir) ? date('d-m-Y', strtotime($tanggalAkhir)) : '-');
echo "<tr><td colspan='5' style='text-align: right;'>Periode: {$periode}</td></tr>";

echo "<tr><td colspan='5' style='font-weight: bold; font-size: 14px; text-align: center;'>Laporan Barang Masuk</td></tr>";
echo "<tr><td colspan='5'>&nbsp;</td></tr>";
echo "</table>";

// TABEL DATA
echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;'>";
echo "<thead>
<tr style='font-weight: bold; background-color: #f2f2f2; text-align: center;'>
  <th style='padding: 5px;'>No</th>
  <th style='padding: 5px;'>ID Barang</th>
  <th style='padding: 5px;'>Nama Barang</th>
  <th style='padding: 5px;'>Jumlah</th>
  <th style='padding: 5px;'>Waktu</th>
</tr>
</thead>
<tbody>";

$no = 1;
$totalBarang = 0;

$query = "SELECT bm.*, b.namabarang AS nama_barang
          FROM barang_masuk bm
          LEFT JOIN databarang b ON bm.idbarang = b.idbarang
          WHERE 1=1";

if (!empty($tanggalMulai) && !empty($tanggalAkhir)) {
  $query .= " AND DATE(bm.waktu) BETWEEN '$tanggalMulai' AND '$tanggalAkhir'";
} else if (!empty($tanggalMulai)) {
  $query .= " AND DATE(bm.waktu) >= '$tanggalMulai'";
} else if (!empty($tanggalAkhir)) {
  $query .= " AND DATE(bm.waktu) <= '$tanggalAkhir'";
}

if (!empty($keyword)) {
  $keyword_esc = mysqli_real_escape_string($koneksi, $keyword);
  $query .= " AND b.namabarang LIKE '%$keyword_esc%'";
}

$query .= " ORDER BY bm.idbarang DESC";
$result = mysqli_query($koneksi, $query);

while ($row = mysqli_fetch_assoc($result)) {
  $jumlah = $row['jumlah_masuk'];
  $totalBarang++;

  echo "<tr>
      <td style='text-align: center; padding: 5px;'>{$no}</td>
      <td style='padding: 5px;'>{$row['idbarang']}</td>
      <td style='padding: 5px;'>{$row['nama_barang']}</td>
      <td style='text-align: center; padding: 5px;'>{$jumlah}</td>
      <td style='padding: 5px;'>{$row['waktu']}</td>
    </tr>";
  $no++;
}

// Baris total
echo "<tr style='font-weight: bold; background-color: #e0f7fa;'>
  <td colspan='3' style='text-align: right; padding: 5px;'>Total Jumlah Barang Masuk</td>
  <td style='text-align: center; padding: 5px;'>{$totalBarang} item</td>
  <td></td>
</tr>";

// Catatan
echo "<tr>
  <td colspan='5' style='color: red; font-style: italic; padding: 5px;'>Note: Total di atas ini terhubung dengan filter tanggal yang diterapkan.</td>
</tr>";

echo "</tbody></table>";
?>
