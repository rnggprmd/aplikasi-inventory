<?php
// koneksi ke database
include __DIR__ . '/../../koneksi.php';

// Mulai session jika belum
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/router.php?page=login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        isset($_POST['id'], $_POST['username'], $_POST['email'], $_POST['jabatan'], $_POST['hak_akses'])
    ) {
        $id = trim($_POST['id']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $jabatan = trim($_POST['jabatan']);
        $hak_akses = trim($_POST['hak_akses']);
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        // Validasi wajib diisi
        if (empty($id) || empty($username) || empty($email) || empty($jabatan) || empty($hak_akses)) {
            header("Location: " . BASE_URL . "/router.php?page=user");
            exit;
        }

        // Validasi email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Location: " . BASE_URL . "/router.php?page=user");
            exit;
        }

        // Validasi hak akses
        $allowed_access = ['Admin', 'Staff'];
        if (!in_array($hak_akses, $allowed_access)) {
            header("Location: " . BASE_URL . "/router.php?page=user");
            exit;
        }

        // Tentukan query berdasarkan apakah password diisi
        if (!empty($password)) {
            // Jika password diisi, update termasuk password
            $query = "UPDATE users SET username=?, email=?, password=?, jabatan=?, hak_akses=? WHERE id=?";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("sssssi", $username, $email, $password, $jabatan, $hak_akses, $id);
        } else {
            // Jika password tidak diubah
            $query = "UPDATE users SET username=?, email=?, jabatan=?, hak_akses=? WHERE id=?";
            $stmt = $koneksi->prepare($query);
            $stmt->bind_param("ssssi", $username, $email, $jabatan, $hak_akses, $id);
        }

        // Eksekusi dan tutup
        $stmt->execute();
        $stmt->close();
        $koneksi->close();

        // Redirect dengan notifikasi sukses
        header("Location: " . BASE_URL . "/router.php?page=user&status=edit_sukses");
        exit;
    } else {
        header("Location: " . BASE_URL . "/router.php?page=user");
        exit;
    }
} else {
    header("Location: " . BASE_URL . "/router.php?page=user");
    exit;
}
?>