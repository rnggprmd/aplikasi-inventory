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

// Tambahan: class dengan footer
class PDF extends FPDF {
  function Footer() {
    $this->SetY(-15);
    $this->SetFont('Arial', 'I', 8);
    $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
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

// JUDUL
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Laporan Data Barang', 0, 1, 'C');
$pdf->Ln(2);

// HEADER TABEL
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 8, 'No', 1);
$pdf->Cell(35, 8, 'ID Barang', 1);
$pdf->Cell(75, 8, 'Nama Barang', 1);
$pdf->Cell(25, 8, 'Stok', 1);
$pdf->Cell(45, 8, 'Harga', 1);
$pdf->Ln();

// ISI TABEL
$pdf->SetFont('Arial', '', 10);
$no = 1;
$totalBarang = 0;

$query = "SELECT * FROM databarang ORDER BY idbarang ASC";
$result = mysqli_query($koneksi, $query);

while ($row = mysqli_fetch_assoc($result)) {
  $pdf->Cell(10, 8, $no++, 1);
  $pdf->Cell(35, 8, $row['idbarang'], 1);
  $pdf->Cell(75, 8, $row['namabarang'], 1);
  $pdf->Cell(25, 8, $row['stockbarang'], 1);
  $pdf->Cell(45, 8, 'Rp. ' . number_format($row['harga'], 0, ',', '.'), 1);
  $pdf->Ln();

  $totalBarang++;
}

// TOTAL DI TABEL
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(120, 8, 'Total Jumlah Data Barang', 1);
$pdf->Cell(25, 8, $totalBarang . ' item', 1, 0, 'C');
$pdf->Cell(45, 8, '', 1);
$pdf->Ln();

// CATATAN
$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(255, 0, 0); // merah
$pdf->MultiCell(0, 6, 'Note: Total di atas ini terhubung dengan tabel yang ditampilkan.', 0, 'L');

// Reset warna
$pdf->SetTextColor(0, 0, 0);

$pdf->Output('D', 'laporan_databarang_' . date('Ymd') . '.pdf');
?>
