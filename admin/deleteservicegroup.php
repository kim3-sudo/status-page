<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Deleting service group', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
writeToLog($link, 'Deleting service group ' . mysqli_real_escape_string($link, $_POST['deleteservicegroupid']), $_SESSION['id']);
$sql = "DELETE FROM servicegroups WHERE servicegroup_id = " . mysqli_real_escape_string($link, $_POST['deleteservicegroupid']);
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Deleted the service group', $_SESSION['id']);
  echo '<p>Deleted service group</p>';
} else {
  writeToLog($link, 'Failed to delete the service group', $_SESSION['id']);
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
