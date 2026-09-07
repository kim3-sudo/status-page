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
writeToLog($link, 'Updating an incident', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$existingincident = $_POST['existingincident'];
$existingincidentupdate = str_replace("<p>&nbsp;</p>", "", $_POST['existingincidentupdate']);
$existingincidentstatus = $_POST['existingincidentstatus'];
writeToLog($link, 'Updating incident ' . $existingincident, $_SESSION['id']);
writeToLog($link, $existingincidentupdate, $_SESSION['id']);
writeToLog($link, $existingincidentstatus, $_SESSION['id']);
if ($_POST['updatetimestamp'] != '') {
  writeToLog($link, 'Incident update has a timestamp, using timestamped query', $_SESSION['id']);
  $updatetimestamp = $_POST['updatetimestamp'];
  $stmt = $link->prepare('INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id, incident_update_timestamp) VALUES (?, ?, ?, ?)');
  $stmt->bind_param('ssis', $existingincidentstatus, $existingincidentupdate, $existingincident, $updatetimestamp);
} else {
  writeToLog($link, 'Incident update has no timestamp', $_SESSION['id']);
  $stmt = $link->prepare('INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id) VALUES (?, ?, ?)');
  $stmt->bind_param('ssi', $existingincidentstatus, $existingincidentupdate, $existingincident);
}
writeToLog($link, 'Executing update query', $_SESSION['id']);
if ($stmt->execute()) {
  $stmt->close();
  $addstmt = $link->prepare('UPDATE incident SET incident_status_short = ? WHERE incident_id = ?');
  $addstmt->bind_param('si', $existingincidentstatus, $existingincident);
  if ($addstmt->execute()) {
    writeToLog($link, 'Updated incident status', $_SESSION['id']);
    echo '<p>Updated incident status</p>';
  } else {
    writeToLog($link, 'Failed to update incident status', $_SESSION['id']);
    echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
  }
  $addstmt->close();
  if ($existingincidentstatus == 'RES') {
    writeToLog($link, 'Incident was marked as resolved', $_SESSION['id']);
    // get the list of affected services
    writeToLog($link, 'Parsing incident affected services', $_SESSION['id']);
    $substmt = $link->prepare('SELECT incident_describes_ids FROM incident WHERE incident_id = ?');
    $substmt->bind_param('i', $existingincident);
    $substmt->execute();
    $row = $substmt->get_result()->fetch_assoc();
    $substmt->close();
    writeToLog($link, $row['incident_describes_ids'], $_SESSION['id']);
    $idsarray = preg_split("/\,/", $row['incident_describes_ids']);
    // mark the affected services as operational
    foreach ($idsarray as &$activeserviceid) {
      writeToLog($link, 'Marking service ' . $activeserviceid . ' as operational', $_SESSION['id']);
      $svcstmt = $link->prepare("UPDATE services SET service_status_short = 'OPE' WHERE service_id = ?");
      $svcstmt->bind_param('i', $activeserviceid);
      if ($svcstmt->execute()) {
        writeToLog($link, 'Marked service as operational', $_SESSION['id']);
        echo '<p>Set service ' . htmlspecialchars($activeserviceid) . ' to operational.</p>';
      } else {
        writeToLog($link, 'Failed to mark service as operational', $_SESSION['id']);
        echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
      }
      $svcstmt->close();
    }
  }
?>
<p>Incident update added successfully</p>
<?php
} else {
?>
<p>Error: <?=htmlspecialchars($link->error)?></p>
<?php
  $stmt->close();
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
