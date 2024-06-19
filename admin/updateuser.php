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
writeToLog($link, 'Updating user first name for ID ' . $_POST['updateuserid'], $_SESSION['id']);
$sql = "UPDATE users SET user_first_name = '" . mysqli_real_escape_string($link, $_POST['updateuserfirstname']) . "' WHERE user_id = " . mysqli_real_escape_string($link, $_POST['updateuserid']);
if ($link->query($sql) === TRUE) {
  echo '<p>Updated first name</p>';
  writeToLog($link, 'Updated ' . $_POST['updateuserid'] . ' first name to ' . $_POST['updateuserfirstname'], $_SESSION['id']);
} else {
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
  writeToLog($link, 'Failed to update ' . $_POST['updateuserid'] . ' first name to ' . $_POST['updateuserfirstname'], $_SESSION['id']);
}
?>
<?php
writeToLog($link, 'Updating user last name for ID ' . $_POST['updateuserid'], $_SESSION['id']);
$sql = "UPDATE users SET user_last_name = '" . mysqli_real_escape_string($link, $_POST['updateuserlastname']) . "' WHERE user_id = " . mysqli_real_escape_string($link, $_POST['updateuserid']);
if ($link->query($sql) === TRUE) {
  echo '<p>Updated last name</p>';
  writeToLog($link, 'Updated ' . $_POST['updateuserid'] . ' last name to ' . $_POST['updateuserlastname'], $_SESSION['id']);
} else {
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
  writeToLog($link, 'Failed to update ' . $_POST['updateuserid'] . ' last name to ' . $_POST['updateuserlastname'], $_SESSION['id']);
}
?>
<?php
writeToLog($link, 'Updating user email name for ID ' . $_POST['updateuserid'], $_SESSION['id']);
$sql = "UPDATE users SET user_email = '" . mysqli_real_escape_string($link, $_POST['updateuseremail']) . "' WHERE user_id = " . mysqli_real_escape_string($link, $_POST['updateuserid']);
if ($link->query($sql) === TRUE) {
  echo '<p>Updated email address</p>';
  writeToLog($link, 'Updated ' . $_POST['updateuserid'] . ' email to ' . $_POST['updateuseremail'], $_SESSION['id']);
} else {
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
  writeToLog($link, 'Failed to update ' . $_POST['updateuserid'] . ' email to ' . $_POST['updateuseremail'], $_SESSION['id']);
}
?>
<?php
if ($_SESSION['suflag'] == 1) {
  writeToLog($link, 'Superuser flag is set', $_SESSION['id']);
  if ($_POST['updateuserpassword'] != '') {
    writeToLog($link, 'Password update by admin is not blank, so setting new one', $_SESSION['id']);
    $sql = "UPDATE users SET user_password = '" . password_hash(mysqli_real_escape_string($link, $_POST['updateuserpassword']), PASSWORD_DEFAULT) . "' WHERE user_id = " . mysqli_real_escape_string($link, $_POST['updateuserid']);
    if ($link->query($sql) === TRUE) {
      writeToLog($link, 'Password for user ' . $_POST['updateuserid'] . ' was updated', $_SESSION['id']);
      echo '<p>Updated password as administrator</p>';
    } else {
      echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
      writeToLog($link, 'Failed to update ' . $_POST['updateuserid'] . ' password', $_SESSION['id']);
    }
  }
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
