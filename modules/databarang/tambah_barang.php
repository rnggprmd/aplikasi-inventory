<?php
// koneksi ke database
include __DIR__ . '/../../koneksi.php';

// Mulai session jika belum
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/router.php?page=login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Cek apakah input berupa array (tambah banyak data)
    if (is_array($_POST['idbarang'])) {
        $idbarangArr    = $_POST['idbarang'];
        $namabarangArr  = $_POST['namabarang'];
        $hargaArr       = $_POST['harga'];

        for ($i = 0; $i < count($idbarangArr); $i++) {
            $idbarang   = trim($idbarangArr[$i]);
            $namabarang = trim($namabarangArr[$i]);
            $harga      = (int) $hargaArr[$i];

            // Validasi
            if (empty($idbarang) || empty($namabarang) || $harga < 0) {
                $_SESSION['error'] = "Data tidak valid pada baris ke-" . ($i + 1);
                header("Location: " . BASE_URL . "/router.php?page=databarang");
                exit;
            }

            // Cek apakah ID sudah ada
            $cek = $koneksi->prepare("SELECT idbarang FROM databarang WHERE idbarang = ?");
            $cek->bind_param("s", $idbarang);
            $cek->execute();
            $cek->store_result();

            if ($cek->num_rows > 0) {
                $cek->close();
                $_SESSION['error'] = "ID Barang '$idbarang' sudah ada.";
                header("Location: " . BASE_URL . "/router.php?page=databarang");
                exit;
            }
            $cek->close();

            // Simpan data ke database, stockbarang default 0
            $stmt = $koneksi->prepare("INSERT INTO databarang (idbarang, namabarang, stockbarang, harga) VALUES (?, ?, 0, ?)");
            $stmt->bind_param("ssi", $idbarang, $namabarang, $harga);
            $stmt->execute();
            $stmt->close();
        }

        $_SESSION['success'] = "Semua data barang berhasil ditambahkan.";
        header("Location: " . BASE_URL . "/router.php?page=databarang&status=sukses");
        exit;

    } else {
        // Tangani 1 data (form lama)
        $idbarang   = trim($_POST['idbarang']);
        $namabarang = trim($_POST['namabarang']);
        $harga      = (int) $_POST['harga'];

        if (empty($idbarang) || empty($namabarang) || $harga < 0) {
            $_SESSION['error'] = "Data tidak valid. Harap cek input Anda.";
            header("Location: " . BASE_URL . "/router.php?page=databarang");
            exit;
        }

        $cek = $koneksi->prepare("SELECT idbarang FROM databarang WHERE idbarang = ?");
        $cek->bind_param("s", $idbarang);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $cek->close();
            $_SESSION['error'] = "ID Barang sudah ada. Gunakan ID lain.";
            header("Location: " . BASE_URL . "/router.php?page=databarang");
            exit;
        }

        $cek->close();
        $stmt = $koneksi->prepare("INSERT INTO databarang (idbarang, namabarang, stockbarang, harga) VALUES (?, ?, 0, ?)");
        $stmt->bind_param("ssi", $idbarang, $namabarang, $harga);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Data barang berhasil ditambahkan.";
            $stmt->close();
            header("Location: " . BASE_URL . "/router.php?page=databarang&status=sukses");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menambahkan data barang.";
            $stmt->close();
            header("Location: " . BASE_URL . "/router.php?page=databarang");
            exit;
        }
    }
} else {
    header("Location: " . BASE_URL . "/router.php?page=databarang");
    exit;
}
