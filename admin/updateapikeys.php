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
writeToLog($link, 'API keys are being rotated', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
if ($_POST['confirmation'] == 'ROTATE MY KEYS') {
  $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < 128; $i++) {
    $randomString .= $characters[random_int(0, $charactersLength - 1)];
  }
  $sql = "DELETE FROM apikeys WHERE apikeys_is_personal = 1 AND apikeys_user_id = " . $_SESSION['id'];
  if ($link->query($sql)) {
    writeToLog($link, 'Old API keys were removed', $_SESSION['id']);
  } else {
    writeToLog($link, 'Failed to remove old API keys', $_SESSION['id'], 'FERR');
    die('Failed to remove old API keys!');
  }
  $sql = "INSERT INTO apikeys (apikeys_user_id, apikeys_authkey, apikeys_is_personal) VALUE (" . $_SESSION['id'] . ", '" . mysqli_real_escape_string($link, $randomString) . "', 1)";
  if ($link->query($sql)) {
    writeToLog($link, 'API keys were rotated, new key starts with', $_SESSION['id']);
    writeToLog($link, substr($randomString, 0, 8), $_SESSION['id']);
    echo '<p>Your API keys were rotated. Your new API key is <code>' . $randomString . '</code>. Save this key now, as you will not be able to see it again later.</p>';
  } else {
    writeToLog($link, 'Failed to generate and save new API key', $_SESSION['id'], 'WARN');
    echo '<p>Failed to generate and save new API key!</p>';
  }
} else {
  writeToLog($link, 'Failed to confirm API key rotation phrase', $_SESSION['id']);
  echo '<p>Failed to confirm API key rotation phrase! Your old key (if set) is still active.</p>';
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
