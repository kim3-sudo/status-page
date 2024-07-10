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
require_once('../vendor/autoload.php');
use OTPHP\TOTP;
writeToLog($link, 'TOTP settings were changed', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
if ($_POST['verifytotp'] && TOTP::createFromSecret($_POST['otpsecret'])->verify($_POST['verifytotp'])) {
  writeToLog($link, 'TOTP enrollment event, TOTP verified', $_SESSION['id']);
  writeToLog($link, 'Setting the TOTP flag true in the user ledger', $_SESSION['id']);
  $sql = "UPDATE users SET user_totpenabled = 1 WHERE user_id = " . $_SESSION['id'];
  if ($link->query($sql)) {
    writeToLog($link, 'TOTP flag was set', $_SESSION['id']);
    echo '<p>TOTP was enabled.</p>';
  } else {
    writeToLog($link, 'Failed to set TOTP flag', $_SESSION['id'], 'WARN');
    echo '<p>Failed to enable TOTP!</p>';
  }
  writeToLog($link, 'Writing the TOTP client secret to the user ledger', $_SESSION['id']);
  $sql = "UPDATE users SET user_totpsecret = '" . mysqli_real_escape_string($link, $_POST['otpsecret']) . "' WHERE user_id = " . $_SESSION['id'];
  if ($link->query($sql)) {
    writeToLog($link, 'TOTP client secret was written', $_SESSION['id']);
    echo '<p>TOTP verification was successful, you are now enrolled in two-factor authentication.</p>';
  } else {
    writeToLog($link, 'Failed to write the TOTP client secret', $_SESSION['id'], 'WARN');
    echo '<p>Failed to save your TOTP client secret!</p>';
  }
} else {
  echo '<p>TOTP was not enabled, code could not be verified.</p>';
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
