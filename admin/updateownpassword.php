<?php
/*
    Status Page
    Copyright (C) 2024 Sejin Kim

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
?>
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
writeToLog($link, 'Querying users for old password', $_SESSION['id']);
$sql = "SELECT user_password FROM users WHERE user_id = " . $_SESSION['id'];
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$oldhash = $row['user_password'];
writeToLog($link, 'Fetched old password hash', $_SESSION['id']);
if (strlen($_POST['updateownpassword']) >= 14 && password_verify($_POST['oldpassword'], $oldhash) && $_POST['updateownpassword'] == $_POST['updateownpasswordconfirm']) {
  writeToLog($link, 'All conditions satisfied for self-initiated password update', $_SESSION['id']);
  $sql = "UPDATE users SET user_password = '" . password_hash(mysqli_real_escape_string($link, $_POST['updateownpassword']), PASSWORD_DEFAULT) . "' WHERE user_id = " . $_SESSION['id'];
  if ($link->query($sql) === TRUE) {
    writeToLog($link, 'Updated own password', $_SESSION['id']);
    echo '<p>Updated own password</p>';
  } else {
    writeToLog($link, 'Error when updating own password', $_SESSION['id'], 'NERR');
    writeToLog($link, $sql, $_SESSION['id'], 'NERR');
    writeToLog($link, $link->error, $_SESSION['id'], 'NERR');
    echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
  }
} elseif ($_POST['updateuserpassword'] != $_POST['updateuserpasswordconfirm']) {
  writeToLog($link, 'Failed password confirmation on password change attempt', $_SESSION['id'], 'WARN');
  echo '<p>Passwords do not match.</p>';
} elseif (password_verify($_POST['oldpassword'], $oldhash)) {
  writeToLog($link, 'Failed old password on password change attempt', $_SESSION['id'], 'WARN');
  echo '<p>Old password is not correct.</p>';
} else {
  writeToLog($link, 'Failed password length requirement on password change attempt', $_SESSION['id'], 'WARN');
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
