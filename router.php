<?php
session_start();

define('BASE_PATH', __DIR__);
define('BASE_URL', '/abhipraya-cipta-bersama');

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$routes = [
    // auth
    'login' => BASE_PATH . '/modules/auth/login.php',
    'logout' => BASE_PATH . '/modules/auth/logout.php',
    // dashboard
    'dashboard' => BASE_PATH . '/modules/dashboard/dashboard.php',
    'dashboard_data' => BASE_PATH . '/modules/dashboard/dashboard_data.php',
    'dashboard_grafik_data' => BASE_PATH . '/modules/dashboard/dashboard_grafik_data.php',
    // customer
    'customer' => BASE_PATH . '/modules/customer/customer.php',
    'generate_idcustomer' => BASE_PATH . '/modules/customer/generate_idcustomer.php',
    'tambah_customer' => BASE_PATH . '/modules/customer/tambah_customer.php',
    'edit_customer' => BASE_PATH . '/modules/customer/edit_customer.php',
    'hapus_customer' => BASE_PATH . '/modules/customer/hapus_customer.php',
    // databarang
    'databarang' => BASE_PATH . '/modules/databarang/databarang.php',
    'generate_idbarang' => BASE_PATH . '/modules/databarang/generate_idbarang.php',
    'tambah_barang' => BASE_PATH . '/modules/databarang/tambah_barang.php',
    'edit_barang' => BASE_PATH . '/modules/databarang/edit_barang.php',
    'hapus_barang' => BASE_PATH . '/modules/databarang/hapus_barang.php',
    // barangmasuk
    'barangmasuk' => BASE_PATH . '/modules/barangmasuk/barangmasuk.php',
    'generate_idbarangmasuk' => BASE_PATH . '/modules/barangmasuk/generate_idbarangmasuk.php',
    'simpan_barang_masuk' => BASE_PATH . '/modules/barangmasuk/simpan_barang_masuk.php',
    'edit_barangmasuk' => BASE_PATH . '/modules/barangmasuk/edit_barangmasuk.php',
    'hapus_barangmasuk' => BASE_PATH . '/modules/barangmasuk/hapus_barangmasuk.php',
    // barangkeluar
    'barangkeluar' => BASE_PATH . '/modules/barangkeluar/barangkeluar.php',
    'generate_idbarangkeluar' => BASE_PATH . '/modules/barangkeluar/generate_idbarangkeluar.php',
    'simpan_barang_keluar' => BASE_PATH . '/modules/barangkeluar/simpan_barang_keluar.php',
    'edit_barangkeluar' => BASE_PATH . '/modules/barangkeluar/edit_barangkeluar.php',
    'hapus_barangkeluar' => BASE_PATH . '/modules/barangkeluar/hapus_barangkeluar.php',
    // user
    'user' => BASE_PATH . '/modules/user/user.php',
    'tambah_user' => BASE_PATH . '/modules/user/tambah_user.php',
    'edit_user' => BASE_PATH . '/modules/user/edit_user.php',
    'hapus_user' => BASE_PATH . '/modules/user/hapus_user.php',
    // laporan
    'laporan' => BASE_PATH . '/modules/laporan/laporan.php',
    // laporandatabarang
    'laporan_databarang' => BASE_PATH . '/modules/laporan/laporan_databarang/laporan_databarang.php',
    'pdf_databarang' => BASE_PATH . '/modules/laporan/laporan_databarang/pdf_databarang.php',
    'excel_databarang' => BASE_PATH . '/modules/laporan/laporan_databarang/excel_databarang.php',
    // laporanbarangmasuk
    'laporan_barangmasuk' => BASE_PATH . '/modules/laporan/laporan_barangmasuk/laporan_barangmasuk.php',
    'pdf_barangmasuk' => BASE_PATH . '/modules/laporan/laporan_barangmasuk/pdf_barangmasuk.php',
    'excel_barangmasuk' => BASE_PATH . '/modules/laporan/laporan_barangmasuk/excel_barangmasuk.php',
    // laporanbarangkeluar 
    'laporan_barangkeluar' => BASE_PATH . '/modules/laporan/laporan_barangkeluar/laporan_barangkeluar.php',
    'pdf_barangkeluar' => BASE_PATH . '/modules/laporan/laporan_barangkeluar/pdf_barangkeluar.php',
    'excel_barangkeluar' => BASE_PATH . '/modules/laporan/laporan_barangkeluar/excel_barangkeluar.php'
    
];
// Redirect ke login jika belum login dan buka page lain selain login
if ($page !== 'login' && !isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/router.php?page=login");
    exit();
}

if (array_key_exists($page, $routes)) {
    include $routes[$page];
} else {
    header("HTTP/1.0 404 Not Found");
    echo "404 - Halaman tidak ditemukan.";
    exit();
}
