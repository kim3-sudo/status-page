<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Updating setting value', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
writeToLog($link, 'Updating setting key ' . mysqli_real_escape_string($link, $_POST['setting_key']), $_SESSION['id']);
writeToLog($link, 'Updating setting value to ' . mysqli_real_escape_string($link, $_POST['setting_value']), $_SESSION['id']);
$sql = "UPDATE settings SET setting_value = '" . mysqli_real_escape_string($link, $_POST['setting_value']) . "' WHERE setting_key = '" . mysqli_real_escape_string($link, $_POST['setting_key']) . "'";
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Updated setting', $_SESSION['id']);
  echo '<p>Updated setting</p>';
} else {
  writeToLog($link, 'Failed to update setting', $_SESSION['id'], 'WARN');
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
