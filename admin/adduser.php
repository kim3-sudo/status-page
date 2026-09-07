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
writeToLog($link, 'Adding a new user by admin', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
writeToLog($link, 'Generating new password', $_SESSION['id']);
$fp = @fopen('words.txt', 'r');
if ($fp) {
  $words = explode("\n", fread($fp, filesize('words.txt')));
}
$autogenpassword = '';
$counter = 0;
while ($counter < 4) {
  $rand_key = array_rand($words, 1);
  if (strlen($words[$rand_key]) > 5) {
    $autogenpassword .= $words[$rand_key];
    if ($counter < 3) {
      $autogenpassword .= '-';
    }
    $counter++;
  }
}
$adduserfirst = $_POST['adduserfirst'];
$adduserlast = $_POST['adduserlast'];
$adduseremail = $_POST['adduseremail'];
$hashedpassword = password_hash($autogenpassword, PASSWORD_DEFAULT);
writeToLog($link, 'Inserting the new user with email ' . $adduseremail . ' to user ledger', $_SESSION['id']);
$stmt = $link->prepare('INSERT INTO users (user_first_name, user_last_name, user_email, user_password) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $adduserfirst, $adduserlast, $adduseremail, $hashedpassword);
if ($stmt->execute()) {
  writeToLog($link, 'Created new user ' . $adduseremail, $_SESSION['id']);
  echo '<p>Created new user: ' . htmlspecialchars($adduserfirst) . '&nbsp;' . htmlspecialchars($adduserlast) . '</p>';
  echo '<p>' . htmlspecialchars($adduserfirst) . "'s temporary password is <code>" . htmlspecialchars($autogenpassword) . "</code>. Make sure you record this temporary password now, as you cannot get it later.</p>";
} else {
  writeToLog($link, 'Failed to create new user', $_SESSION['id']);
  echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
}
$stmt->close();
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
