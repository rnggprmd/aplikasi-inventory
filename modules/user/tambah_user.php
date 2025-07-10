<?php
include __DIR__ . '/../../koneksi.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: " . BASE_URL . "/router.php?page=login");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $jabatan = trim($_POST['jabatan']);
    $hak_akses = trim($_POST['hak_akses']);

    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($jabatan) || empty($hak_akses)) {
        header("Location: " . BASE_URL . "/router.php?page=user");
        exit;
    }

    // Validasi email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: " . BASE_URL . "/router.php?page=user");
        exit;
    }

    // Cek apakah username atau email sudah ada
    $cekStmt = $koneksi->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $cekStmt->bind_param("ss", $username, $email);
    $cekStmt->execute();
    $cekStmt->store_result();

    if ($cekStmt->num_rows > 0) {
        $cekStmt->close();
        header("Location: " . BASE_URL . "/router.php?page=user");
        exit;
    }
    $cekStmt->close();

    // Simpan password apa adanya (TIDAK DIHASH)
    $stmt = $koneksi->prepare("INSERT INTO users (username, email, password, jabatan, hak_akses) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $password, $jabatan, $hak_akses);

    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "/router.php?page=user&status=sukses");
        exit;
    } else {
        header("Location: " . BASE_URL . "/router.php?page=user");
        exit;
    }
}
?>
