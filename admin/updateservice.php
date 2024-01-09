<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$sql = "UPDATE services SET service_name = '" . mysqli_real_escape_string($link, $_POST['updatename']) . "' WHERE service_id = " . mysqli_real_escape_string($link, $_POST['updateid']);
if ($link->query($sql) === TRUE) {
  echo '<p>Updated service name</p>';
} else {
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
<?php
$sql = "UPDATE services SET servicegroup_id = " . mysqli_real_escape_string($link, $_POST['updategroup']) . " WHERE service_id = " . mysqli_real_escape_string($link, $_POST['updateid']);
if ($link->query($sql) === TRUE) {
  echo '<p>Updated service group</p>';
} else {
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
<?php
$sql = "UPDATE services SET service_description = '" . mysqli_real_escape_string($link, $_POST['updatedescription']) . "' WHERE service_id = " . mysqli_real_escape_string($link, $_POST['updateid']);
if ($link->query($sql) === TRUE) {
  echo '<p>Updated service description</p>';
} else {
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
<?php
$sql = "UPDATE services SET service_link = '" . mysqli_real_escape_string($link, $_POST['updatelink']) . "' WHERE service_id = " . mysqli_real_escape_string($link, $_POST['updateid']);
if ($link->query($sql) === TRUE) {
  echo '<p>Updated service link</p>';
} else {
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
