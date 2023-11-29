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
$sql = "UPDATE servicegroups SET servicegroup_name = '" . mysqli_real_escape_string($link, $_POST['updateservicegroupname']) . "' WHERE servicegroup_id = " . mysqli_real_escape_string($link, $_POST['updateservicegroupid']);
if ($link->query($sql) === TRUE) {
  echo '<p>Updated service group</p>';
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
