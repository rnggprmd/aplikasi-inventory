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
  <title>Laporan Data Barang</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
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

        <div class="bg-white p-3 rounded mb-2 text-center shadow-sm">
          <h4 class="mt-3 mb-3 fw-bold">Laporan</h4>
        </div>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mt-1">
          <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
            <li class="breadcrumb-item">
              <a href="<?= BASE_URL ?>/router.php?page=dashboard" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Laporan Data Barang</li>
          </ol>
        </nav>

        <!-- Laporan Data Barang -->
        <div class="bg-white rounded p-4 shadow-sm">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-2">Laporan Data Barang</h5>
          </div>

          <!-- Tombol Export -->
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div class="d-flex justify-content-md-end gap-2">
              <a href="<?= BASE_URL ?>/router.php?page=excel_databarang" class="btn btn-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
              </a>
              <a href="<?= BASE_URL ?>/router.php?page=pdf_databarang" class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
              </a>
            </div>
          </div>

          <!-- Tabel Data Barang -->
          <div class="table-responsive bg-light p-3 rounded shadow-sm mt-3">
            <table class="table table-striped table-hover align-middle w-100">
              <thead class="table-light">
                <tr>
                  <th>No</th>
                  <th>Id Barang</th>
                  <th>Nama Barang</th>
                  <th>Stok</th>
                  <th>Harga</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                $query = "SELECT * FROM databarang ORDER BY idbarang ASC";

                // Hitung jumlah total barang
                $queryTotal = "SELECT COUNT(*) AS total_barang FROM databarang";
                $resultTotal = mysqli_query($koneksi, $queryTotal);
                $rowTotal = mysqli_fetch_assoc($resultTotal);
                $totalBarang = $rowTotal['total_barang'];

                $result = mysqli_query($koneksi, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                  echo "<tr class='table-primary text-dark'>";
                  echo "<td>" . $no++ . "</td>";
                  echo "<td>" . htmlspecialchars($row['idbarang']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['namabarang']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['stockbarang']) . "</td>";
                  echo "<td>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>";
                  echo "</tr>";
                }
                ?>
              </tbody>
            </table>
            <div class="mt-3 p-3 bg-white rounded shadow-sm border">
              <div class="p-2 bg-white rounded shadow-sm border">
                <h6 class="mb-0 fw-bold text-danger">Note:
                  <span class="text-danger">Total dibawah ini terhubung dengan tabel</span>
                </h6>
              </div>
              <h6 class="mt-2 mb-0 p-2 fw-bold text-primary">Total Jumlah Data Barang:
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