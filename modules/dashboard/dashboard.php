<?php
// Jangan akses langsung file ini dari URL
if (!defined('BASE_URL')) {
  die('Akses langsung tidak diizinkan');
}

// koneksi ke database
include __DIR__ . '/../../koneksi.php';

// Cek session sudah dilakukan di router.php, jadi di sini kamu bisa yakin sudah login
// Sudah login karena masuk lewat router.php
$username = $_SESSION['username'] ?? null;

if (!$username) {
  // Fail safe jika session habis
  header("Location: " . BASE_URL . "/router.php?page=login");
  exit;
}

// Ambil data user
$stmt = $koneksi->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Jika user tidak ditemukan
if (!$user) {
  session_destroy();
  header("Location: " . BASE_URL . "/router.php?page=login");
  exit;
}

// Ambil notifikasi login sukses
$loginSuccess = false;
if (isset($_SESSION['login_success']) && $_SESSION['login_success']) {
  $loginSuccess = true;
  unset($_SESSION['login_success']); // Hanya tampilkan sekali
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <title>Dashboard</title>
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

        <div class="bg-white p-3 rounded mb-4 text-center shadow-sm">
          <h4 class="mt-3 mb-3 fw-bold">Dashboard</h4>
        </div>

        <!-- Notifikasi Berhasil Login -->
        <?php if ($loginSuccess): ?>
          <div id="loginAlert" class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Selamat datang!</strong> Anda berhasil login.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <!-- Info Cards --><!-- 2x2 Grid Cards -->
        <section class="row g-3 mb-4">
          <div class="col-12 col-md-6">
            <div class="bg-white p-3 rounded text-center shadow-sm">
              <i class="bi bi-people-fill fs-2 mb-2"></i>
              <h5 class="fw-bold" id="jumlah-customer">0</h5>
              <p class="mb-0">Customer</p>
              <div class="card-footer-link">
                <a href="<?= BASE_URL ?>/router.php?page=customer">Lihat Detail</a>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="bg-white p-3 rounded text-center shadow-sm">
              <i class="bi bi-archive-fill fs-2 mb-2"></i>
              <h5 class="fw-bold" id="jumlah-databarang">0</h5>
              <p class="mb-0">Data Barang</p>
              <div class="card-footer-link">
                <a href="<?= BASE_URL ?>/router.php?page=databarang">Lihat Detail</a>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="bg-white p-3 rounded text-center shadow-sm">
              <i class="bi bi-arrow-down-circle-fill fs-2 mb-2"></i>
              <h5 class="fw-bold" id="jumlah-barangmasuk">0</h5>
              <p class="mb-0">Barang Masuk</p>
              <div class="card-footer-link">
                <a href="<?= BASE_URL ?>/router.php?page=barangmasuk">Lihat Detail</a>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="bg-white p-3 rounded text-center shadow-sm">
              <i class="bi bi-arrow-up-circle-fill fs-2 mb-2"></i>
              <h5 class="fw-bold" id="jumlah-barangkeluar">0</h5>
              <p class="mb-0">Barang Keluar</p>
              <div class="card-footer-link">
                <a href="<?= BASE_URL ?>/router.php?page=barangkeluar">Lihat Detail</a>
              </div>
            </div>
          </div>
        </section>

        <!-- Grafik -->
        <section class="bg-white p-4 rounded shadow-sm">
          <h5 class="fw-bold mb-3">Grafik Barang Masuk dan Barang Keluar</h5>
          <canvas id="barangChart" height="100"></canvas>
        </section>
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

  <!-- CDN & Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/assets/scripts.js"></script>

  <!-- Dashboard Data & Chart -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      // Ambil data statistik dashboard
      fetch("<?= BASE_URL ?>/router.php?page=dashboard_data")
        .then(response => response.json())
        .then(data => {
          document.getElementById("jumlah-databarang").textContent = data.databarang ?? 0;
          document.getElementById("jumlah-barangmasuk").textContent = data.barangmasuk ?? 0;
          document.getElementById("jumlah-barangkeluar").textContent = data.barangkeluar ?? 0;
          document.getElementById("jumlah-customer").textContent = data.customer ?? 0;
        })
        .catch(error => {
          console.error("Gagal mengambil data dashboard:", error);
        });

      // Ambil data grafik
      fetch("<?= BASE_URL ?>/router.php?page=dashboard_grafik_data")
        .then(res => res.json())
        .then(data => {
          const ctx = document.getElementById("barangChart").getContext("2d");
          new Chart(ctx, {
            type: "line",
            data: {
              labels: data.labels,
              datasets: [
                {
                  label: "Barang Masuk",
                  data: data.barang_masuk,
                  borderColor: "green",
                  backgroundColor: "rgba(0,128,0,0.1)",
                  tension: 0.4
                },
                {
                  label: "Barang Keluar",
                  data: data.barang_keluar,
                  borderColor: "red",
                  backgroundColor: "rgba(255,0,0,0.1)",
                  tension: 0.4
                }
              ]
            },
            options: {
              responsive: true,
              plugins: {
                legend: { position: "top" },
                title: { display: true, text: "Berdasarkan per Tanggal" }
              }
            }
          });
        })
        .catch(error => {
          console.error("Gagal mengambil data grafik:", error);
        });
    });
  </script>

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