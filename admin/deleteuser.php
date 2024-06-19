<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Deleting user', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
writeToLog($link, 'Deleting user ' . mysqli_real_escape_string($link, $_POST['deleteuserid']), $_SESSION['id']);
$sql = "DELETE FROM users WHERE user_id = " . mysqli_real_escape_string($link, $_POST['deleteuserid']);
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Deleted the user', $_SESSION['id']);
  echo '<p>Deleted user ' . $_POST['deleteuserid'] . '</p>';
} else {
  writeToLog($link, 'Failed to delete the user', $_SESSION['id']);
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
