<?php
// Define base URL folder
define('BASE_URL', '/abhipraya-cipta-bersama');

// Redirect ke router.php dengan page login
header("Location: " . BASE_URL . "/router.php?page=login");
exit();
?>