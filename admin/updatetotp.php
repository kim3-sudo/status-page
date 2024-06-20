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
if ($_POST['totpenabled'] == 1) {
  $otp = TOTP::generate();
  writeToLog($link, 'TOTP was enabled', $_SESSION['id']);
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
  $sql = "UPDATE users SET user_totpsecret = '" . $otp->getSecret() . "' WHERE user_id = " . $_SESSION['id'];
  if ($link->query($sql)) {
    writeToLog($link, 'TOTP client secret was written', $_SESSION['id']);
    echo '<p>The OTP secret is <code>' . $otp->getSecret() . '</code>.</p>';
    echo '<p>Use this OTP secret to set up your authenticator app now. You will not be able to see this secret again!</p>';
    echo '<p>If you need to reset this secret, disable TOTP, then reenable it to generate a new secret.</p>';
    echo '<p>Administrative users can also disable TOTP for you, but you must re-enroll yourself..</p>';
  } else {
    writeToLog($link, 'Failed to write the TOTP client secret', $_SESSION['id'], 'WARN');
    echo '<p>Failed to save your TOTP client secret!</p>';
  }
} else {
  writeToLog($link, 'TOTP was disabled', $_SESSION['id']);
  writeToLog($link, 'Setting the TOTP flag false in the user ledger', $_SESSION['id']);
  $sql = "UPDATE users SET user_totpenabled = 0 WHERE user_id = " . $_SESSION['id'];
  if ($link->query($sql)) {
    writeToLog($link, 'TOTP flag was unset', $_SESSION['id']);
    echo '<p>TOTP was disabled.</p>';
  } else {
    writeToLog($link, 'Failed to unset TOTP flag', $_SESSION['id'], 'WARN');
    echo '<p>Failed to disable TOTP!</p>';
  }
  writeToLog($link, 'Removing old TOTP secrets', $_SESSION['id']);
  $sql = "UPDATE users SET user_totpsecret = NULL WHERE user_id = " . $_SESSION['id'];
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
