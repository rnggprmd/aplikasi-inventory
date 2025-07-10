<?php
require('fpdf/fpdf.php');
// Jangan akses langsung file ini dari URL
if (!defined('BASE_URL')) {
  die('Akses langsung tidak diizinkan');
}

// koneksi ke database
include __DIR__ . '/../../../koneksi.php';

// Cek session sudah dilakukan di router.php, jadi di sini kamu bisa yakin sudah login
// Sudah login karena masuk lewat router.php
$username = $_SESSION['username'] ?? null;

if (!$username) {
  // Fail safe jika session habis
  header("Location: " . BASE_URL . "/router.php?page=login");
  exit;
}

// Ambil parameter GET
$tanggalMulai = $_GET['tanggalMulai'] ?? '';
$tanggalAkhir = $_GET['tanggalAkhir'] ?? '';
$keyword = $_GET['keyword'] ?? '';

// Membuat class turunan dari FPDF untuk menambahkan footer
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

// KOP SURAT
$pdf->Image('img/Logo Sidebar.png', 10, 10, 25);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, 'PT. ABHIPRAYA CIPTA BERSAMA', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Jl. Contoh Alamat No.123, Jakarta, Indonesia', 0, 1, 'C');
$pdf->Cell(0, 6, 'Telp: (021) 123456 | Email: info@perusahaan.com', 0, 1, 'C');
$pdf->Ln(3);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(5);

// TANGGAL CETAK
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 6, 'Tanggal Cetak: ' . date('d-m-Y'), 0, 1, 'R');
$pdf->Ln(2);

// PERIODE FILTER (tambahan)
$pdf->SetFont('Arial', '', 9);
$periode = 'Periode: ';
$periode .= !empty($tanggalMulai) ? date('d-m-Y', strtotime($tanggalMulai)) : '-';
$periode .= ' s/d ';
$periode .= !empty($tanggalAkhir) ? date('d-m-Y', strtotime($tanggalAkhir)) : '-';
$pdf->Cell(0, 6, $periode, 0, 1, 'R');
$pdf->Ln(1);

// JUDUL
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Laporan Barang Masuk', 0, 1, 'C');
$pdf->Ln(2);

// HEADER TABEL
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 8, 'No', 1);
$pdf->Cell(25, 8, 'Id Barang', 1);
$pdf->Cell(50, 8, 'Nama Barang', 1);
$pdf->Cell(40, 8, 'Jumlah', 1);
$pdf->Cell(65, 8, 'Waktu', 1);
$pdf->Ln();

// QUERY
$pdf->SetFont('Arial', '', 10);
$no = 1;
$totalBarang = 0; // Inisialisasi total

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
  $keyword_safe = mysqli_real_escape_string($koneksi, $keyword);
  $query .= " AND b.namabarang LIKE '%$keyword_safe%'";
}

$query .= " ORDER BY bm.idbarang DESC";
$result = mysqli_query($koneksi, $query);

while ($row = mysqli_fetch_assoc($result)) {
  $jumlah = $row['jumlah_masuk'];
  $totalBarang++;

  $pdf->Cell(10, 8, $no++, 1);
  $pdf->Cell(25, 8, $row['idbarang'], 1);
  $pdf->Cell(50, 8, $row['nama_barang'], 1);
  $pdf->Cell(40, 8, $jumlah, 1);
  $pdf->Cell(65, 8, $row['waktu'], 1);
  $pdf->Ln();
}

// BARIS TOTAL DALAM TABEL
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(85, 8, 'Total Jumlah Barang Masuk', 1); // Gabungan dari kolom No, ID, Nama
$pdf->Cell(40, 8, $totalBarang . ' item', 1, 0, 'C');
$pdf->Cell(65, 8, '', 1); // Kolom Harga & Waktu dikosongkan
$pdf->Ln();

// CATATAN DI BAWAH
$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(255, 0, 0); // Merah
$pdf->MultiCell(0, 6, 'Note: Total di atas ini terhubung dengan filter tanggal yang diterapkan.', 0, 'L');

// Reset warna
$pdf->SetTextColor(0, 0, 0);

$pdf->Output('D', 'laporan_barangmasuk_' . date('Ymd') . '.pdf');
?>