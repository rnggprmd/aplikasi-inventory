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

// Ambil data barang untuk JS
$jsBarangArray = [];
$result = mysqli_query($koneksi, "SELECT idbarang, namabarang, harga FROM databarang");
while ($row = mysqli_fetch_assoc($result)) {
    $jsBarangArray[$row['idbarang']] = [
        'namabarang' => $row['namabarang'],
        'harga' => (int) $row['harga']
    ];
}

// Buat opsi customer dan barang sebagai string HTML
$customerOptionsHtml = '';
$custResult = mysqli_query($koneksi, "SELECT * FROM customer ORDER BY id_customer ASC");
while ($cust = mysqli_fetch_assoc($custResult)) {
    $customerOptionsHtml .= '<option value="' . htmlspecialchars($cust['id_customer']) . '">' .
        htmlspecialchars($cust['id_customer'] . ' - ' . $cust['nama_customer']) .
        '</option>';
}

$barangOptionsHtml = '';
foreach ($jsBarangArray as $id => $data) {
    $barangOptionsHtml .= '<option value="' . htmlspecialchars($id) . '">' .
        htmlspecialchars($id . ' - ' . $data['namabarang']) .
        '</option>';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Barang Keluar</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Barang Keluar</li>
                    </ol>
                </nav>

                <div class="bg-white p-3 rounded mb-4 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Barang Keluar</h5>
                        <a href="barangkeluar.php" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalTambah">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Data
                        </a>
                    </div>

                    <div class="table-responsive bg-light p-3 rounded shadow-sm mt-3">
                        <table id="table-data-barangkeluar" class="table table-striped table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Id Barang</th>
                                    <th>Nama Barang</th>
                                    <th>Jumlah Keluar</th>
                                    <th>Harga</th>
                                    <th class="text-center">Waktu</th>
                                    <th>Id Customer</th>
                                    <th>Nama Customer</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;

                                $query = "SELECT bk.*, db.namabarang, db.harga, c.nama_customer FROM barang_keluar bk
                                JOIN databarang db ON bk.idbarang = db.idbarang
                                JOIN customer c ON bk.id_customer = c.id_customer
                                ORDER BY bk.waktu DESC";

                                $result = mysqli_query($koneksi, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $waktu_formatted = date('Y-m-d\TH:i', strtotime($row['waktu']));
                                    echo "<tr class='table-primary text-dark'>
                                <td>" . $no++ . "</td>
                                <td>" . htmlspecialchars($row['idbarang']) . "</td>
                                <td>" . htmlspecialchars($row['namabarang']) . "</td>
                                <td>" . (int) $row['jumlah_keluar'] . "</td>
                                <td>Rp. " . number_format($row['harga'], 0, ',', '.') . "</td>
                                <td class='text-center'>" . htmlspecialchars($row['waktu']) . "</td>
                                <td>" . htmlspecialchars($row['id_customer']) . "</td>
                                <td>" . htmlspecialchars($row['nama_customer']) . "</td>
                                <td class='text-center'>
                                    <div class='d-flex justify-content-center gap-1'>
                                        <button class='btn btn-warning btn-sm me-1' data-bs-toggle='modal' data-bs-target='#editModal" . $row['id_barangkeluar'] . "'>
                                            <i class='bi bi-pencil'></i>
                                        </button>
                                        <button class='btn btn-danger btn-sm btn-confirm-delete-keluar'
                                            data-id='" . htmlspecialchars($row['id_barangkeluar']) . "'
                                            data-nama='" . htmlspecialchars($row['namabarang']) . "'
                                            data-bs-toggle='modal' data-bs-target='#modalConfirmDeleteKeluar'>
                                            <i class='bi bi-trash'></i>
                                        </button>
                                    </div>
                                </td>
                                </tr>";

                                    echo "<div class='modal fade' id='editModal" . $row['id_barangkeluar'] . "' tabindex='-1' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <form class='form-edit-barangkeluar' action='" . BASE_URL . "/router.php?page=edit_barangkeluar' method='POST'>
                                            <div class='modal-header'>
                                                <h5 class='modal-title'>Edit Data Barang Keluar</h5>
                                                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                            </div>
                                            <div class='modal-body'>
                                                <input type='hidden' name='id_barangkeluar' value='" . htmlspecialchars($row['id_barangkeluar']) . "' />
                                                <div class='mb-3'>
                                                    <label class='form-label'>Nama Barang</label>
                                                    <input type='text' class='form-control' name='nama_barang' value='" . htmlspecialchars($row['namabarang']) . "' readonly required />
                                                </div>
                                                <div class='mb-3'>
                                                    <label class='form-label'>Jumlah Barang Keluar</label>
                                                    <input type='number' class='form-control' name='jumlah_keluar' value='" . (int) $row['jumlah_keluar'] . "' required min='1' />
                                                </div>
                                                <div class='mb-3'>
                                                    <label class='form-label'>Harga</label>
                                                    <input type='text' class='form-control' value='Rp. " . number_format($row['harga'], 0, ',', '.') . "' readonly />
                                                </div>
                                                <div class='mb-3'>
                                                    <label for='waktu" . $row['idbarang'] . "' class='form-label'>Waktu</label>
                                                    <input type='datetime-local' class='form-control' name='waktu' id='waktu" . $row['idbarang'] . "' value='" . htmlspecialchars($waktu_formatted) . "' required />
                                                </div> 
                                                <div class='mb-3'>
                                                    <label class='form-label'>Customer</label>
                                                    <select name='id_customer' class='form-control' required>";
                                    $custResult = mysqli_query($koneksi, "SELECT * FROM customer ORDER BY id_customer ASC");
                                    while ($cust = mysqli_fetch_assoc($custResult)) {
                                        $selected = ($row['id_customer'] == $cust['id_customer']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($cust['id_customer']) . "' $selected>"
                                            . htmlspecialchars($cust['id_customer'] . ' - ' . $cust['nama_customer']) . "</option>";
                                    }
                                    echo "</select>
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
                            <form action="<?= BASE_URL ?>/router.php?page=simpan_barang_keluar" method="POST"
                                id="formTambahBarangKeluar">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTambahLabel">Tambah Data Barang Keluar</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="tabel-barangkeluar">
                                            <thead>
                                                <tr>
                                                    <th>ID Barang</th>
                                                    <th>Nama Barang</th>
                                                    <th>Jumlah Keluar</th>
                                                    <th>Harga</th>
                                                    <th>Waktu</th>
                                                    <th>Customer</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody-barangkeluar">
                                                <!-- Baris input akan ditambahkan lewat JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" class="btn btn-success" id="tambahBarisKeluarBtn">+ Tambah
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
    <div class="modal fade" id="modalConfirmDeleteKeluar" tabindex="-1" aria-labelledby="modalConfirmDeletekeluarLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalConfirmDeleteKeluarLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmDeleteKeluarText">Apakah Anda yakin ingin menghapus data barang masuk ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="btnDeleteConfirmKeluar" class="btn btn-danger">Ya, Hapus</a>
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
    <!-- Alert Toast Tambah, Edit dan Hapus -->

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
            $('#table-data-barangkeluar').DataTable({
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
        const barangData = <?= json_encode($jsBarangArray) ?>;

        const idBarangSelect = document.getElementById('idbarang');
        const namaBarangInput = document.getElementById('nama_barang');
        const hargaDisplayInput = document.getElementById('harga_display');
        const hargaHiddenInput = document.getElementById('harga');
        const formTambahBarangKeluar = document.getElementById('formTambahBarangKeluar');

        idBarangSelect.addEventListener('change', function () {
            const selectedId = this.value;
            if (selectedId && barangData[selectedId]) {
                namaBarangInput.value = barangData[selectedId].namabarang;
                hargaDisplayInput.value = 'Rp. ' + new Intl.NumberFormat('id-ID').format(barangData[selectedId].harga);
                hargaHiddenInput.value = barangData[selectedId].harga;
            } else {
                namaBarangInput.value = '';
                hargaDisplayInput.value = '';
                hargaHiddenInput.value = '';
            }
        });

    </script>

    <script>
        // Tangani semua form edit di barangkeluar.php
        document.querySelectorAll(".form-edit-barangkeluar").forEach(form => {
            const btnReset = form.querySelector("button[type='reset']");
            if (!btnReset) return;

            // Simpan nilai asli dari input yang ingin dikembalikan saat reset
            const originalValues = {
                nama_barang: form.querySelector("input[name='nama_barang']").value,
                jumlah_keluar: form.querySelector("input[name='jumlah_keluar']").value,
                harga: form.querySelector("input[name='harga']").value,
                waktu: form.querySelector("input[name='waktu']").value,
                id_customer: form.querySelector("select[name='id_customer']").value
            };

            btnReset.addEventListener('click', (e) => {
                e.preventDefault(); // supaya tidak otomatis reset dulu

                // Reset form ke nilai default (kosong/ awal)
                form.reset();

                // Kembalikan nilai readonly dan input yang reset() tidak handle
                form.querySelector("input[name='nama_barang']").value = originalValues.nama_barang;
                form.querySelector("input[name='harga']").value = originalValues.harga;

                // Kembalikan nilai lain secara manual juga supaya pasti
                form.querySelector("input[name='jumlah_keluar']").value = originalValues.jumlah_keluar;
                form.querySelector("input[name='waktu']").value = originalValues.waktu;
                form.querySelector("select[name='id_customer']").value = originalValues.id_customer;
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
        document.querySelectorAll('.btn-confirm-delete-keluar').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const nama = button.dataset.nama || 'data barang masuk ini';
                const url = '<?= BASE_URL ?>/router.php?page=hapus_barangkeluar&id_barangkeluar=' + encodeURIComponent(id);

                document.getElementById('confirmDeleteKeluarText').innerText = `Apakah Anda yakin ingin menghapus barang "${nama}"?`;
                document.getElementById('btnDeleteConfirmKeluar').href = url;
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
        const barangMap = <?= json_encode($jsBarangArray); ?>;
        const customerOptions = `<?php
        $customerResult = mysqli_query($koneksi, "SELECT * FROM customer ORDER BY id_customer ASC");
        while ($customer = mysqli_fetch_assoc($customerResult)) {
            echo '<option value="' . htmlspecialchars($customer['id_customer']) . '">' .
                htmlspecialchars($customer['id_customer'] . ' - ' . $customer['nama_customer']) .
                '</option>';
        }
        ?>`;

        const barangOptions = `<?php
        foreach ($jsBarangArray as $id => $data) {
            echo '<option value="' . htmlspecialchars($id) . '">' .
                htmlspecialchars($id . ' - ' . $data['namabarang']) .
                '</option>';
        }
        ?>`;

        let counterKeluar = 0;

        function getCurrentDateTimeLocal() {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            return now.toISOString().slice(0, 16);
        }

        function tambahBarisBarangKeluar() {
            const rowId = `row-keluar-${counterKeluar}`;
            const nowStr = getCurrentDateTimeLocal();

            const row = `
<tr id="${rowId}">
    <td>
        <select name="idbarang[]" class="form-control idbarang-select" required data-counter="${counterKeluar}">
            <option value="">Pilih</option>
            ${barangOptions}
        </select>
    </td>
    <td><input type="text" name="nama_barang[]" class="form-control nama-barang" readonly required></td>
    <td><input type="number" name="jumlah_keluar[]" class="form-control" min="1" required></td>
    <td>
        <input type="text" name="harga_display[]" class="form-control harga-display" readonly>
        <input type="hidden" name="harga[]" class="harga-hidden">
    </td>
    <td><input type="datetime-local" name="waktu[]" class="form-control" required value="${nowStr}"></td>
    <td>
        <select name="id_customer[]" class="form-control" required>
            <option value="">Pilih</option>
            ${customerOptions}
        </select>
    </td>
    <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBarisBarangKeluar('${rowId}')">Hapus</button></td>
</tr>
        `;

            $('#tbody-barangkeluar').append(row);
            counterKeluar++;
        }

        function hapusBarisBarangKeluar(id) {
            document.getElementById(id).remove();
        }

        // Tambah baris pertama saat modal dibuka
        $('#modalTambah').on('show.bs.modal', function () {
            $('#tbody-barangkeluar').empty();
            counterKeluar = 0;
            tambahBarisBarangKeluar();
        });

        // Reset saat modal ditutup
        $('#modalTambah').on('hidden.bs.modal', function () {
            $('#tbody-barangkeluar').empty();
            counterKeluar = 0;
        });

        // Tombol tambah baris
        $('#tambahBarisKeluarBtn').on('click', tambahBarisBarangKeluar);

        // Auto-fill nama barang dan harga saat idbarang dipilih
        $(document).on('change', '.idbarang-select', function () {
            const selectedId = $(this).val();
            const tr = $(this).closest('tr');
            const namaInput = tr.find('.nama-barang');
            const hargaDisplay = tr.find('.harga-display');
            const hargaHidden = tr.find('.harga-hidden');

            if (selectedId && barangMap[selectedId]) {
                namaInput.val(barangMap[selectedId].namabarang);
                const harga = barangMap[selectedId].harga;
                hargaDisplay.val('Rp. ' + new Intl.NumberFormat('id-ID').format(harga));
                hargaHidden.val(harga);
            } else {
                namaInput.val('');
                hargaDisplay.val('');
                hargaHidden.val('');
            }
        });
    </script>

</body>

</html>