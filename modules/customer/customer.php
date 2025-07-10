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
  <title>Customer</title>
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
      <?php include __DIR__ . '/../../includes/sidebar.php'; ?>

      <!-- Main Content -->
      <main class="col py-3 px-4">
        <!-- Baru Ditambahkan Semalam -->
        <button class="btn btn-outline-primary d-md-none mb-3" onclick="toggleSidebar()">
          <i class="bi bi-list"></i> Menu
        </button>
        <!-- Header -->
        <div class="bg-white p-3 rounded mb-2 text-center shadow-sm">
          <h4 class="mt-3 mb-3 fw-bold">Customer</h4>
        </div>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mt-1">
          <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
            <li class="breadcrumb-item">
              <a href="<?= BASE_URL ?>/router.php?page=dashboard" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Customer</li>
          </ol>
        </nav>

        <!-- content all customer -->
        <div class="bg-white p-3 rounded mb-4 shadow-sm">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Data Customer</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahCustomer">
              <i class="bi bi-plus-lg me-1"></i> Tambah Data
            </button>
          </div>

          <div class="table-responsive bg-light p-3 rounded shadow-sm mt-3">
            <table id="tableCustomer" class="table table-striped table-hover align-middle w-100">
              <thead class="table-light">
                <tr>
                  <th>No</th>
                  <th>Id Customer</th>
                  <th>Nama Customer</th>
                  <th>Alamat</th>
                  <th>No Telepon</th>
                  <th class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                $query = "SELECT * FROM customer";
                $result = mysqli_query($koneksi, $query);
                while ($row = mysqli_fetch_assoc($result)):
                  ?>
                  <tr class="table-primary text-dark">
                    <td><?= $no ?></td>
                    <td><?= htmlspecialchars($row['id_customer']) ?></td>
                    <td><?= htmlspecialchars($row['nama_customer']) ?></td>
                    <td><?= htmlspecialchars($row['alamat']) ?></td>
                    <td><?= htmlspecialchars($row['no_telp']) ?></td>
                    <td class="text-center">
                      <div class='d-flex justify-content-center gap-1'>
                        <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal"
                          data-bs-target="#editCustomerModal<?= $row['id_customer'] ?>">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btn-confirm-delete"
                          data-id="<?= htmlspecialchars($row['id_customer']) ?>"
                          data-nama="<?= htmlspecialchars($row['nama_customer']) ?>" data-bs-toggle="modal"
                          data-bs-target="#modalConfirmDelete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>

                  <!-- Modal Edit Customer -->
                  <div class="modal fade" id="editCustomerModal<?= $row['id_customer'] ?>" tabindex="-1"
                    aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <form action="<?= BASE_URL ?>/router.php?page=edit_customer" method="POST">
                          <div class="modal-header">
                            <h5 class="modal-title">Edit Data Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <input type="hidden" name="id_customer" value="<?= $row['id_customer'] ?>" />
                            <div class="mb-3">
                              <label class="form-label">Nama Customer</label>
                              <input type="text" class="form-control" name="nama_customer"
                                value="<?= htmlspecialchars($row['nama_customer']) ?>" required />
                            </div>
                            <div class="mb-3">
                              <label class="form-label">Alamat</label>
                              <textarea class="form-control" name="alamat"
                                required><?= htmlspecialchars($row['alamat']) ?></textarea>
                            </div>
                            <div class="mb-3">
                              <label class="form-label">No Telepon</label>
                              <input type="text" class="form-control" name="no_telp"
                                value="<?= htmlspecialchars($row['no_telp']) ?>" required />
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="reset" class="btn btn-danger">Reset</button>
                            <button type="submit" class="btn btn-primary">Perbarui</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  <?php
                  $no++;
                endwhile;
                ?>
              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- Modal Tambah Customer -->
  <div class="modal fade" id="modalTambahCustomer" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="<?= BASE_URL ?>/router.php?page=tambah_customer" method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Tambah Customer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label>Id Customer</label>
              <input type="text" name="id_customer" id="id_customer" class="form-control" readonly required />
            </div>
            <div class="mb-3">
              <label>Nama Customer</label>
              <input type="text" name="nama_customer" class="form-control" placeholder="Masukkan Nama Customer"
                required />
            </div>
            <div class="mb-3">
              <label>Alamat</label>
              <textarea name="alamat" class="form-control" placeholder="Masukkan Alamat" required></textarea>
            </div>
            <div class="mb-3">
              <label>No Telepon</label>
              <input type="text" name="no_telp" class="form-control" placeholder="Masukkan No Telepon" required />
            </div>
          </div>
          <div class="modal-footer">
            <button type="reset" class="btn btn-danger">Reset</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Hapus -->
  <div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-labelledby="modalConfirmDeleteLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modalConfirmDeleteLabel">Konfirmasi Hapus</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="confirmDeleteText">Apakah Anda yakin ingin menghapus customer ini?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <a href="#" id="btnDeleteConfirm" class="btn btn-danger">Ya, Hapus</a>
        </div>
      </div>
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

  <!-- Alert Toast Tambah, Edit dan Hapus -->
  <?php
  $toastMessages = [
    'sukses' => ['class' => 'text-bg-success', 'message' => 'Customer berhasil ditambahkan!'],
    'edit_sukses' => ['class' => 'text-bg-warning', 'message' => 'Customer berhasil diperbarui!'],
    'hapus_sukses' => ['class' => 'text-bg-danger', 'message' => 'Customer berhasil dihapus!'],
    'hapus_gagal' => ['class' => 'text-bg-secondary', 'message' => 'Customer gagal dihapus karena masih memiliki transaksi.']
  ];

  if (isset($_GET['status']) && array_key_exists($_GET['status'], $toastMessages)):
    $toast = $toastMessages[$_GET['status']];
    ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
      <div class="toast align-items-center <?= $toast['class'] ?> border-0 show" role="alert" aria-live="assertive"
        aria-atomic="true" data-bs-delay="2000" data-bs-autohide="true">
        <div class="d-flex">
          <div class="toast-body">
            <?= $toast['message'] ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Close"></button>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <!-- CSS DataTables dengan Bootstrap 5 -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">

  <!-- jQuery (dibutuhkan DataTables) -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

  <!-- JS DataTables dan integrasi Bootstrap 5 -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- Script DataTables -->
  <script>
    $(document).ready(function () {
      $('#tableCustomer').DataTable({
        language: {
          search: "Cari:",
          lengthMenu: "Tampilkan _MENU_ data per halaman",
          info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
          paginate: {
            first: "Pertama",
            last: "Terakhir",
            next: "Berikutnya",
            previous: "Sebelumnya"
          },
          zeroRecords: "Data tidak ditemukan",
          infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
          infoFiltered: "(disaring dari _MAX_ total data)"
        }
      });
    });
  </script>

  <!-- Script modal tambah reset -->
  <script>
    const modalTambahCustomer = document.getElementById('modalTambahCustomer');
    const idCustomerInput = document.getElementById('id_customer');
    let defaultIdCustomer = '';

    $('#modalTambahCustomer').on('show.bs.modal', function () {
      $.get('<?= BASE_URL ?>/router.php?page=generate_idcustomer', function (data) {
        idCustomerInput.value = data;
        defaultIdCustomer = data;
      });
    });

    modalTambahCustomer.querySelector('form').addEventListener('reset', function () {
      setTimeout(() => {
        idCustomerInput.value = defaultIdCustomer;
      }, 0);
    });
  </script>

  <!-- Script modal edit reset -->
  <script>
    document.querySelectorAll('.modal.fade[id^="editCustomerModal"]').forEach(modal => {
      const form = modal.querySelector('form');
      let initialFormData = {};

      modal.addEventListener('show.bs.modal', () => {
        initialFormData = {};
        form.querySelectorAll('input, textarea').forEach(input => {
          initialFormData[input.name] = input.value;
        });
      });

      form.addEventListener('reset', () => {
        setTimeout(() => {
          Object.entries(initialFormData).forEach(([name, value]) => {
            const input = form.querySelector(`[name="${name}"]`);
            if (input) input.value = value;
          });
        }, 0);
      });
    });
  </script>

  <!-- Alert Toast remove status from URL -->
  <script>
    if (
      window.location.search.includes("status=sukses") ||
      window.location.search.includes("status=edit_sukses") ||
      window.location.search.includes("status=hapus_sukses") ||
      window.location.search.includes("status=hapus_gagal")
    ) {
      setTimeout(() => {
        const url = new URL(window.location.href);
        url.searchParams.delete("status");
        window.history.replaceState({}, document.title, url.pathname + url.search);
      }, 2000);
    }
  </script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const toastEl = document.querySelector('.toast');
      if (toastEl) {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
      }
    });
  </script>

  <!-- Tombol Hapus -->
  <script>
    document.querySelectorAll('.btn-confirm-delete').forEach(button => {
      button.addEventListener('click', () => {
        const id = button.dataset.id;
        const nama = button.dataset.nama;
        const url = '<?= BASE_URL ?>/router.php?page=hapus_customer&id=' + encodeURIComponent(id);

        document.getElementById('confirmDeleteText').innerText = `Apakah Anda yakin ingin menghapus customer "${nama}"?`;
        document.getElementById('btnDeleteConfirm').href = url;
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