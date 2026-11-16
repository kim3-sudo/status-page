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
writeToLog($db, 'Updating setting value', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$setting_key = $_POST['setting_key'];
$setting_value = $_POST['setting_value'];
writeToLog($db, 'Updating setting key ' . $setting_key, $_SESSION['id']);
writeToLog($db, 'Updating setting value to ' . $setting_value, $_SESSION['id']);
$policyerror = settingWriteError($setting_key, $setting_value, $_SESSION);
if ($policyerror !== null) {
  writeToLog($db, 'Refused setting change: ' . $policyerror, $_SESSION['id'], 'WARN');
  echo '<p>Error: ' . e($policyerror) . '</p>';
} else {
  // Insert the key if it is missing (keys added after install, such as
  // saml_email_attribute on older installs), otherwise update it.
  try {
    $db->upsert('settings', ['setting_key' => $setting_key, 'setting_value' => $setting_value], ['setting_key']);
    SiteSettings::clear();
    writeToLog($db, 'Updated setting', $_SESSION['id']);
    echo '<p>Updated setting</p>';
  } catch (PDOException $e) {
    error_log('status-page: setting update failed: ' . $e->getMessage());
    writeToLog($db, 'Failed to update setting', $_SESSION['id'], 'WARN');
    echo '<p>Error: the setting could not be saved.</p>';
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
