<?php
session_start();
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  die('Forbidden');
}
session_destroy();
header('Location: ../index.php');
?>
