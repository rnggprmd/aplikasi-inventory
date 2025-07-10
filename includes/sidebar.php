<?php
// Pastikan $user sudah didefinisikan sebelum pakai file ini

// Cek apakah sedang berada di salah satu submenu Barang
$isBarangPage = in_array($_GET['page'] ?? '', ['databarang', 'barangmasuk', 'barangkeluar']);

// Tambahan pengecekan isset untuk hak_akses
$hakAkses = isset($user['hak_akses']) ? $user['hak_akses'] : null;
?>


<!-- Sidebar -->
<aside id="sidebar" class="bg-white border-end" style="width: 250px; min-height: 100vh">
    <div class="d-flex flex-column align-items-center p-4">
        <img src="<?= BASE_URL ?>/img/Logo Sidebar.png" alt="Logo" style="width: 150px" />
    </div>

    <div class="nav-link d-flex align-items-center gap-3 px-4 mb-3 shadow-sm">
        <i class="bi bi-person-circle fs-2 text-dark"></i>
        <div>
            <?php if ($user): ?>
                <div class="fw-bold text-dark">
                    <?= htmlspecialchars($user['username']) ?>
                </div>
                <small class="text-muted">
                    <?= htmlspecialchars($user['email']) ?>
                </small>
            <?php else: ?>
                <div class="fw-bold text-danger">
                    Pengguna tidak ditemukan
                </div>
                <small class="text-muted">
                    Silakan login ulang
                </small>
            <?php endif; ?>
        </div>
    </div>

    <div class="sidebar-menu-scroll">
        <ul class="nav flex-column px-3">
            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/router.php?page=dashboard"
                    class="nav-link text-dark <?= ($_GET['page'] ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-house-fill me-2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item text-muted small px-3 mb-1">- Pelanggan</li>
            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/router.php?page=customer"
                    class="nav-link text-dark <?= ($_GET['page'] ?? '') === 'customer' ? 'active' : '' ?>">
                    <i class="bi bi-people-fill me-2"></i> Customer
                </a>
            </li>

            <li class="nav-item text-muted small px-3 mb-1">- Master Data</li>
            <li class="nav-item mb-2">
                <a class="nav-link text-dark d-flex justify-content-between align-items-center <?= $isBarangPage ? 'active' : '' ?>"
                    data-bs-toggle="collapse" href="#submenuBarang" role="button"
                    aria-expanded="<?= $isBarangPage ? 'true' : 'false' ?>" aria-controls="submenuBarang">
                    <span><i class="bi bi-box-seam-fill me-2"></i> Barang</span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse ps-4 <?= $isBarangPage ? 'show' : '' ?>" id="submenuBarang">
                    <ul class="nav flex-column mt-2">
                        <li class="nav-item mb-2">
                            <a href="<?= BASE_URL ?>/router.php?page=databarang"
                                class="nav-link text-dark small <?= ($_GET['page'] ?? '') === 'databarang' ? 'active' : '' ?>">
                                Data Barang
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="<?= BASE_URL ?>/router.php?page=barangmasuk"
                                class="nav-link text-dark small <?= ($_GET['page'] ?? '') === 'barangmasuk' ? 'active' : '' ?>">
                                Barang Masuk
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="<?= BASE_URL ?>/router.php?page=barangkeluar"
                                class="nav-link text-dark small <?= ($_GET['page'] ?? '') === 'barangkeluar' ? 'active' : '' ?>">
                                Barang Keluar
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <?php if ($user && $user['hak_akses'] === 'Admin'): ?>
                <li class="nav-item text-muted small px-3 mb-1">- Pengguna</li>
                <li class="nav-item mb-2">
                    <a href="<?= BASE_URL ?>/router.php?page=user"
                        class="nav-link text-dark <?= ($_GET['page'] ?? '') === 'user' ? 'active' : '' ?>">
                        <i class="bi bi-person-fill me-2"></i> User
                    </a>
                </li>
                <li class="nav-item text-muted small px-3 mb-1">- Cetak Berkas</li>
                <li class="nav-item mb-2">
                    <a href="<?= BASE_URL ?>/router.php?page=laporan"
                        class="nav-link text-dark <?= ($_GET['page'] ?? '') === 'laporan' ? 'active' : '' ?>">
                        <i class="bi bi-file-earmark-text-fill me-2"></i> Laporan
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <div class="mt-auto text-center p-3">
            <a href="#" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="bi bi-box-arrow-right me-2"></i> Keluar
            </a>
        </div>
    </div>
</aside>