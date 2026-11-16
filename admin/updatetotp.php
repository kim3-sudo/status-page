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
require_once('../vendor/autoload.php');
use OTPHP\TOTP;
writeToLog($db, 'TOTP settings were changed', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
if ($_POST['verifytotp'] && TOTP::createFromSecret($_POST['otpsecret'])->verify($_POST['verifytotp'])) {
  writeToLog($db, 'TOTP enrollment event, TOTP verified', $_SESSION['id']);
  writeToLog($db, 'Setting the TOTP flag true in the user ledger', $_SESSION['id']);
  if ($db->tryExecute('UPDATE users SET user_totpenabled = ? WHERE user_id = ?', [1, (int)$_SESSION['id']])) {
    writeToLog($db, 'TOTP flag was set', $_SESSION['id']);
    echo '<p>TOTP was enabled.</p>';
  } else {
    writeToLog($db, 'Failed to set TOTP flag', $_SESSION['id'], 'WARN');
    echo '<p>Failed to enable TOTP!</p>';
  }
  writeToLog($db, 'Writing the TOTP client secret to the user ledger', $_SESSION['id']);
  $otpsecret = $_POST['otpsecret'];
  if ($db->tryExecute('UPDATE users SET user_totpsecret = ? WHERE user_id = ?', [$otpsecret, (int)$_SESSION['id']])) {
    writeToLog($db, 'TOTP client secret was written', $_SESSION['id']);
    echo '<p>TOTP verification was successful, you are now enrolled in two-factor authentication.</p>';
  } else {
    writeToLog($db, 'Failed to write the TOTP client secret', $_SESSION['id'], 'WARN');
    echo '<p>Failed to save your TOTP client secret!</p>';
  }
} else {
  echo '<p>TOTP was not enabled, code could not be verified.</p>';
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
