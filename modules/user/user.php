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

$stmtUserLogin = $koneksi->prepare("SELECT * FROM users WHERE username = ?");
$stmtUserLogin->bind_param("s", $username);
$stmtUserLogin->execute();
$resultUserLogin = $stmtUserLogin->get_result();
$user = $resultUserLogin->fetch_assoc();
$stmtUserLogin->close();  // jangan lupa close setelah selesai
if (!$user) {
  session_destroy();
  header("Location: " . BASE_URL . "/router.php?page=login");
  exit;
}

// Ambil semua user untuk ditampilkan di tabel
$stmt = $koneksi->prepare("SELECT * FROM users ORDER BY username ASC");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <title>User</title>
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

        <!-- Judul Halaman -->
        <div class="bg-white p-3 rounded mb-2 text-center shadow-sm">
          <h4 class="mt-3 mb-3 fw-bold">User</h4>
        </div>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mt-1">
          <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
            <li class="breadcrumb-item">
              <a href="<?= BASE_URL ?>/router.php?page=dashboard" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">User</li>
          </ol>
        </nav>

        <!-- Konten Data Pengguna -->
        <div class="bg-white p-3 rounded mb-4 shadow-sm">
          <!-- Header & Tombol Tambah -->
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Data Pengguna</h5>
            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
              <i class="bi bi-plus-lg me-1"></i> Tambah Data
            </a>
          </div>

          <!-- Tabel Data Pengguna -->
          <div class="table-responsive bg-light p-3 rounded shadow-sm mt-3">
            <table class="table table-striped table-hover align-middle w-100">
              <thead class="table-light">
                <tr>
                  <th>No</th>
                  <th>Nama Pengguna</th>
                  <th>Email</th>
                  <th>Jabatan</th>
                  <th>Hak Akses</th>
                  <th class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1;
                while ($userData = $result->fetch_assoc()) { ?>
                  <tr class="table-primary text-dark">
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($userData['username']); ?></td>
                    <td><?= htmlspecialchars($userData['email']); ?></td>
                    <td><?= htmlspecialchars($userData['jabatan']); ?></td>
                    <td><?= htmlspecialchars($userData['hak_akses']); ?></td>
                    <td class="text-center">
                      <div class='d-flex justify-content-center gap-1'>
                        <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalEdit"
                          data-id="<?= $userData['id']; ?>"
                          data-username="<?= htmlspecialchars($userData['username']); ?>"
                          data-email="<?= htmlspecialchars($userData['email']); ?>"
                          data-jabatan="<?= htmlspecialchars($userData['jabatan']); ?>"
                          data-hakakses="<?= htmlspecialchars($userData['hak_akses']); ?>">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btn-confirm-delete" data-id="<?= $userData['id']; ?>"
                          data-username="<?= htmlspecialchars($userData['username']); ?>" data-bs-toggle="modal"
                          data-bs-target="#modalConfirmDeleteUser">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Modal Tambah User -->
        <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form action="<?= BASE_URL ?>/router.php?page=tambah_user" method="POST">
                <div class="modal-header">
                  <h5 class="modal-title">Tambah User</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-2">
                    <label class="form-label">Nama Pengguna</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan Nama Pengguna"
                      required />
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Masukkan Email" required />
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Kata Sandi</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan Kata Sandi"
                      required />
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" class="form-control" placeholder="Masukkan Jabatan" required />
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Hak Akses</label>
                    <select name="hak_akses" class="form-control" required>
                      <option value="">-- Pilih Hak Akses --</option>
                      <option value="Admin">Admin</option>
                      <option value="Staff">Staff</option>
                    </select>
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

        <!-- Modal Edit User -->
        <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form action="<?= BASE_URL ?>/router.php?page=edit_user" method="POST">
                <div class="modal-header">
                  <h5 class="modal-title">Edit User</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" id="edit-id" />
                  <div class="mb-2">
                    <label class="form-label">Nama Pengguna</label>
                    <input type="text" name="username" id="edit-username" class="form-control" required />
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="edit-email" class="form-control" required />
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" id="edit-password" class="form-control"
                      placeholder="Kosongkan jika tidak ingin diubah" />
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" id="edit-jabatan" class="form-control" required />
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Hak Akses</label>
                    <select name="hak_akses" id="edit-hakakses" class="form-control" required>
                      <option value="">-- Pilih Hak Akses --</option>
                      <option value="Admin">Admin</option>
                      <option value="Staff">Staff</option>
                    </select>
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

      </main>
    </div>
  </div>

  <!-- Modal Konfirmasi Hapus User -->
  <div class="modal fade" id="modalConfirmDeleteUser" tabindex="-1" aria-labelledby="modalConfirmDeleteUserLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modalConfirmDeleteUserLabel">Konfirmasi Hapus</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="confirmDeleteUserText">Apakah Anda yakin ingin menghapus user ini?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <a href="#" id="btnDeleteUserConfirm" class="btn btn-danger">Ya, Hapus</a>
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
    'sukses' => ['class' => 'text-bg-success', 'message' => 'User berhasil ditambahkan!'],
    'edit_sukses' => ['class' => 'text-bg-warning', 'message' => 'User berhasil diperbarui!'],
    'hapus_sukses' => ['class' => 'text-bg-danger', 'message' => 'User berhasil dihapus!']
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
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
  <!-- jQuery (dibutuhkan DataTables) -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <!-- JS DataTables dan integrasi Bootstrap 5 -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <!-- Script -->
  <script>
    $(document).ready(function () {
      $('.table').DataTable({
        language: {
          search: 'Cari:',
          lengthMenu: 'Tampilkan _MENU_ data per halaman',
          info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
          paginate: {
            first: 'Pertama',
            last: 'Terakhir',
            next: 'Berikutnya',
            previous: 'Sebelumnya',
          },
          zeroRecords: 'Data tidak ditemukan',
          infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
          infoFiltered: '(disaring dari _MAX_ total data)',
        },
      });
    });
  </script>

  <script>
    const modalEdit = document.getElementById('modalEdit');
    let originalValues = {}; // untuk menyimpan nilai awal

    modalEdit.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;

      modalEdit.querySelector('#edit-id').value = button.getAttribute('data-id');
      modalEdit.querySelector('#edit-username').value = button.getAttribute('data-username');
      modalEdit.querySelector('#edit-email').value = button.getAttribute('data-email');
      modalEdit.querySelector('#edit-password').value = '';
      modalEdit.querySelector('#edit-jabatan').value = button.getAttribute('data-jabatan');
      modalEdit.querySelector('#edit-hakakses').value = button.getAttribute('data-hakakses');

      // Simpan nilai asli
      originalValues = {
        id: button.getAttribute('data-id'),
        username: button.getAttribute('data-username'),
        email: button.getAttribute('data-email'),
        password: '',
        jabatan: button.getAttribute('data-jabatan'),
        hakakses: button.getAttribute('data-hakakses'),
      };
    });

    // Tangani tombol reset pada form modal edit
    const modalEditForm = modalEdit.querySelector('form');
    const resetButton = modalEditForm.querySelector('button[type="reset"]');

    resetButton.addEventListener('click', (e) => {
      e.preventDefault(); // cegah reset otomatis

      modalEdit.querySelector('#edit-id').value = originalValues.id;
      modalEdit.querySelector('#edit-username').value = originalValues.username;
      modalEdit.querySelector('#edit-email').value = originalValues.email;
      modalEdit.querySelector('#edit-password').value = originalValues.password;
      modalEdit.querySelector('#edit-jabatan').value = originalValues.jabatan;
      modalEdit.querySelector('#edit-hakakses').value = originalValues.hakakses;
    });

    // Reset Otomatis ketika modal tambah di close
    const tambahModal = document.getElementById('modalTambah');
    tambahModal.addEventListener('hidden.bs.modal', () => {
      tambahModal.querySelector('form').reset();
    });
  </script>

  <!-- Alert Toast -->
  <script>
    if (
      window.location.search.includes('status=sukses') ||
      window.location.search.includes('status=edit_sukses') ||
      window.location.search.includes('status=hapus_sukses')
    ) {
      setTimeout(() => {
        const url = new URL(window.location.href);
        url.searchParams.delete('status');
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

  <!-- Script untuk tombol hapus user -->
  <script>
    document.querySelectorAll('.btn-confirm-delete').forEach((button) => {
      button.addEventListener('click', () => {
        const id = button.dataset.id;
        const username = button.dataset.username;
        const url = '<?= BASE_URL ?>/router.php?page=hapus_user&id=' + encodeURIComponent(id);

        document.getElementById('confirmDeleteUserText').innerText = `Apakah Anda yakin ingin menghapus user "${username}"?`;
        document.getElementById('btnDeleteUserConfirm').href = url;
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