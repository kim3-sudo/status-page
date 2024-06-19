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
$sql = "SELECT user_password FROM users WHERE user_id = " . $_SESSION['id'];
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$oldhash = $row['user_password'];
if (strlen($_POST['updateownpassword']) >= 14 && password_hash($_POST['oldpassword'], PASSWORD_DEFAULT) != $oldhash && $_POST['updateownpassword'] == $_POST['updateownpasswordconfirm']) {
  $sql = "UPDATE users SET user_password = '" . password_hash(mysqli_real_escape_string($link, $_POST['updateownpassword']), PASSWORD_DEFAULT) . "' WHERE user_id = " . $_SESSION['id'];
  if ($link->query($sql) === TRUE) {
    echo '<p>Updated own password</p>';
  } else {
    echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
  }
} elseif ($_POST['updateuserpassword'] != $_POST['updateuserpasswordconfirm']) {
  echo '<p>Passwords do not match.</p>';
} elseif ($_POST['oldpassword'] != $oldhash) {
  echo '<p>Old password is not correct.</p>';
} else {
  echo '<p>Password does not match minimum length requirement.</p>';
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
