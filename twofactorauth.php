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
include('templates/_header.php');
writeToLog($link, 'Two-factor authentication TOTP code request', -1);
writeToLog($link, mysqli_real_escape_string($link, $_POST['email']), -1);
require_once('vendor/autoload.php');
use OTPHP\TOTP;
?>
<div class="container">
  <div class="row">
    <div class="col">
<?php
if (!isset($_POST['email'], $_POST['id'], $_POST['firstname'], $_POST['lastname'], $_POST['suflag'], $_POST['totpcode']) ) {
  writeToLog($link, 'Missing field information', -1);
  exit('Missing field information');
}
writeToLog($link, 'Querying for TOTP token for user ID ' . mysqli_real_escape_string($link, $_POST['id']), -1);
$sql = "SELECT user_id, user_totpsecret FROM users WHERE user_totpsecret IS NOT NULL AND user_id = " . mysqli_real_escape_string($link, $_POST['id']);
$result = mysqli_query($link, $sql);
if (mysqli_num_rows($result) == 1) {
  $row = mysqli_fetch_assoc($result);
  $totpsecret = $row['user_totpsecret'];
  writeToLog($link, 'Verifying TOTP token starting with ' . substr($totpsecret, 0, 14), -1);
  if ($totpsecret) {
    writeToLog($link, 'Query was successful, checking result against POSTed data for match', -1);
    if (TOTP::createFromSecret($totpsecret)->verify($_POST['totpcode'])) {
      writeToLog($link, 'TOTP code was verified against secret', -1);
      writeToLog($link, 'Password hashes are good, two-factor passed, generating session tokens', -1);
      session_regenerate_id();
      $_SESSION['loggedin'] = true;
      $_SESSION['email'] = $_POST['email'];
      $_SESSION['id'] = $_POST['id'];
      $_SESSION['firstname'] = $_POST['firstname'];
      $_SESSION['lastname'] = $_POST['lastname'];
      $_SESSION['suflag'] = $_POST['suflag'];
      writeToLog($link, 'Redirecting user to admin', -1);
      header('Location: admin/admin.php');
    } else {
      writeToLog($link, 'Authentication failed on TOTP!', -1, 'WARN');
      echo '<p>Failed two-factor authentication check</p>';
    }
  } else {
    writeToLog($link, 'Failed to match user ledger row', -1, 'WARN');
    echo '<p>Something went wrong, check logs</p>';
  }
} else {
  writeToLog($link, mysqli_num_rows($result) . ' rows were matched when querying for TOTP tokens', -1);
}
$stmt->close();
?>
    </div>
  </div>
</div>
<?php
include('templates/_footer.php');
?>
