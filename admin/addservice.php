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
writeToLog($link, 'Creating new service', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$newservicename = $_POST['newservicename'];
$newserviceingroup = $_POST['newserviceingroup'];
$newservicedescription = $_POST['newservicedescription'];
$newservicelink = $_POST['newservicelink'];
writeToLog($link, 'Adding new service ' . $newservicename . ' to group ' . $newserviceingroup, $_SESSION['id']);
$stmt = $link->prepare("INSERT INTO services (service_name, servicegroup_id, service_description, service_status_short, service_link) VALUES (?, ?, ?, 'OPE', ?)");
$stmt->bind_param('siss', $newservicename, $newserviceingroup, $newservicedescription, $newservicelink);
if ($stmt->execute()) {
  writeToLog($link, 'Created new service', $_SESSION['id']);
  echo '<p>Created new service</p>';
} else {
  writeToLog($link, 'Failed to create new service', $_SESSION['id']);
  echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
}
$stmt->close();
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
