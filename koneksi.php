<?php
$koneksi = mysqli_connect("localhost", "root", "", "abhipraya_cipta_bersama");

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>