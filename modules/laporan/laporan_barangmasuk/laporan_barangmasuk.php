<?php
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

$stmt = $koneksi->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Laporan Barang Masuk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

    <!-- Link CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">

    <!-- Logo Icon Title -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/img/Logo Sidebar.png">
</head>

<body>
    <div class="container-fluid">
        <div class="row flex-nowrap">
            <!-- Sidebar -->
            <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col py-3 px-4">
                <!-- Baru Ditambahkan Semalam -->
                <button class="btn btn-outline-primary d-md-none mb-3" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i> Menu
                </button>
                <!-- Header -->
                <div class="bg-white p-3 rounded mb-2 text-center shadow-sm">
                    <h4 class="mt-3 mb-3 fw-bold">Laporan</h4>
                </div>

                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mt-1">
                    <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/router.php?page=dashboard"
                                class="text-decoration-none">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Laporan Barang Masuk</li>
                    </ol>
                </nav>

                <!-- Laporan Barang Masuk -->
                <div class="bg-white rounded p-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-2">Laporan Barang Masuk</h5>
                    </div>

                    <form method="GET" action="router.php">
                        <input type="hidden" name="page" value="laporan_barangmasuk" />
                        <div class="row mb-4">

                            <!-- Tanggal Mulai & Sampai -->
                            <div class="col-md-6 mb-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="tanggalMulai" class="form-label">Mulai Dari</label>
                                        <input type="date" id="tanggalMulai" name="tanggalMulai"
                                            class="form-control text-center"
                                            value="<?= htmlspecialchars($_GET['tanggalMulai'] ?? '') ?>" />
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tanggalAkhir" class="form-label">Sampai Dengan</label>
                                        <input type="date" id="tanggalAkhir" name="tanggalAkhir"
                                            class="form-control text-center"
                                            value="<?= htmlspecialchars($_GET['tanggalAkhir'] ?? '') ?>" />
                                    </div>
                                </div>
                            </div>

                            <!-- Export Buttons (kanan atas) -->
                            <div class="col-md-6 mb-1 text-md-end">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <div class="d-flex justify-content-md-end gap-2">
                                    <a href="<?= BASE_URL ?>/router.php?page=excel_barangmasuk&tanggalMulai=<?= urlencode($_GET['tanggalMulai'] ?? '') ?>&tanggalAkhir=<?= urlencode($_GET['tanggalAkhir'] ?? '') ?>&keyword=<?= urlencode($_GET['keyword'] ?? '') ?>"
                                        class="btn btn-success" target="_blank">
                                        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                                    </a>
                                    <a href="<?= BASE_URL ?>/router.php?page=pdf_barangmasuk&tanggalMulai=<?= urlencode($_GET['tanggalMulai'] ?? '') ?>&tanggalAkhir=<?= urlencode($_GET['tanggalAkhir'] ?? '') ?>&keyword=<?= urlencode($_GET['keyword'] ?? '') ?>"
                                        class="btn btn-danger" target="_blank">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                                    </a>
                                </div>
                            </div>

                            <!-- Clear & Tampilkan Buttons (kiri bawah) -->
                            <div class="col-md-6 mb-2">
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <a href="router.php?page=laporan_barangmasuk"
                                            class="btn btn-secondary w-100">Clear</a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Tabel -->
                    <div class="table-responsive bg-light p-3 rounded shadow-sm mt-3">
                        <table class="table table-striped table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Id Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah Barang Masuk</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $tanggalMulai = $_GET['tanggalMulai'] ?? '';
                                $tanggalAkhir = $_GET['tanggalAkhir'] ?? '';
                                $keyword = $_GET['keyword'] ?? '';

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

                                // Hitung total jumlah barang masuk
                                $totalQuery = "SELECT COUNT(*) AS total_barang 
                                FROM barang_masuk bm
                                LEFT JOIN databarang b ON bm.idbarang = b.idbarang
                                WHERE 1=1";

                                if (!empty($tanggalMulai) && !empty($tanggalAkhir)) {
                                    $totalQuery .= " AND DATE(bm.waktu) BETWEEN '$tanggalMulai' AND '$tanggalAkhir'";
                                } elseif (!empty($tanggalMulai)) {
                                    $totalQuery .= " AND DATE(bm.waktu) >= '$tanggalMulai'";
                                } elseif (!empty($tanggalAkhir)) {
                                    $totalQuery .= " AND DATE(bm.waktu) <= '$tanggalAkhir'";
                                }

                                if (!empty($keyword)) {
                                    $keyword_esc = mysqli_real_escape_string($koneksi, $keyword);
                                    $totalQuery .= " AND b.namabarang LIKE '%$keyword_esc%'";
                                }

                                $totalResult = mysqli_query($koneksi, $totalQuery);
                                $totalRow = mysqli_fetch_assoc($totalResult);
                                $totalBarang = $totalRow['total_barang'] ?? 0;

                                $query .= " ORDER BY bm.idbarang DESC";
                                $result = mysqli_query($koneksi, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr class='table-primary text-dark'>
                                <td>" . $no++ . "</td>
                                <td>{$row['idbarang']}</td>
                                <td>{$row['nama_barang']}</td>
                                <td>{$row['jumlah_masuk']}</td>
                                <td>{$row['waktu']}</td>
                                </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="mt-3 p-3 bg-white rounded shadow-sm border">
                            <div class="p-2 bg-white rounded shadow-sm border">
                                <h6 class="mb-0 fw-bold text-danger">Note:
                                    <span class="text-danger">Total dibawah ini terhubung dengan filter tanggal</span>
                                </h6>
                            </div>
                            <h6 class="mt-2 mb-0 p-2 fw-bold text-primary">Total Jumlah Barang Masuk:
                                <span class="text-dark"><?= $totalBarang ?> item</span>
                            </h6>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Modal Konfirmasi Logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Konfirmasi Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin keluar?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="<?= BASE_URL ?>/router.php?page=logout" class="btn btn-danger">Ya, Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CSS DataTables dengan Bootstrap 5 -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- jQuery (dibutuhkan DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- JS DataTables dan integrasi Bootstrap 5 -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.table').DataTable({
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Berikutnya",
                        "previous": "Sebelumnya"
                    },
                    "zeroRecords": "Data tidak ditemukan",
                    "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                    "infoFiltered": "(disaring dari _MAX_ total data)"
                }
            });
        });
    </script>

    <!-- Notif Logout -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const alertBox = document.getElementById('loginAlert');
            if (alertBox) {
                setTimeout(() => {
                    // Gunakan Bootstrap class untuk fade out, lalu hapus
                    alertBox.classList.remove('show');
                    alertBox.classList.add('fade');
                    setTimeout(() => alertBox.remove(), 500); // hapus dari DOM setelah animasi selesai
                }, 3000); // 3 detik
            }
        });
    </script>

    <!-- Baru Ditambahkan Semalam -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>

</html>