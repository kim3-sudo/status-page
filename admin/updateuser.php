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
$sql = "UPDATE users SET user_first_name = '" . mysqli_real_escape_string($link, $_POST['updateuserfirstname']) . "' WHERE user_id = " . mysqli_real_escape_string($link, $_POST['updateuserid']);
if ($link->query($sql) === TRUE) {
  echo '<p>Updated first name</p>';
} else {
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
<?php
$sql = "UPDATE users SET user_last_name = '" . mysqli_real_escape_string($link, $_POST['updateuserlastname']) . "' WHERE user_id = " . mysqli_real_escape_string($link, $_POST['updateuserid']);
if ($link->query($sql) === TRUE) {
  echo '<p>Updated last name</p>';
} else {
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
<?php
$sql = "UPDATE users SET user_email = '" . mysqli_real_escape_string($link, $_POST['updateuseremail']) . "' WHERE user_id = " . mysqli_real_escape_string($link, $_POST['updateuserid']);
if ($link->query($sql) === TRUE) {
  echo '<p>Updated email address</p>';
} else {
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
<?php
if ($_SESSION['suflag'] == 1) {
  if ($_POST['updateuserpassword'] != '') {
    $sql = "UPDATE users SET user_password = '" . password_hash(mysqli_real_escape_string($link, $_POST['updateuserpassword']), PASSWORD_DEFAULT) . "' WHERE user_id = " . mysqli_real_escape_string($link, $_POST['updateuserid']);
    if ($link->query($sql) === TRUE) {
      echo '<p>Updated password as administrator</p>';
    } else {
      echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
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
