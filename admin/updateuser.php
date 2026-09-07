<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Permission is hereby granted, free of charge, to any person obtaining a copy of 
  this software and associated documentation files (the “Software”), to deal in 
  the Software without restriction, including without limitation the rights to 
  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies 
  of the Software, and to permit persons to whom the Software is furnished to do 
  so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all 
  copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
  SOFTWARE.
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
