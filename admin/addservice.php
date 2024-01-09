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
$sql = "INSERT INTO services (service_name, servicegroup_id, service_description, service_status_short, service_link) VALUE ('" . mysqli_real_escape_string($link, $_POST['newservicename']) . "', " . mysqli_real_escape_string($link, $_POST['newserviceingroup']) . ", '" . mysqli_real_escape_string($link, $_POST['newservicedescription']) . "', 'OPE', '" . mysqli_real_escape_string($link, $_POST['newservicelink']) . "')";
if ($link->query($sql) === TRUE) {
  echo '<p>Created new service</p>';
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
