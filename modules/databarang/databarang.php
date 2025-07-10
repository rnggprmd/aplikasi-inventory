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
  <title>Data Barang</title>
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
          <h4 class="mt-3 mb-3 fw-bold">Barang</h4>
        </div>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mt-1">
          <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
            <li class="breadcrumb-item">
              <a href="<?= BASE_URL ?>/router.php?page=dashboard" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Data Barang</li>
          </ol>
        </nav>

        <div class="bg-white p-3 rounded mb-4 shadow-sm">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Data Barang</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahBarangModal">
              <i class="bi bi-plus-lg me-1"></i> Tambah Data
            </button>
          </div>

          <div class="table-responsive bg-light p-3 rounded shadow-sm mt-3">
            <table id="table-data-barang" class="table table-striped table-hover align-middle w-100">
              <thead class="table-light">
                <tr>
                  <th>No</th>
                  <th>Id Barang</th>
                  <th>Nama Barang</th>
                  <th>Jumlah Barang</th>
                  <th>Harga</th>
                  <th class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;

                $query = "SELECT * FROM databarang ORDER BY idbarang ASC";
                $result = mysqli_query($koneksi, $query);

                while ($row = mysqli_fetch_assoc($result)) {
                  echo "<tr class='table-primary text-dark'>";
                  echo "<td>" . $no++ . "</td>";
                  echo "<td>" . htmlspecialchars($row['idbarang']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['namabarang']) . "</td>";
                  echo "<td>" . (isset($row['stockbarang']) && $row['stockbarang'] !== null ? htmlspecialchars($row['stockbarang']) : '0') . "</td>";
                  echo "<td>Rp " . number_format($row['harga'], 0, ',', '.') . "</td>";
                  echo "<td class='text-center'>
                        <div class='d-flex justify-content-center gap-1'>
                          <button class='btn btn-warning btn-sm me-1' data-bs-toggle='modal' data-bs-target='#editModal" . preg_replace("/[^a-zA-Z0-9]/", "", $row['idbarang']) . "'>
                            <i class='bi bi-pencil'></i>
                          </button>
                          <button
                            class='btn btn-danger btn-sm btn-confirm-delete'
                            data-id='" . htmlspecialchars($row['idbarang']) . "'
                            data-nama='" . htmlspecialchars($row['namabarang']) . "'
                            data-bs-toggle='modal' data-bs-target='#modalConfirmDeleteBarang'>
                            <i class='bi bi-trash'></i>
                          </button>
                        </div>
                      </td>";
                  echo "</tr>";

                  // Modal Edit
                  echo "
                <div class='modal fade' id='editModal" . preg_replace("/[^a-zA-Z0-9]/", "", $row['idbarang']) . "' tabindex='-1' aria-labelledby='editModalLabel" . $row['idbarang'] . "' aria-hidden='true'>
                  <div class='modal-dialog'>
                    <div class='modal-content'>
                      <form class='form-edit-barang' action='" . BASE_URL . "/router.php?page=edit_barang' method='POST'>
                        <div class='modal-header'>
                          <h5 class='modal-title' id='editModalLabel" . $row['idbarang'] . "'>Edit Data Barang</h5>
                          <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
                        <div class='modal-body'>
                          <input type='hidden' name='idbarang' value='" . htmlspecialchars($row['idbarang']) . "' />
                          <div class='mb-3'>
                            <label for='namabarang" . $row['idbarang'] . "' class='form-label'>Nama Barang</label>
                            <input type='text' class='form-control' name='namabarang' id='namabarang" . $row['idbarang'] . "' value='" . htmlspecialchars($row['namabarang']) . "' required />
                          </div>
                          <div class='mb-3'>
                            <label for='harga" . $row['idbarang'] . "' class='form-label'>Harga</label>
                            <input type='text' class='form-control harga-display' data-id='" . $row['idbarang'] . "' value='Rp " . number_format($row['harga'], 0, ',', '.') . "' autocomplete='off' />
                            <input type='hidden' name='harga' class='harga-hidden' id='harga_hidden" . $row['idbarang'] . "' value='" . htmlspecialchars($row['harga']) . "' />
                          </div>
                        </div>
                        <div class='modal-footer'>
                          <button type='reset' class='btn btn-danger'>Reset</button>
                          <button type='submit' class='btn btn-primary'>Perbarui</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                ";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>

      <!-- Modal Tambah Barang -->
      <div class="modal fade" id="tambahBarangModal" tabindex="-1" aria-labelledby="tambahBarangModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <form id="formTambahBarang" action="<?= BASE_URL ?>/router.php?page=tambah_barang" method="POST" novalidate>
              <div class="modal-header">
                <h5 class="modal-title" id="tambahBarangModalLabel">Tambah Data Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
              </div>
              <div class="modal-body">
                <div class="table-responsive">
                  <table class="table table-bordered" id="tabel-barang">
                    <thead>
                      <tr>
                        <th>ID Barang</th>
                        <th>Nama Barang</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody id="tbody-barang">
                      <!-- Baris input akan ditambah JS -->
                    </tbody>
                  </table>
                </div>
                <button type="button" class="btn btn-success" id="tambahBarisBtn">+ Tambah Baris</button>
              </div>
              <div class="modal-footer">
                <button type="reset" class="btn btn-danger" id="resetFormBtn">Reset</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Hapus Barang -->
  <div class="modal fade" id="modalConfirmDeleteBarang" tabindex="-1" aria-labelledby="modalConfirmDeleteBarangLabel"
    aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modalConfirmDeleteBarangLabel">Konfirmasi Hapus</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="confirmDeleteBarangText">Apakah Anda yakin ingin menghapus barang ini?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <a href="#" id="btnDeleteConfirmBarang" class="btn btn-danger">Ya, Hapus</a>
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
    'sukses' => ['class' => 'text-bg-success', 'message' => 'Barang berhasil ditambahkan!'],
    'edit_sukses' => ['class' => 'text-bg-warning', 'message' => 'Barang berhasil diperbarui!'],
    'hapus_sukses' => ['class' => 'text-bg-danger', 'message' => 'Barang berhasil dihapus!']
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

  <!-- Script -->
  <script>
    $(document).ready(function () {
      $('#table-data-barang').DataTable({
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
    // Untuk modal tambah barang: reset kembalikan idbarang ke nilai default (generate terakhir)
    const tambahBarangModal = document.getElementById('tambahBarangModal');
    const inputIdBarangTambah = document.getElementById('idbarang');
    let defaultIdBarangTambah = '';

    $('#tambahBarangModal').on('show.bs.modal', function () {
      $.get('<?= BASE_URL ?>/modules/databarang/generate_idbarang.php', function (data) {
        inputIdBarangTambah.value = data;
        defaultIdBarangTambah = data;
      });
    });

    tambahBarangModal.querySelector('form').addEventListener('reset', function () {
      setTimeout(() => {
        inputIdBarangTambah.value = defaultIdBarangTambah;
      }, 0);
    });

    // Untuk modal edit barang: reset kembalikan form ke nilai awal saat modal dibuka
    document.querySelectorAll('.form-edit-barang').forEach(form => {
      let initialFormData = {};

      form.closest('.modal').addEventListener('show.bs.modal', () => {
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

  <script>
    document.querySelectorAll('.harga-display').forEach(function (input) {
      input.addEventListener('input', function () {
        const id = input.getAttribute('data-id');
        const hiddenInput = document.getElementById('harga_hidden' + id);

        let numericValue = this.value.replace(/[^0-9]/g, '');
        let formatted = numericValue ? 'Rp ' + new Intl.NumberFormat('id-ID').format(numericValue) : '';
        this.value = formatted;
        if (hiddenInput) hiddenInput.value = numericValue;
      });
    });
  </script>

  <!-- Alert Toast -->
  <script>
    if (
      window.location.search.includes("status=sukses") ||
      window.location.search.includes("status=edit_sukses") ||
      window.location.search.includes("status=hapus_sukses")
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
        const url = '<?= BASE_URL ?>/router.php?page=hapus_barang&id=' + encodeURIComponent(id);

        document.getElementById('confirmDeleteBarangText').innerText = `Apakah Anda yakin ingin menghapus barang "${nama}"?`;
        document.getElementById('btnDeleteConfirmBarang').href = url;
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

  <!-- Modal Tambah Data -->
  <script>
    let counter = 0;           // untuk ID baris tabel
    let idBarangCounter = 0;   // untuk generate ID barang, dari server nanti
    const idBarangPrefix = 'BRG';

    // Fungsi generate ID barang, format BRG001, BRG002, dst
    function generateIdBarangJS() {
      const id = idBarangPrefix + String(idBarangCounter).padStart(3, '0');
      idBarangCounter++;
      return id;
    }

    // Fungsi tambah baris input barang di tabel tbody
    function tambahBarisBarang() {
      const rowId = `row-${counter}`;
      const idBarang = generateIdBarangJS();

      const row = `
    <tr id="${rowId}">
      <td><input type="text" name="idbarang[]" class="form-control" value="${idBarang}" readonly></td>
      <td><input type="text" name="namabarang[]" class="form-control" required></td>
      <td>
        <input type="text" class="form-control harga-display" data-id="${counter}" required>
        <input type="hidden" name="harga[]" id="harga_hidden${counter}">
      </td>
      <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris('${rowId}')">Hapus</button></td>
    </tr>
  `;
      $('#tbody-barang').append(row);
      counter++;
    }

    // Fungsi hapus baris tabel berdasarkan id row
    function hapusBaris(id) {
      document.getElementById(id).remove();

      // Tambahkan ini untuk menjaga urutan ID tetap rapi
      if (idBarangCounter > 0) idBarangCounter--;
    }

    // Event tombol tambah baris
    $('#tambahBarisBtn').on('click', tambahBarisBarang);

    // Reset isi tbody dan counters saat modal tambah ditutup
    $('#tambahBarangModal').on('hidden.bs.modal', function () {
      $('#tbody-barang').empty();
      counter = 0;
      idBarangCounter = 0;
    });

    // Saat modal tambah dibuka, ambil ID terakhir dari server lalu tambah baris pertama
    $('#tambahBarangModal').on('show.bs.modal', function () {
      $('#tbody-barang').empty();
      counter = 0;

      $.get('<?= BASE_URL ?>/modules/databarang/generate_idbarang.php', function (data) {
        // Ambil nomor terakhir dari ID, misal BRG012 -> 12
        const lastNumber = parseInt(data.replace(/[^\d]/g, ''), 10);
        idBarangCounter = lastNumber || 0; // fallback kalau data invalid

        tambahBarisBarang(); // tambah baris pertama langsung
      });
    });

    // Format input harga dengan format Rp ribuan, update hidden input harga[]
    $(document).on('input', '.harga-display', function () {
      const id = $(this).data('id');
      let numericValue = this.value.replace(/[^0-9]/g, '');
      let formatted = numericValue ? 'Rp ' + new Intl.NumberFormat('id-ID').format(numericValue) : '';
      this.value = formatted;
      $(`#harga_hidden${id}`).val(numericValue);
    });
  </script>

</body>

</html>