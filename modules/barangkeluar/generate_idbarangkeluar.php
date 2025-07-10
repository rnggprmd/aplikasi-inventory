<?php
// koneksi ke database
include __DIR__ . '/../../koneksi.php';

function generateIdBarang($koneksi) {
    $query = "SELECT id FROM barangkeluar ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    $lastId = mysqli_fetch_assoc($result);

    if ($lastId) {
        $angka = (int) substr($lastId['id'], 3);
        $angka++;
        return 'TSK' . str_pad($angka, 3, '0', STR_PAD_LEFT);
    } else {
        return 'TSK001';
    }
}

echo generateIdBarang($koneksi);
