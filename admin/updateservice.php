<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Updating service', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
writeToLog($link, 'Updating service ' . mysqli_real_escape_string($link, $_POST['updateid']), $_SESSION['id']);
writeToLog($link, 'Updating service name to ' . mysqli_real_escape_string($link, $_POST['updatename']), $_SESSION['id']);
$sql = "UPDATE services SET service_name = '" . mysqli_real_escape_string($link, $_POST['updatename']) . "' WHERE service_id = " . mysqli_real_escape_string($link, $_POST['updateid']);
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Updated service name', $_SESSION['id']);
  echo '<p>Updated service name</p>';
} else {
  writeToLog($link, 'Failed to update service name', $_SESSION['id'], 'WARN');
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
<?php
writeToLog($link, 'Updating service group to ' . mysqli_real_escape_string($link, $_POST['updategroup']), $_SESSION['id']);
$sql = "UPDATE services SET servicegroup_id = " . mysqli_real_escape_string($link, $_POST['updategroup']) . " WHERE service_id = " . mysqli_real_escape_string($link, $_POST['updateid']);
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Updated service group', $_SESSION['id']);
  echo '<p>Updated service group</p>';
} else {
  writeToLog($link, 'Failed to update service group', $_SESSION['id'], 'WARN');
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
<?php
writeToLog($link, 'Updating service description to ' . mysqli_real_escape_string($link, $_POST['updatedescription']), $_SESSION['id']);
$sql = "UPDATE services SET service_description = '" . mysqli_real_escape_string($link, $_POST['updatedescription']) . "' WHERE service_id = " . mysqli_real_escape_string($link, $_POST['updateid']);
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Updated service description', $_SESSION['id']);
  echo '<p>Updated service description</p>';
} else {
  writeToLog($link, 'Failed to update service description', $_SESSION['id'], 'WARN');
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
<?php
writeToLog($link, 'Updating service link to ' . mysqli_real_escape_string($link, $_POST['updatelink']), $_SESSION['id']);
$sql = "UPDATE services SET service_link = '" . mysqli_real_escape_string($link, $_POST['updatelink']) . "' WHERE service_id = " . mysqli_real_escape_string($link, $_POST['updateid']);
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Updated service link', $_SESSION['id']);
  echo '<p>Updated service link</p>';
} else {
  writeToLog($link, 'Failed to update service link', $_SESSION['id'], 'WARN');
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
