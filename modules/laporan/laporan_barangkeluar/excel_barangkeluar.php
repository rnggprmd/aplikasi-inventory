<?php
// Jangan akses langsung file ini dari URL
if (!defined('BASE_URL')) {
  die('Akses langsung tidak diizinkan');
}

// koneksi ke database
include __DIR__ . '/../../../koneksi.php';

$username = $_SESSION['username'] ?? null;
if (!$username) {
  header("Location: " . BASE_URL . "/router.php?page=login");
  exit;
}
error_reporting(0);

// Set header Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_barang_keluar_" . date('Ymd') . ".xls");

// Ambil parameter GET
$tanggalMulai = mysqli_real_escape_string($koneksi, $_GET['tanggalMulai'] ?? '');
$tanggalAkhir = mysqli_real_escape_string($koneksi, $_GET['tanggalAkhir'] ?? '');
$filterCustomer = mysqli_real_escape_string($koneksi, $_GET['filter_customer'] ?? '');

// Header Laporan
echo "<table border='0' style='border-collapse: collapse;'>";
echo "<tr><td colspan='8' style='font-weight: bold; font-size: 16px; text-align: center;'>PT. ABHIPRAYA CIPTA BERSAMA</td></tr>";
echo "<tr><td colspan='8' style='text-align: center;'>Jl. Contoh Alamat No.123, Jakarta, Indonesia</td></tr>";
echo "<tr><td colspan='8' style='text-align: center;'>Telp: (021) 123456 | Email: info@perusahaan.com</td></tr>";
echo "<tr><td colspan='8'><hr></td></tr>";
echo "<tr><td colspan='8' style='font-weight: bold; font-size: 14px; text-align: center;'>Laporan Barang Keluar</td></tr>";
echo "<tr><td colspan='8' style='text-align: right;'>Tanggal Cetak: " . date('d-m-Y') . "</td></tr>";

// Tampilkan periode
$periode = 'Periode: ';
$periode .= !empty($tanggalMulai) ? date('d-m-Y', strtotime($tanggalMulai)) : '-';
$periode .= ' s/d ';
$periode .= !empty($tanggalAkhir) ? date('d-m-Y', strtotime($tanggalAkhir)) : '-';
echo "<tr><td colspan='8' style='text-align: right;'>$periode</td></tr>";

// Tampilkan nama customer (jika ada filter)
if (!empty($filterCustomer)) {
  $custQuery = mysqli_query($koneksi, "SELECT nama_customer FROM customer WHERE id_customer = '$filterCustomer' LIMIT 1");
  $custRow = mysqli_fetch_assoc($custQuery);
  $namaCustomer = $custRow['nama_customer'] ?? '-';
  echo "<tr><td colspan='8' style='text-align: right;'>Customer: " . htmlspecialchars($namaCustomer) . "</td></tr>";
}

echo "<tr><td colspan='8'>&nbsp;</td></tr>";
echo "</table>";

// Header Tabel Data
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<thead>
<tr style='font-weight: bold; background-color: #f2f2f2;'>
  <th>No</th>
  <th>Id Barang</th>
  <th>Nama Barang</th>
  <th>Jumlah Barang Keluar</th>
  <th>Harga</th>
  <th>Id Customer</th>
  <th>Nama Customer</th>
  <th>Waktu</th>
</tr>
</thead>
<tbody>";

$no = 1;
$totalKeluar = 0;
$totalHarga = 0;

$query = "SELECT bk.*, b.namabarang AS nama_barang, b.harga, c.nama_customer
          FROM barang_keluar bk
          LEFT JOIN databarang b ON bk.idbarang = b.idbarang
          LEFT JOIN customer c ON bk.id_customer = c.id_customer
          WHERE 1=1";

// Filter tanggal
if (!empty($tanggalMulai) && !empty($tanggalAkhir)) {
    $query .= " AND DATE(bk.waktu) BETWEEN '$tanggalMulai' AND '$tanggalAkhir'";
} elseif (!empty($tanggalMulai)) {
    $query .= " AND DATE(bk.waktu) >= '$tanggalMulai'";
} elseif (!empty($tanggalAkhir)) {
    $query .= " AND DATE(bk.waktu) <= '$tanggalAkhir'";
}

// Filter customer
if (!empty($filterCustomer)) {
    $query .= " AND bk.id_customer = '$filterCustomer'";
}

$query .= " ORDER BY bk.waktu DESC";
$result = mysqli_query($koneksi, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $jumlah = $row['jumlah_keluar'];
    $harga = $row['harga'] ?? 0;
    $totalKeluar += $jumlah;
    $totalHarga += ($jumlah * $harga);

    echo "<tr>
      <td>{$no}</td>
      <td>{$row['idbarang']}</td>
      <td>{$row['nama_barang']}</td>
      <td>{$jumlah}</td>
      <td>Rp. " . number_format($harga, 0, ',', '.') . "</td>
      <td>{$row['id_customer']}</td>
      <td>{$row['nama_customer']}</td>
      <td>{$row['waktu']}</td>
    </tr>";
    $no++;
}

echo "<tr style='font-weight: bold;'>
        <td colspan='3'>Total Jumlah Barang Keluar</td>
        <td colspan='5' style='text-align: left;'>{$totalKeluar} unit</td>
      </tr>";

echo "<tr style='font-weight: bold;'>
        <td colspan='3'>Total Harga Barang Keluar</td>
        <td colspan='5' style='text-align: left;'>Rp. " . number_format($totalHarga, 0, ',', '.') . "</td>
      </tr>";

echo "<tr><td colspan='8'><br><i style='color: red;'>Note: Total di atas mengikuti filter tanggal dan customer.</i></td></tr>";
echo "</tbody></table>";
?>
