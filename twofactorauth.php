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
session_start();
include('templates/_header.php');
writeToLog($db, 'Two-factor authentication TOTP code request', -1);
writeToLog($db, $_POST['email'], -1);
require_once('vendor/autoload.php');
use OTPHP\TOTP;
?>
<div class="container">
  <div class="row">
    <div class="col">
<?php
if (!isset($_POST['email'], $_POST['id'], $_POST['totpcode']) ) {
  writeToLog($db, 'Missing field information', -1);
  exit('Missing field information');
}
writeToLog($db, 'Querying for TOTP token for user ID ' . $_POST['id'], -1);
$row = $db->one('SELECT user_id, user_first_name, user_last_name, user_issuperuser, user_totpsecret FROM users WHERE user_totpsecret IS NOT NULL AND user_id = ?', [(int)$_POST['id']]);
if ($row !== null) {
  $totpsecret = $row['user_totpsecret'];
  writeToLog($db, 'Verifying TOTP token starting with ' . substr($totpsecret, 0, 14), -1);
  if ($totpsecret) {
    writeToLog($db, 'Query was successful, checking result against POSTed data for match', -1);
    if (TOTP::createFromSecret($totpsecret)->verify($_POST['totpcode'])) {
      writeToLog($db, 'TOTP code was verified against secret', -1);
      writeToLog($db, 'Password hashes are good, two-factor passed, generating session tokens', -1);
      session_regenerate_id(true);
      $_SESSION['loggedin'] = true;
      $_SESSION['email'] = $_POST['email'];
      // The user's identity and privilege level come from the row we just
      // verified the TOTP secret against, not from the client-POSTed form
      // fields — trusting the POSTed suflag would let anyone self-promote
      // to superuser by tampering with their own login request.
      $_SESSION['id'] = $row['user_id'];
      $_SESSION['firstname'] = $row['user_first_name'];
      $_SESSION['lastname'] = $row['user_last_name'];
      $_SESSION['suflag'] = $row['user_issuperuser'];
      writeToLog($db, 'Redirecting user to admin', -1);
      header('Location: admin/admin.php');
    } else {
      writeToLog($db, 'Authentication failed on TOTP!', -1, 'WARN');
      echo '<p>Failed two-factor authentication check</p>';
    }
  } else {
    writeToLog($db, 'Failed to match user ledger row', -1, 'WARN');
    echo '<p>Something went wrong, check logs</p>';
  }
} else {
  writeToLog($db, 'No row matched when querying for TOTP tokens', -1);
}
?>
    </div>
  </div>
</div>
<?php
include('templates/_footer.php');
?>
