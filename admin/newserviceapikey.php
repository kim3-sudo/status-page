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
writeToLog($db, 'New service API keys are being generated', $_SESSION['id']);
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
  $newapikeyname = $_POST['newapikeyname'];
  if ($db->tryExecute('INSERT INTO apikeys (apikeys_user_id, apikeys_key_hash, apikeys_is_personal, apikeys_nickname) VALUES (?, ?, ?, ?)', [(int)$_SESSION['id'], apiKeyHash($randomString), 0, $newapikeyname])) {
    writeToLog($db, 'New service API key was generated, new key starts with', $_SESSION['id']);
    writeToLog($db, apiKeyFingerprint($randomString), $_SESSION['id']);
    echo '<p>Your new service API key is ready. Your new API key is <code>' . htmlspecialchars($randomString) . '</code>. Save this key now, as you will not be able to see it again later.</p>';
  } else {
    writeToLog($db, 'Failed to generate and save new API key', $_SESSION['id'], 'WARN');
    echo '<p>Failed to generate and save new API key!</p>';
  }
} else {
  writeToLog($db, 'No API key nickname', $_SESSION['id']);
  echo '<p>No API key nickname.</p>';
}
?>
        <a href="./" class="btn btn-primary">Admin Portal</a>
        <button class="btn btn-secondary" data-history-back>Go Back</button>
      </div>
    </div>
  </div>
</div>
<?php
include('../templates/_footer.php');
?>
