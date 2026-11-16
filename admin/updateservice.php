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
writeToLog($db, 'Updating service', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$updateid = $_POST['updateid'];
$updatename = $_POST['updatename'];
$updategroup = $_POST['updategroup'];
$updatedescription = $_POST['updatedescription'];
$updatelink = $_POST['updatelink'];

writeToLog($db, 'Updating service ' . $updateid, $_SESSION['id']);
writeToLog($db, 'Updating service name to ' . $updatename, $_SESSION['id']);
if ($db->tryExecute('UPDATE services SET service_name = ? WHERE service_id = ?', [$updatename, (int)$updateid])) {
  writeToLog($db, 'Updated service name', $_SESSION['id']);
  echo '<p>Updated service name</p>';
} else {
  writeToLog($db, 'Failed to update service name', $_SESSION['id'], 'WARN');
  echo '<p>Error: ' . e($db->lastError()) . '</p>';
}
?>
<?php
writeToLog($db, 'Updating service group to ' . $updategroup, $_SESSION['id']);
if ($db->tryExecute('UPDATE services SET servicegroup_id = ? WHERE service_id = ?', [(int)$updategroup, (int)$updateid])) {
  writeToLog($db, 'Updated service group', $_SESSION['id']);
  echo '<p>Updated service group</p>';
} else {
  writeToLog($db, 'Failed to update service group', $_SESSION['id'], 'WARN');
  echo '<p>Error: ' . e($db->lastError()) . '</p>';
}
?>
<?php
writeToLog($db, 'Updating service description to ' . $updatedescription, $_SESSION['id']);
if ($db->tryExecute('UPDATE services SET service_description = ? WHERE service_id = ?', [$updatedescription, (int)$updateid])) {
  writeToLog($db, 'Updated service description', $_SESSION['id']);
  echo '<p>Updated service description</p>';
} else {
  writeToLog($db, 'Failed to update service description', $_SESSION['id'], 'WARN');
  echo '<p>Error: ' . e($db->lastError()) . '</p>';
}
?>
<?php
writeToLog($db, 'Updating service link to ' . $updatelink, $_SESSION['id']);
if ($db->tryExecute('UPDATE services SET service_link = ? WHERE service_id = ?', [$updatelink, (int)$updateid])) {
  writeToLog($db, 'Updated service link', $_SESSION['id']);
  echo '<p>Updated service link</p>';
} else {
  writeToLog($db, 'Failed to update service link', $_SESSION['id'], 'WARN');
  echo '<p>Error: ' . e($db->lastError()) . '</p>';
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
