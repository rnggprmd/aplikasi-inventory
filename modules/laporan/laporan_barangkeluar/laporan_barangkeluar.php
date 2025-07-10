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
    <title>Laporan Barang Keluar</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Laporan Barang Keluar</li>
                    </ol>
                </nav>

                <!-- Laporan Barang Keluar -->
                <div class="bg-white rounded p-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-2">Laporan Barang Keluar</h5>
                    </div>

                    <!-- Filter -->
                    <form method="GET" action="router.php" id="filterForm">
                        <input type="hidden" name="page" value="laporan_barangkeluar">
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

                            <!-- Tombol Export -->
                            <div class="col-md-6 mb-1 text-md-end">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <div class="d-flex justify-content-md-end gap-2">
                                    <a href="<?= BASE_URL ?>/router.php?page=excel_barangkeluar&tanggalMulai=<?= urlencode($_GET['tanggalMulai'] ?? '') ?>&tanggalAkhir=<?= urlencode($_GET['tanggalAkhir'] ?? '') ?>&filter_customer=<?= urlencode($_GET['filter_customer'] ?? '') ?>"
                                        class="btn btn-success" target="_blank">Export Excel</a>
                                    <a href="<?= BASE_URL ?>/router.php?page=pdf_barangkeluar&tanggalMulai=<?= urlencode($_GET['tanggalMulai'] ?? '') ?>&tanggalAkhir=<?= urlencode($_GET['tanggalAkhir'] ?? '') ?>&filter_customer=<?= urlencode($_GET['filter_customer'] ?? '') ?>"
                                        class="btn btn-danger" target="_blank">Export PDF</a>
                                </div>
                            </div>

                            <!-- Tombol Clear dan Tampilkan -->
                            <div class="col-md-6 mb-2">
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <a href="router.php?page=laporan_barangkeluar"
                                            class="btn btn-secondary w-100">Clear</a>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Dropdown Customer -->
                            <div class="col-md-6 mb-2 text-md-end d-flex justify-content-md-end align-items-center">
                                <select name="filter_customer" class="form-select" style="width: 230px;"
                                    onchange="document.getElementById('filterForm').dispatchEvent(new Event('submit'))">
                                    <option value="">-- Semua Customer --</option>
                                    <?php
                                    $customers = mysqli_query($koneksi, "SELECT * FROM customer");
                                    $selectedCustomer = $_GET['filter_customer'] ?? '';
                                    while ($cust = mysqli_fetch_assoc($customers)) {
                                        $selected = ($selectedCustomer == $cust['id_customer']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($cust['id_customer']) . "' $selected>" . htmlspecialchars($cust['nama_customer']) . "</option>";
                                    }
                                    ?>
                                </select>
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
                                    <th>Jumlah Barang Keluar</th>
                                    <th>Harga</th>
                                    <th>Waktu</th>
                                    <th>Id Customer</th>
                                    <th>Nama Customer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                // Ambil nilai filter dari GET dan escape
                                $tanggalMulai = isset($_GET['tanggalMulai']) ? $_GET['tanggalMulai'] : '';
                                $tanggalAkhir = isset($_GET['tanggalAkhir']) ? $_GET['tanggalAkhir'] : '';
                                $filterCustomer = isset($_GET['filter_customer']) ? mysqli_real_escape_string($koneksi, $_GET['filter_customer']) : '';

                                // Mulai bangun query dasar
                                $query = "SELECT bk.*, b.namabarang AS nama_barang, b.harga, c.nama_customer 
                            FROM barang_keluar bk
                            LEFT JOIN databarang b ON bk.idbarang = b.idbarang
                            LEFT JOIN customer c ON bk.id_customer = c.id_customer
                            WHERE 1=1 ";

                                // Button Filter
                                // Filter tanggal
                                if (!empty($tanggalMulai) && !empty($tanggalAkhir)) {
                                    // Validasi tanggal format YYYY-MM-DD jika perlu sebelum digunakan
                                    $query .= " AND DATE(bk.waktu) BETWEEN '$tanggalMulai' AND '$tanggalAkhir' ";
                                } elseif (!empty($tanggalMulai)) {
                                    $query .= " AND DATE(bk.waktu) >= '$tanggalMulai' ";
                                } elseif (!empty($tanggalAkhir)) {
                                    $query .= " AND DATE(bk.waktu) <= '$tanggalAkhir' ";
                                }
                                // Button Filter
                                
                                // Button Customer
                                // Filter Customer
                                if (!empty($filterCustomer)) {
                                    // escape string untuk aman
                                    $filterCustomerEscaped = mysqli_real_escape_string($koneksi, $filterCustomer);
                                    $query .= " AND bk.id_customer = '$filterCustomerEscaped' ";
                                }
                                // Button Customer
                                
                                // Penghitungan untuk Jumlah Barang Keluar
                                // Hitung total jumlah barang keluar
                                $totalJumlahQuery = "SELECT SUM(bk.jumlah_keluar) AS total_keluar
                            FROM barang_keluar bk
                            LEFT JOIN databarang b ON bk.idbarang = b.idbarang
                            LEFT JOIN customer c ON bk.id_customer = c.id_customer
                            WHERE 1=1";

                                // Filter tanggal
                                if (!empty($tanggalMulai) && !empty($tanggalAkhir)) {
                                    $totalJumlahQuery .= " AND DATE(bk.waktu) BETWEEN '$tanggalMulai' AND '$tanggalAkhir'";
                                } elseif (!empty($tanggalMulai)) {
                                    $totalJumlahQuery .= " AND DATE(bk.waktu) >= '$tanggalMulai'";
                                } elseif (!empty($tanggalAkhir)) {
                                    $totalJumlahQuery .= " AND DATE(bk.waktu) <= '$tanggalAkhir'";
                                }

                                // Filter customer
                                if (!empty($filterCustomer)) {
                                    $filterCustomerEscaped = mysqli_real_escape_string($koneksi, $filterCustomer);
                                    $totalJumlahQuery .= " AND bk.id_customer = '$filterCustomerEscaped'";
                                }

                                $totalJumlahResult = mysqli_query($koneksi, $totalJumlahQuery);
                                $totalJumlahRow = mysqli_fetch_assoc($totalJumlahResult);
                                $totalBarangKeluar = $totalJumlahRow['total_keluar'] ?? 0;
                                // Penghitungan untuk Jumlah Barang Keluar
                                
                                // Penghitugan untuk Harga Total
                                $totalHargaQuery = "SELECT SUM(bk.jumlah_keluar * b.harga) AS total_harga
                            FROM barang_keluar bk
                            LEFT JOIN databarang b ON bk.idbarang = b.idbarang
                            WHERE 1=1";

                                // Filter tanggal
                                if (!empty($tanggalMulai) && !empty($tanggalAkhir)) {
                                    $totalHargaQuery .= " AND DATE(bk.waktu) BETWEEN '$tanggalMulai' AND '$tanggalAkhir' ";
                                } elseif (!empty($tanggalMulai)) {
                                    $totalHargaQuery .= " AND DATE(bk.waktu) >= '$tanggalMulai' ";
                                } elseif (!empty($tanggalAkhir)) {
                                    $totalHargaQuery .= " AND DATE(bk.waktu) <= '$tanggalAkhir' ";
                                }

                                // Filter customer
                                if (!empty($filterCustomer)) {
                                    $filterCustomerEscaped = mysqli_real_escape_string($koneksi, $filterCustomer);
                                    $totalHargaQuery .= " AND bk.id_customer = '$filterCustomerEscaped' ";
                                }

                                $totalHargaResult = mysqli_query($koneksi, $totalHargaQuery);
                                $totalHargaRow = mysqli_fetch_assoc($totalHargaResult);
                                $totalHarga = $totalHargaRow['total_harga'] ?? 0;
                                // Penghitugan untuk Harga Total
                                
                                $query .= " ORDER BY bk.idbarang DESC";
                                $result = mysqli_query($koneksi, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr class='table-primary text-dark'>
                                        <td>" . $no++ . "</td>
                                        <td>{$row['idbarang']}</td>
                                        <td>{$row['nama_barang']}</td>
                                        <td>{$row['jumlah_keluar']}</td>
                                        <td>Rp. " . number_format($row['harga'], 0, ',', '.') . "</td>
                                        <td>{$row['waktu']}</td>
                                        <td>{$row['id_customer']}</td>
                                        <td>" . htmlspecialchars($row['nama_customer']) . "</td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="mt-3 p-3 bg-white rounded shadow-sm border">
                            <div class="p-2 bg-white rounded shadow-sm border">
                                <h6 class="mb-0 fw-bold text-danger">Note:
                                    <span class="text-danger">Total dibawah ini terhubung dengan filter tanggal dan
                                        customer</span>
                                </h6>
                            </div>
                            <h6 class="mt-2 mb-0 p-2 fw-bold text-primary">Total Jumlah Barang Keluar:
                                <span class="text-dark"><?= $totalBarangKeluar ?> unit</span>
                            </h6>
                            <h6 class="mb-0 p-2 fw-bold text-primary">Total Harga Barang Keluar:
                                <span class="text-dark">Rp. <?= number_format($totalHarga, 0, ',', '.') ?></span>
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

    <script>
        document.getElementById('filterForm').addEventListener('submit', function (e) {
            e.preventDefault();  // cegah submit default

            const form = e.target;
            const params = new URLSearchParams();

            // selalu sertakan page karena wajib
            params.append('page', form.page.value);

            // ambil input yang lain
            const tanggalMulai = form.tanggalMulai.value.trim();
            const tanggalAkhir = form.tanggalAkhir.value.trim();
            const filterCustomer = form.filter_customer.value.trim();

            if (tanggalMulai !== '') {
                params.append('tanggalMulai', tanggalMulai);
            }
            if (tanggalAkhir !== '') {
                params.append('tanggalAkhir', tanggalAkhir);
            }
            if (filterCustomer !== '') {
                params.append('filter_customer', filterCustomer);
            }

            // redirect dengan query string yang sudah dibersihkan
            window.location.href = 'router.php?' + params.toString();
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