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
writeToLog($link, 'Querying users for old password', $_SESSION['id']);
$stmt = $link->prepare('SELECT user_password FROM users WHERE user_id = ?');
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$oldhash = $row['user_password'];
writeToLog($link, 'Fetched old password hash', $_SESSION['id']);
if (strlen($_POST['updateownpassword']) >= 14 && password_verify($_POST['oldpassword'], $oldhash) && $_POST['updateownpassword'] == $_POST['updateownpasswordconfirm']) {
  writeToLog($link, 'All conditions satisfied for self-initiated password update', $_SESSION['id']);
  $newhash = password_hash($_POST['updateownpassword'], PASSWORD_DEFAULT);
  $stmt = $link->prepare('UPDATE users SET user_password = ? WHERE user_id = ?');
  $stmt->bind_param('si', $newhash, $_SESSION['id']);
  if ($stmt->execute()) {
    writeToLog($link, 'Updated own password', $_SESSION['id']);
    echo '<p>Updated own password</p>';
  } else {
    writeToLog($link, 'Error when updating own password', $_SESSION['id'], 'NERR');
    writeToLog($link, $link->error, $_SESSION['id'], 'NERR');
    echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
  }
  $stmt->close();
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
