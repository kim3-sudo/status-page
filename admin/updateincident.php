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
