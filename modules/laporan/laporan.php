<?php
// Jangan akses langsung file ini dari URL
if (!defined('BASE_URL')) {
  die('Akses langsung tidak diizinkan');
}

// koneksi ke database
include __DIR__ . '/../../koneksi.php';

// Batasi akses hanya untuk Admin
include __DIR__ . '/../../role.php';
cekHakAkses(['Admin']);

// Cek session sudah dilakukan di router.php, jadi di sini kamu bisa yakin sudah login
// Sudah login karena masuk lewat router.php
$username = $_SESSION['username'] ?? null;

if (!$username) {
  // Fail safe jika session habis
  header("Location: " . BASE_URL . "/router.php?page=login");
  exit;
}

// Ambil data lengkap user dari database
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
  <title>Laporan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">

  <!-- Logo Icon Title -->
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/img/Logo Sidebar.png">
</head>

<body>
  <div class="container-fluid">
    <div class="row flex-nowrap">
      <!-- Sidebar -->
      <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

      <!-- Main Content -->
      <main class="col py-3 px-4">
        <!-- Baru Ditambahkan Semalam -->
        <button class="btn btn-outline-primary d-md-none mb-3" onclick="toggleSidebar()">
          <i class="bi bi-list"></i> Menu
        </button>

        <div class="d-flex justify-content-center align-items-center" style="height: 90vh;">
          <div class="border rounded shadow p-4" style="width: 500px;">
            <div class="border-bottom pb-2 mb-3 position-relative">
              <div class="d-flex align-items-center justify-content-center position-relative">
                <!-- Judul di Tengah -->
                <h5 class="mb-0 text-center flex-grow-1">Pilih Jenis Laporan</h5>

                <!-- Tombol Close -->
                <a href="<?= BASE_URL ?>/router.php?page=dashboard" class="btn-close position-absolute end-0"
                  aria-label="Close"></a>
              </div>
            </div>

            <div class="container">
              <div class="row g-3 justify-content-center">
                <div class="col-12 col-md-4 d-flex">
                  <a href="<?= BASE_URL ?>/router.php?page=laporan_databarang"
                    class="btn btn-primary w-100 shadow laporan-card text-center"
                    style="background: linear-gradient(to right, #6a11cb, #2575fc); border: none; height: 150px;">
                    <div class="laporan-content d-flex flex-column align-items-center justify-content-center"
                      style="gap: 10px;">
                      <i class="bi bi-box-seam" style="font-size: 2.5rem;"></i>
                      <span>Laporan Data Barang</span>
                    </div>
                  </a>
                </div>

                <div class="col-12 col-md-4 d-flex">
                  <a href="<?= BASE_URL ?>/router.php?page=laporan_barangmasuk"
                    class="btn btn-primary w-100 shadow laporan-card text-center"
                    style="background: linear-gradient(to right, #6a11cb, #2575fc); border: none; height: 150px;">
                    <div class="laporan-content d-flex flex-column align-items-center justify-content-center"
                      style="gap: 10px;">
                      <i class="bi bi-arrow-down-circle" style="font-size: 2.5rem;"></i>
                      <span>Laporan Barang Masuk</span>
                    </div>
                  </a>
                </div>

                <div class="col-12 col-md-4 d-flex">
                  <a href="<?= BASE_URL ?>/router.php?page=laporan_barangkeluar"
                    class="btn btn-primary w-100 shadow laporan-card text-center"
                    style="background: linear-gradient(to right, #6a11cb, #2575fc); border: none; height: 150px;">
                    <div class="laporan-content d-flex flex-column align-items-center justify-content-center"
                      style="gap: 10px;">
                      <i class="bi bi-arrow-up-circle" style="font-size: 2.5rem;"></i>
                      <span>Laporan Barang Keluar</span>
                    </div>
                  </a>
                </div>
              </div>
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