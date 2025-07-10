<?php
// role.php

function cekHakAkses(array $allowedRoles) {
    if (!isset($_SESSION['username'])) {
        header("Location: " . BASE_URL . "/router.php?page=login");
        exit;
    }

    global $koneksi;

    $username = $_SESSION['username'];

    $stmt = $koneksi->prepare("SELECT hak_akses FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        session_destroy();
        header("Location: " . BASE_URL . "/router.php?page=login");
        exit;
    }

    if (!in_array($user['hak_akses'], $allowedRoles)) {
        // Redirect langsung tanpa pesan
        header("Location: " . BASE_URL . "/router.php?page=dashboard");
        exit;
    }
}