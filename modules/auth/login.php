<?php
if (!defined('BASE_URL')) {
  die('Akses langsung tidak diizinkan');
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// koneksi.php tetap di-include, tapi tidak perlu session_start() atau define BASE_URL
include __DIR__ . '/../../koneksi.php';

$error_message = '';

// Kalau user sudah login (session ada), redirect sudah ditangani di router.php,
if (isset($_SESSION['username'])) {
  header("Location: " . BASE_URL . "/router.php?page=dashboard");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = mysqli_real_escape_string($koneksi, $_POST['username']);
  $password = $_POST['password'];

  $query = "SELECT * FROM users WHERE username = ?";
  $stmt = mysqli_prepare($koneksi, $query);
  mysqli_stmt_bind_param($stmt, "s", $username);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if ($row = mysqli_fetch_assoc($result)) {
    if ($password === $row['password']) {
      $_SESSION['username'] = $username;
      $_SESSION['login_success'] = true;
      header("Location: " . BASE_URL . "/router.php?page=dashboard");
      exit;
    } else {
      $error_message = "Username atau password salah!";
    }
  } else {
    $error_message = "Username atau password salah!";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style_auth.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Logo Icon Title -->
  <link rel="icon" type="image/png" href="<?= BASE_URL ?>/img/Logo Sidebar.png">
  <title>Abhipraya Cipta Bersama</title>
</head>

<body>
  <div class="wrapper">
    <div class="container">
      <!-- Card Logo -->
      <div class="logo-card">
        <img src="<?= BASE_URL ?>/img/Logo Sidebar.png" alt="Logo Perusahaan" />
      </div>

      <!-- Card Form Login -->
      <div class="login-box">
        <div class="login-header">
          <header>Login</header>
        </div>

        <?php if (!empty($error_message)): ?>
          <div class="error-message" style="color: red; text-align: center;">
            <?php echo $error_message; ?>
          </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/router.php?page=login" method="POST">
          <div class="input-box input-icon">
            <i class="fas fa-user"></i>
            <input type="text" class="input-field" name="username" placeholder="Username" autocomplete="off" required>
          </div>
          <div class="input-box input-icon">
            <i class="fas fa-lock"></i>
            <input type="password" class="input-field" name="password" id="password" placeholder="Password"
              autocomplete="off" required>
            <button type="button" class="toggle-password" onclick="togglePassword()">
              <i class="fas fa-eye" id="eye-icon"></i>
            </button>
          </div>
          <div class="input-submit">
            <button class="submit-btn" id="submit" type="submit"><strong>Login</strong></button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- Tambah Toast Logout di sini -->
  <?php if (isset($_SESSION['toast'])): ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
      <div class="toast show align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
          <div class="toast-body">
            <?= $_SESSION['toast']; ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
            aria-label="Close"></button>
        </div>
      </div>
    </div>
    <?php unset($_SESSION['toast']); ?>
  <?php endif; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function togglePassword() {
      const passwordField = document.getElementById("password");
      const eyeIcon = document.getElementById("eye-icon");

      if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
      } else {
        passwordField.type = "password";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
      }

      passwordField.focus(); // Tetap fokus agar UX-nya enak
    }
  </script>

</body>

</html>