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
writeToLog($link, 'API keys are being rotated', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
if ($_POST['confirmation'] == 'ROTATE MY KEYS') {
  $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < 128; $i++) {
    $randomString .= $characters[random_int(0, $charactersLength - 1)];
  }
  $stmt = $link->prepare('DELETE FROM apikeys WHERE apikeys_is_personal = 1 AND apikeys_user_id = ?');
  $stmt->bind_param('i', $_SESSION['id']);
  if ($stmt->execute()) {
    writeToLog($link, 'Old API keys were removed', $_SESSION['id']);
  } else {
    writeToLog($link, 'Failed to remove old API keys', $_SESSION['id'], 'FERR');
    die('Failed to remove old API keys!');
  }
  $stmt->close();
  $stmt = $link->prepare('INSERT INTO apikeys (apikeys_user_id, apikeys_authkey, apikeys_is_personal) VALUES (?, ?, 1)');
  $stmt->bind_param('is', $_SESSION['id'], $randomString);
  if ($stmt->execute()) {
    writeToLog($link, 'API keys were rotated, new key starts with', $_SESSION['id']);
    writeToLog($link, substr($randomString, 0, 8), $_SESSION['id']);
    echo '<p>Your API keys were rotated. Your new API key is <code>' . htmlspecialchars($randomString) . '</code>. Save this key now, as you will not be able to see it again later.</p>';
  } else {
    writeToLog($link, 'Failed to generate and save new API key', $_SESSION['id'], 'WARN');
    echo '<p>Failed to generate and save new API key!</p>';
  }
  $stmt->close();
} else {
  writeToLog($link, 'Failed to confirm API key rotation phrase', $_SESSION['id']);
  echo '<p>Failed to confirm API key rotation phrase! Your old key (if set) is still active.</p>';
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
