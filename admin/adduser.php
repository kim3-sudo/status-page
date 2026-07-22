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
