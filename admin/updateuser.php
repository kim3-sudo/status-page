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
require('_guard.php');
include('../templates/_header.php');
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$updateuserid = $_POST['updateuserid'];
$updateuserfirstname = $_POST['updateuserfirstname'];
$updateuserlastname = $_POST['updateuserlastname'];
$updateuseremail = $_POST['updateuseremail'];

writeToLog($link, 'Updating user first name for ID ' . $updateuserid, $_SESSION['id']);
$stmt = $link->prepare('UPDATE users SET user_first_name = ? WHERE user_id = ?');
$stmt->bind_param('si', $updateuserfirstname, $updateuserid);
if ($stmt->execute()) {
  echo '<p>Updated first name</p>';
  writeToLog($link, 'Updated ' . $updateuserid . ' first name to ' . $updateuserfirstname, $_SESSION['id']);
} else {
  echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
  writeToLog($link, 'Failed to update ' . $updateuserid . ' first name to ' . $updateuserfirstname, $_SESSION['id']);
}
$stmt->close();
?>
<?php
writeToLog($link, 'Updating user last name for ID ' . $updateuserid, $_SESSION['id']);
$stmt = $link->prepare('UPDATE users SET user_last_name = ? WHERE user_id = ?');
$stmt->bind_param('si', $updateuserlastname, $updateuserid);
if ($stmt->execute()) {
  echo '<p>Updated last name</p>';
  writeToLog($link, 'Updated ' . $updateuserid . ' last name to ' . $updateuserlastname, $_SESSION['id']);
} else {
  echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
  writeToLog($link, 'Failed to update ' . $updateuserid . ' last name to ' . $updateuserlastname, $_SESSION['id']);
}
$stmt->close();
?>
<?php
writeToLog($link, 'Updating user email name for ID ' . $updateuserid, $_SESSION['id']);
$stmt = $link->prepare('UPDATE users SET user_email = ? WHERE user_id = ?');
$stmt->bind_param('si', $updateuseremail, $updateuserid);
if ($stmt->execute()) {
  echo '<p>Updated email address</p>';
  writeToLog($link, 'Updated ' . $updateuserid . ' email to ' . $updateuseremail, $_SESSION['id']);
} else {
  echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
  writeToLog($link, 'Failed to update ' . $updateuserid . ' email to ' . $updateuseremail, $_SESSION['id']);
}
$stmt->close();
?>
<?php
if ($_SESSION['suflag'] == 1) {
  writeToLog($link, 'Superuser flag is set', $_SESSION['id']);
  if ($_POST['updateuserpassword'] != '') {
    writeToLog($link, 'Password update by admin is not blank, so setting new one', $_SESSION['id']);
    $newhash = password_hash($_POST['updateuserpassword'], PASSWORD_DEFAULT);
    $stmt = $link->prepare('UPDATE users SET user_password = ? WHERE user_id = ?');
    $stmt->bind_param('si', $newhash, $updateuserid);
    if ($stmt->execute()) {
      writeToLog($link, 'Password for user ' . $updateuserid . ' was updated', $_SESSION['id']);
      echo '<p>Updated password as administrator</p>';
    } else {
      echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
      writeToLog($link, 'Failed to update ' . $updateuserid . ' password', $_SESSION['id']);
    }
    $stmt->close();
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
