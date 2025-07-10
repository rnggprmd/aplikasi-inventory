<?php
// koneksi ke database
include __DIR__ . '/../../koneksi.php';

function generateIdBarang($koneksi) {
    $query = "SELECT idbarang FROM databarang ORDER BY idbarang DESC LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    $lastId = mysqli_fetch_assoc($result);

    if ($lastId) {
        $angka = (int) substr($lastId['idbarang'], 3);
        $angka++;
        return 'BRG' . str_pad($angka, 3, '0', STR_PAD_LEFT);
    } else {
        return 'BRG001';
    }
}

echo generateIdBarang($koneksi);
