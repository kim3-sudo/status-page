<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Deleting service', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
writeToLog($link, 'Deleting service ' . mysqli_real_escape_string($link, $_POST['deleteserviceid']), $_SESSION['id']);
$sql = "DELETE FROM services WHERE service_id = " . mysqli_real_escape_string($link, $_POST['deleteserviceid']);
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Deleted the service', $_SESSION['id']);
  echo '<p>Deleted service</p>';
} else {
  writeToLog($link, 'Failed to delete the service', $_SESSION['id']);
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
      <a href="./" class="btn btn-primary">Admin Portal</a>
      <button class="btn btn-secondary" onclick="history.back()">Go Back</a>
      </div>
    </div>
  </div>
</div>
<?php
include('../templates/_footer.php');
?>
