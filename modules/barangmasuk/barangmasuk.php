<?php
date_default_timezone_set('Asia/Jakarta');

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

$jsBarangArray = [];
$result = mysqli_query($koneksi, "SELECT idbarang, namabarang FROM databarang");
while ($row = mysqli_fetch_assoc($result)) {
    $jsBarangArray[$row['idbarang']] = ['namabarang' => $row['namabarang']];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Barang Masuk</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

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
                <div class="bg-white p-3 rounded mb-2 text-center shadow-sm">
                    <h4 class="mt-3 mb-3 fw-bold">Barang</h4>
                </div>

                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mt-1">
                    <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/router.php?page=dashboard"
                                class="text-decoration-none">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Barang Masuk</li>
                    </ol>
                </nav>

                <div class="bg-white p-3 rounded mb-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Barang Masuk</h5>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Data
                        </a>
                    </div>

                    <div class="table-responsive bg-light p-3 rounded shadow-sm mt-3">
                        <table id="table-data-barangmasuk" class="table table-striped table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Id Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah Masuk</th>
                                    <th class="text-center">Waktu</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;

                                $query = "SELECT bm.*, db.namabarang, db.harga FROM barang_masuk bm
                                JOIN databarang db ON bm.idbarang = db.idbarang
                                ORDER BY bm.waktu DESC";

                                $result = mysqli_query($koneksi, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $waktu_formatted = date('Y-m-d\TH:i', strtotime($row['waktu']));
                                    echo "<tr class='table-primary text-dark'>
                                <td>" . $no++ . "</td>
                                <td>" . htmlspecialchars($row['idbarang']) . "</td>
                                <td>" . htmlspecialchars($row['namabarang']) . "</td>
                                <td>" . (int) $row['jumlah_masuk'] . "</td>
                                <td class='text-center'>" . htmlspecialchars($row['waktu']) . "</td>
                                <td class='text-center'>
                                    <div class='d-flex justify-content-center gap-1'>
                                        <button class='btn btn-warning btn-sm me-1' data-bs-toggle='modal' data-bs-target='#editModal" . $row['id_barangmasuk'] . "'>
                                            <i class='bi bi-pencil'></i>
                                        </button>
                                        <button class='btn btn-danger btn-sm btn-confirm-delete-masuk'
                                            data-id='" . htmlspecialchars($row['id_barangmasuk']) . "'
                                            data-nama='" . htmlspecialchars($row['namabarang']) . "'
                                            data-bs-toggle='modal' data-bs-target='#modalConfirmDeleteMasuk'>
                                            <i class='bi bi-trash'></i>
                                        </button>
                                    </div>
                                </td>
                                </tr>";

                                    // Modal Edit
                                    echo "<div class='modal fade' id='editModal" . $row['id_barangmasuk'] . "'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <form class='form-edit-barangmasuk' action='" . BASE_URL . "/router.php?page=edit_barangmasuk' method='POST'>
                                            <div class='modal-header'>
                                                <h5 class='modal-title' id='editModalLabel" . $row['idbarang'] . "'>Edit Data Barang Masuk</h5>
                                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                            </div>
                                            <div class='modal-body'>
                                                <input type='hidden' name='id_barangmasuk' value='" . htmlspecialchars($row['id_barangmasuk']) . "' />
                                                <div class='mb-3'>
                                                    <label for='nama_barang" . $row['idbarang'] . "' class='form-label'>Nama Barang</label>
                                                    <input type='text' class='form-control' name='nama_barang' id='nama_barang" . $row['idbarang'] . "' value='" . htmlspecialchars($row['namabarang']) . "' readonly required />
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='jumlah_masuk" . $row['idbarang'] . "' class='form-label'>Jumlah Barang Masuk</label>
                                                    <input type='number' class='form-control' name='jumlah_masuk' id='jumlah_masuk" . $row['idbarang'] . "' value='" . htmlspecialchars($row['jumlah_masuk']) . "' required />
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='waktu" . $row['idbarang'] . "' class='form-label'>Waktu</label>
                                                    <input type='datetime-local' class='form-control' name='waktu' id='waktu" . $row['idbarang'] . "' value='" . htmlspecialchars($waktu_formatted) . "' required />
                                                </div> 
                                            </div>
                                            <div class='modal-footer'>
                                                <button type='reset' class='btn btn-danger'>Reset</button>
                                                <button type='submit' class='btn btn-primary'>Perbarui</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Tambah -->
                <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <form action="<?= BASE_URL ?>/router.php?page=simpan_barang_masuk" method="POST"
                                id="formTambahBarangMasuk">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTambahLabel">Tambah Data Barang Masuk</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="tabel-barangmasuk">
                                            <thead>
                                                <tr>
                                                    <th>ID Barang</th>
                                                    <th>Nama Barang</th>
                                                    <th>Jumlah Masuk</th>
                                                    <th>Waktu</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody-barangmasuk">
                                                <!-- Baris input ditambahkan lewat JS -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-success" id="tambahBarisMasukBtn">+ Tambah
                                        Baris</button>
                                </div>
                                <div class="modal-footer">
                                    <button type="reset" class="btn btn-danger">Reset</button>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus Barang Masuk -->
    <div class="modal fade" id="modalConfirmDeleteMasuk" tabindex="-1" aria-labelledby="modalConfirmDeleteMasukLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalConfirmDeleteMasukLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmDeleteMasukText">Apakah Anda yakin ingin menghapus data barang masuk ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="btnDeleteConfirmMasuk" class="btn btn-danger">Ya, Hapus</a>
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
        'hapus_sukses' => ['class' => 'text-bg-danger', 'message' => 'Barang berhasil dihapus!'],
        'hapus_gagal' => ['class' => 'text-bg-secondary', 'message' => 'Barang gagal dihapus karena masih memiliki transaksi.']
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
            $('#table-data-barangmasuk').DataTable({
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
        document.querySelectorAll(".form-edit-barangmasuk").forEach(form => {
            const btnReset = form.querySelector("button[type='reset']");
            if (!btnReset) return;

            // Simpan nilai asli dari input yang penting saat modal dibuka
            const originalValues = {
                nama_barang: form.querySelector("input[name='nama_barang']").value,
                jumlah_masuk: form.querySelector("input[name='jumlah_masuk']").value,
                waktu: form.querySelector("input[name='waktu']").value
            };

            btnReset.addEventListener('click', (e) => {
                // Reset native form fields dulu
                form.reset();

                // Setelah reset, kembalikan nilai input readonly dan nilai penting lainnya
                form.querySelector("input[name='nama_barang']").value = originalValues.nama_barang;
                form.querySelector("input[name='jumlah_masuk']").value = originalValues.jumlah_masuk;
                form.querySelector("input[name='waktu']").value = originalValues.waktu;
            });
        });
    </script>

    <!-- Alert Toast -->
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
        document.querySelectorAll('.btn-confirm-delete-masuk').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const nama = button.dataset.nama || 'data barang masuk ini';
                const url = '<?= BASE_URL ?>/router.php?page=hapus_barangmasuk&id_barangmasuk=' + encodeURIComponent(id);

                document.getElementById('confirmDeleteMasukText').innerText = `Apakah Anda yakin ingin menghapus barang "${nama}"?`;
                document.getElementById('btnDeleteConfirmMasuk').href = url;
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

    <script>
        function getCurrentDateTimeLocal() {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset()); // sesuaikan timezone
            return now.toISOString().slice(0, 16);
        }
    </script>

    <script>
        const barangMap = <?= json_encode($jsBarangArray); ?>;
        let counterMasuk = 0;

        const barangOptions = `<?php
        foreach ($jsBarangArray as $id => $data) {
            echo '<option value="' . htmlspecialchars($id) . '">' .
                htmlspecialchars($id . ' - ' . $data['namabarang']) .
                '</option>';
        }
        ?>`;

        function getCurrentDateTimeLocal() {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            return now.toISOString().slice(0, 16);
        }

        function tambahBarisBarangMasuk() {
            const rowId = `row-masuk-${counterMasuk}`;
            const nowStr = getCurrentDateTimeLocal();

            const row = `
<tr id="${rowId}">
    <td>
        <select name="idbarang[]" class="form-control idbarang-select" required data-counter="${counterMasuk}">
            <option value="">Pilih</option>
            ${barangOptions}
        </select>
    </td>
    <td><input type="text" name="nama_barang[]" class="form-control nama-barang" readonly required></td>
    <td><input type="number" name="jumlah_masuk[]" class="form-control" min="1" required></td>
    <td><input type="datetime-local" name="waktu[]" class="form-control" required value="${nowStr}"></td>
    <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBarisBarangMasuk('${rowId}')">Hapus</button></td>
</tr>
`;
            $('#tbody-barangmasuk').append(row);
            counterMasuk++;
        }

        function hapusBarisBarangMasuk(id) {
            document.getElementById(id).remove();
        }

        // Tambah baris pertama saat modal dibuka
        $('#modalTambah').on('show.bs.modal', function () {
            $('#tbody-barangmasuk').empty();
            counterMasuk = 0;
            tambahBarisBarangMasuk();
        });

        // Reset form
        $('#modalTambah').on('hidden.bs.modal', function () {
            $('#tbody-barangmasuk').empty();
            counterMasuk = 0;
        });

        $('#tambahBarisMasukBtn').on('click', tambahBarisBarangMasuk);

        // Isi otomatis nama_barang saat idbarang dipilih
        $(document).on('change', '.idbarang-select', function () {
            const selectedId = $(this).val();
            const namaInput = $(this).closest('tr').find('.nama-barang');
            if (selectedId && barangMap[selectedId]) {
                namaInput.val(barangMap[selectedId].namabarang);
            } else {
                namaInput.val('');
            }
        });
    </script>

</body>

</html>