<?php
require('fpdf/fpdf.php');

if (!defined('BASE_URL')) {
  die('Akses langsung tidak diizinkan');
}

include __DIR__ . '/../../../koneksi.php';

$username = $_SESSION['username'] ?? null;
if (!$username) {
  header("Location: " . BASE_URL . "/router.php?page=login");
  exit;
}

error_reporting(0);

$tanggalMulai = $_GET['tanggalMulai'] ?? '';
$tanggalAkhir = $_GET['tanggalAkhir'] ?? '';
$filterCustomer = mysqli_real_escape_string($koneksi, $_GET['filter_customer'] ?? '');

// Extend FPDF class for footer
class PDF extends FPDF {
  function Footer() {
    $this->SetY(-15);
    $this->SetFont('Arial','I',8);
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
  }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Kop Surat
$pdf->Image('img/Logo Sidebar.png', 10, 10, 25);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, 'PT. ABHIPRAYA CIPTA BERSAMA', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Jl. Contoh Alamat No.123, Jakarta, Indonesia', 0, 1, 'C');
$pdf->Cell(0, 6, 'Telp: (021) 123456 | Email: info@perusahaan.com', 0, 1, 'C');
$pdf->Ln(3);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

// Tanggal Cetak
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 6, 'Tanggal Cetak: ' . date('d-m-Y'), 0, 1, 'R');
$pdf->Ln(2);

// Periode Filter & Customer
$pdf->SetFont('Arial', '', 9);
$periode = 'Periode: ';
$periode .= !empty($tanggalMulai) ? date('d-m-Y', strtotime($tanggalMulai)) : '-';
$periode .= ' s/d ';
$periode .= !empty($tanggalAkhir) ? date('d-m-Y', strtotime($tanggalAkhir)) : '-';

$pdf->Cell(0, 6, $periode, 0, 1, 'R');

if (!empty($filterCustomer)) {
  // Ambil nama customer
  $custResult = mysqli_query($koneksi, "SELECT nama_customer FROM customer WHERE id_customer = '$filterCustomer' LIMIT 1");
  $custRow = mysqli_fetch_assoc($custResult);
  $namaCustomer = $custRow['nama_customer'] ?? '-';
  $pdf->Cell(0, 6, "Customer: $namaCustomer", 0, 1, 'R');
}
$pdf->Ln(3);

// Judul
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Laporan Barang Keluar', 0, 1, 'C');
$pdf->Ln(2);

// Header Tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 8, 'No', 1);
$pdf->Cell(25, 8, 'Id Barang', 1);
$pdf->Cell(40, 8, 'Nama Barang', 1);
$pdf->Cell(25, 8, 'Jumlah', 1);
$pdf->Cell(30, 8, 'Harga', 1);
$pdf->Cell(25, 8, 'Id Customer', 1);
$pdf->Cell(35, 8, 'Nama Customer', 1);
$pdf->Ln();

// Query Data
$pdf->SetFont('Arial', '', 10);
$no = 1;
$totalBarangKeluar = 0;
$totalHarga = 0;

$query = "SELECT bk.*, b.namabarang AS nama_barang, b.harga, c.nama_customer
          FROM barang_keluar bk
          LEFT JOIN databarang b ON bk.idbarang = b.idbarang
          LEFT JOIN customer c ON bk.id_customer = c.id_customer
          WHERE 1=1";

if (!empty($tanggalMulai) && !empty($tanggalAkhir)) {
  $query .= " AND DATE(bk.waktu) BETWEEN '$tanggalMulai' AND '$tanggalAkhir'";
} elseif (!empty($tanggalMulai)) {
  $query .= " AND DATE(bk.waktu) >= '$tanggalMulai'";
} elseif (!empty($tanggalAkhir)) {
  $query .= " AND DATE(bk.waktu) <= '$tanggalAkhir'";
}

if (!empty($filterCustomer)) {
  $query .= " AND bk.id_customer = '$filterCustomer'";
}

$query .= " ORDER BY bk.waktu DESC";

$result = mysqli_query($koneksi, $query);

while ($row = mysqli_fetch_assoc($result)) {
  $jumlah = $row['jumlah_keluar'];
  $harga = $row['harga'] ?? 0;
  $totalBarangKeluar += $jumlah;
  $totalHarga += $jumlah * $harga;

  $pdf->Cell(10, 8, $no++, 1);
  $pdf->Cell(25, 8, $row['idbarang'], 1);
  $pdf->Cell(40, 8, $row['nama_barang'], 1);
  $pdf->Cell(25, 8, $jumlah, 1);
  $pdf->Cell(30, 8, 'Rp. ' . number_format($harga, 0, ',', '.'), 1);
  $pdf->Cell(25, 8, $row['id_customer'], 1);
  $pdf->Cell(35, 8, $row['nama_customer'], 1);
  $pdf->Ln();
}

// Baris Total
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(75, 8, 'Total Jumlah Barang Keluar', 1);
$pdf->Cell(25, 8, $totalBarangKeluar . ' unit', 1, 0, 'C');
$pdf->Cell(90, 8, '', 1); // Kosongkan kolom harga dan customer
$pdf->Ln();

$pdf->Cell(75, 8, 'Total Harga Barang Keluar', 1);
$pdf->Cell(25, 8, 'Rp. ' . number_format($totalHarga, 0, ',', '.'), 1, 0, 'C');
$pdf->Cell(90, 8, '', 1);
$pdf->Ln();

// Catatan
$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(255, 0, 0);
$pdf->MultiCell(0, 6, 'Note: Total di atas terhubung dengan filter tanggal dan customer yang diterapkan.', 0, 'L');
$pdf->SetTextColor(0, 0, 0);

$pdf->Output('D', 'laporan_barangkeluar_' . date('Ymd') . '.pdf');
?>