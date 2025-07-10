<?php
// koneksi ke database
include __DIR__ . '/../../koneksi.php';

function generateIdBarang($koneksi) {
    $query = "SELECT id_barangmasuk FROM barangmasuk ORDER BY id_barangmasuk DESC LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    $lastId = mysqli_fetch_assoc($result);

    if ($lastId) {
        $angka = (int) substr($lastId['id_barangmasuk'], 3);
        $angka++;
        return 'TSM' . str_pad($angka, 3, '0', STR_PAD_LEFT);
    } else {
        return 'TSM001';
    }
}

echo generateIdBarang($koneksi);
