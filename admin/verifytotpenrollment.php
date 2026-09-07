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
use chillerlan\QRCode\QRCode;
writeToLog($link, 'TOTP settings were changed', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
if ($_POST['totpenabled'] == 1) {
  $otp = TOTP::generate();
  writeToLog($link, 'TOTP enrollment request', $_SESSION['id']);
  $qrdata = 'otpauth://totp/Status Page:Status Page ' . $_SESSION['email'] . '?secret=' . $otp->getSecret() . '&issuer=Status Page';
?>
        <p>The OTP secret is <code><?=$otp->getSecret()?></code>.</p>
        <img style="width: 30%" src="<?=(new QRCode)->render($qrdata)?>" alt="QR code with OTP secret" />
        <p>Use this OTP secret to set up your authenticator app now. You will not be able to see this secret again!</p>
        <p>If you need to reset this secret, disable TOTP, then reenable it to generate a new secret.</p>
        <p>Administrative users can also disable TOTP for you, but you must re-enroll yourself.</p>
        <form action="updatetotp.php" method="post">
          <input type="hidden" name="otpsecret" value="<?=$otp->getSecret()?>">
          <label for="verifytotp" class="form-label">Verify TOTP to finish enrollment</label>
          <input type="number" class="form-control" id="verifytotp" name="verifytotp">
          <button type="submit" class="btn btn-primary mt-3">Verify TOTP</button>
        </form>
<?php
} else {
  writeToLog($link, 'TOTP was disabled', $_SESSION['id']);
  writeToLog($link, 'Setting the TOTP flag false in the user ledger', $_SESSION['id']);
  $stmt = $link->prepare('UPDATE users SET user_totpenabled = 0 WHERE user_id = ?');
  $stmt->bind_param('i', $_SESSION['id']);
  if ($stmt->execute()) {
    writeToLog($link, 'TOTP flag was unset', $_SESSION['id']);
    echo '<p>TOTP was disabled.</p>';
  } else {
    writeToLog($link, 'Failed to unset TOTP flag', $_SESSION['id'], 'WARN');
    echo '<p>Failed to disable TOTP!</p>';
  }
  $stmt->close();
  writeToLog($link, 'Removing old TOTP secrets', $_SESSION['id']);
  $stmt = $link->prepare('UPDATE users SET user_totpsecret = NULL WHERE user_id = ?');
  $stmt->bind_param('i', $_SESSION['id']);
  if ($stmt->execute()) {
    writeToLog($link, 'Removed old TOTP secrets', $_SESSION['id']);
    echo '<p>Old secrets have been removed.</p>';
  } else {
    writeToLog($link, 'Failed to remove old TOTP secrets', $_SESSION['id'], 'WARN');
    echo '<p>Failed to remove old secrets!</p>';
  }
  $stmt->close();
  echo '<a href="./" class="btn btn-primary">Admin Portal</a>';
  echo '<button class="btn btn-secondary" onclick="history.back()">Go Back</a>';
}
?>
      </div>
    </div>
  </div>
</div>
<?php
include('../templates/_footer.php');
?>
