<?php
session_start();
if (!isset($_SESSION['id'])) {
  http_response_code(403);
  die('Forbidden');
}
include('../templates/_header.php');
writeToLog($link, 'Logout called for user, session ending', $_SESSION['id']);
echo '<div class="container"><div class="row"><div class="col"><p>Logged out</p></div></div></div>';
include('../templates/_footer.php');
session_destroy();
header('Location: ../index.php');
?>
