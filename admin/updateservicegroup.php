<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Updating service group', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
writeToLog($link, 'Updating service group ' . mysqli_real_escape_string($link, $_POST['updateservicegroupid']), $_SESSION['id']);
$sql = "UPDATE servicegroups SET servicegroup_name = '" . mysqli_real_escape_string($link, $_POST['updateservicegroupname']) . "' WHERE servicegroup_id = " . mysqli_real_escape_string($link, $_POST['updateservicegroupid']);
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Updated service group name to ' . mysqli_real_escape_string($link, $_POST['updateservicegroupname']), $_SESSION['id']);
  echo '<p>Updated service group</p>';
} else {
  writeToLog($link, 'Failed to update service group name', $_SESSION['id'], 'NERR');
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
