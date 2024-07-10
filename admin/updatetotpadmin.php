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
if ($_SESSION['suflag'] != 1) {
  die('Forbidden');
}
require_once('../vendor/autoload.php');
use OTPHP\TOTP;
writeToLog($link, 'TOTP settings were changed', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
if (isset($_POST['totpuserid'])) {
  writeToLog($link, 'TOTP was disabled by admin for user ID '. $_POST['totpuserid'], $_SESSION['id']);
  writeToLog($link, 'Setting the TOTP flag false in the user ledger', $_SESSION['id']);
  $sql = "UPDATE users SET user_totpenabled = 0 WHERE user_id = " . $_POST['totpuserid'];
  if ($link->query($sql)) {
    writeToLog($link, 'TOTP flag was unset', $_SESSION['id']);
    echo '<p>TOTP was disabled.</p>';
  } else {
    writeToLog($link, 'Failed to unset TOTP flag', $_SESSION['id'], 'WARN');
    echo '<p>Failed to disable TOTP!</p>';
  }
  writeToLog($link, 'Removing old TOTP secrets', $_SESSION['id']);
  $sql = "UPDATE users SET user_totpsecret = NULL WHERE user_id = " . $_POST['totpuserid'];
  if ($link->query($sql)) {
    writeToLog($link, 'Removed old TOTP secrets', $_SESSION['id']);
    echo '<p>Old secrets have been removed.</p>';
  } else {
    writeToLog($link, 'Failed to remove old TOTP secrets', $_SESSION['id'], 'WARN');
    echo '<p>Failed to remove old secrets!</p>';
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
