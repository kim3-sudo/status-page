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
if ($_SESSION['suflag'] != 1) {
  die('Forbidden');
}
require_once('../vendor/autoload.php');
use OTPHP\TOTP;
writeToLog($db, 'TOTP settings were changed', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
if (isset($_POST['totpuserid'])) {
  $totpuserid = $_POST['totpuserid'];
  writeToLog($db, 'TOTP was disabled by admin for user ID '. $totpuserid, $_SESSION['id']);
  writeToLog($db, 'Setting the TOTP flag false in the user ledger', $_SESSION['id']);
  if ($db->tryExecute('UPDATE users SET user_totpenabled = ? WHERE user_id = ?', [0, (int)$totpuserid])) {
    writeToLog($db, 'TOTP flag was unset', $_SESSION['id']);
    echo '<p>TOTP was disabled.</p>';
  } else {
    writeToLog($db, 'Failed to unset TOTP flag', $_SESSION['id'], 'WARN');
    echo '<p>Failed to disable TOTP!</p>';
  }
  writeToLog($db, 'Removing old TOTP secrets', $_SESSION['id']);
  if ($db->tryExecute('UPDATE users SET user_totpsecret = NULL WHERE user_id = ?', [(int)$totpuserid])) {
    writeToLog($db, 'Removed old TOTP secrets', $_SESSION['id']);
    echo '<p>Old secrets have been removed.</p>';
  } else {
    writeToLog($db, 'Failed to remove old TOTP secrets', $_SESSION['id'], 'WARN');
    echo '<p>Failed to remove old secrets!</p>';
  }
}
?>
      <a href="./" class="btn btn-primary">Admin Portal</a>
      <button class="btn btn-secondary" onclick="history.back()">Go Back</button>
      </div>
    </div>
  </div>
</div>
<?php
include('../templates/_footer.php');
?>
