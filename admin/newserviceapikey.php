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
writeToLog($link, 'New service API keys are being generated', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
if (isset($_POST['newapikeyname'])) {
  $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < 128; $i++) {
    $randomString .= $characters[random_int(0, $charactersLength - 1)];
  }
  $sql = "INSERT INTO apikeys (apikeys_user_id, apikeys_authkey, apikeys_is_personal, apikeys_nickname) VALUE (" . $_SESSION['id'] . ", '" . mysqli_real_escape_string($link, $randomString) . "', 0, '" . mysqli_real_escape_string($link, $_POST['newapikeyname']) . "')";
  if ($link->query($sql)) {
    writeToLog($link, 'New service API key was generated, new key starts with', $_SESSION['id']);
    writeToLog($link, substr($randomString, 0, 8), $_SESSION['id']);
    echo '<p>Your new service API key is ready. Your new API key is <code>' . $randomString . '</code>. Save this key now, as you will not be able to see it again later.</p>';
  } else {
    writeToLog($link, 'Failed to generate and save new API key', $_SESSION['id'], 'WARN');
    echo '<p>Failed to generate and save new API key!</p>';
  }
} else {
  writeToLog($link, 'No API key nickname', $_SESSION['id']);
  echo '<p>No API key nickname.</p>';
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
