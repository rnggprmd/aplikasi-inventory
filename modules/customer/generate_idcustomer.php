<?php
// koneksi ke database
include __DIR__ . '/../../koneksi.php';

function generateIdCustomer($koneksi) {
    $query = "SELECT id_customer FROM customer ORDER BY id_customer DESC LIMIT 1";
    $result = mysqli_query($koneksi, $query);
    $lastId = mysqli_fetch_assoc($result);

    if ($lastId) {
        $angka = (int) substr($lastId['id_customer'], 3);
        $angka++;
        return 'CST' . str_pad($angka, 3, '0', STR_PAD_LEFT);
    } else {
        return 'CST001';
    }
}

echo generateIdCustomer($koneksi);
