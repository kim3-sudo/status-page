<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Inserting a new service group', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
writeToLog($link, mysqli_real_escape_string($link, $_POST['newservicegroupname']), $_SESSION['id']);
$sql = "INSERT INTO servicegroups (servicegroup_name) VALUE ('" . mysqli_real_escape_string($link, $_POST['newservicegroupname']) . "')";
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Created the new service group', $_SESSION['id']);
  echo '<p>Created new service group</p>';
} else {
  writeToLog($link, 'Failed to create the new service group', $_SESSION['id'], 'NERR');
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
