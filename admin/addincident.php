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
writeToLog($link, 'Adding a new incident', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$addincidentdescription = $_POST['addincidentdescription'];
writeToLog($link, $addincidentdescription, $_SESSION['id']);
$addincidentupdatedescription = str_replace("<p>&nbsp;</p>", "", $_POST['addincidentupdatedescription']);
writeToLog($link, $addincidentupdatedescription, $_SESSION['id']);
$addincidentstatus = $_POST['addincidentstatus'];
writeToLog($link, $addincidentstatus, $_SESSION['id']);
if (isset($_POST['affectedservices'])) {
  $affectedservicesarray = $_POST['affectedservices'];
  $affectedservicesstr = implode(',', $affectedservicesarray);
  writeToLog($link, 'Affected services are:', $_SESSION['id']);
  writeToLog($link, $affectedservicesstr, $_SESSION['id']);
} else {
  writeToLog($link, 'Missing affected services', $_SESSION['id'], 'WARN');
  exit('Missing affected services');
}
if ($_POST['addincidentstatus'] == '') {
  writeToLog($link, 'Missing incident status', $_SESSION['id'], 'WARN');
  exit('Missing incident status');
}
$starttimestamp = null;
if ($_POST['starttimestamp'] != '') {
  writeToLog($link, 'Start timestamp is set to ' . $_POST['starttimestamp'], $_SESSION['id']);
  $starttimestamp = $_POST['starttimestamp'];
}
$outageseverity = $_POST['outageseverity'];
writeToLog($link, 'Outage severity is ' . $outageseverity, $_SESSION['id']);
writeToLog($link, 'Executing an insert to the incident table now', $_SESSION['id']);
$stmt = $link->prepare('INSERT INTO incident (incident_description, incident_status_short, incident_describes_ids) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $addincidentdescription, $addincidentstatus, $affectedservicesstr);
if ($stmt->execute()) {
  $incidentid = $link->insert_id;
  $stmt->close();
  if ($starttimestamp !== null) {
    writeToLog($link, 'Start timestamp is set, so adding that timestamp to the query', $_SESSION['id']);
    $substmt = $link->prepare('INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id, incident_update_timestamp) VALUES (?, ?, ?, ?)');
    $substmt->bind_param('ssis', $addincidentstatus, $addincidentupdatedescription, $incidentid, $starttimestamp);
  } else {
    writeToLog($link, 'Start timestamp is not set', $_SESSION['id']);
    $substmt = $link->prepare('INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id) VALUES (?, ?, ?)');
    $substmt->bind_param('ssi', $addincidentstatus, $addincidentupdatedescription, $incidentid);
  }
  if ($substmt->execute()) {
    foreach ($affectedservicesarray as &$serviceid) {
      writeToLog($link, 'Updating ' . $serviceid . ' to ' . $outageseverity, $_SESSION['id']);
      echo 'Updating service ID ' . htmlspecialchars($serviceid);
      $subsubstmt = $link->prepare('UPDATE services SET service_status_short = ? WHERE service_id = ?');
      $subsubstmt->bind_param('si', $outageseverity, $serviceid);
      if ($subsubstmt->execute()) {
?>
<p>Updated service <?=htmlspecialchars($serviceid)?>.</p>
<?php
      } else {
        writeToLog($link, 'Error updating service outage severity level', $_SESSION['id'], 'FERR');
        writeToLog($link, $link->error, $_SESSION['id'], 'FERR');
?>
<p>
  Error updating service outage severity level: <?=htmlspecialchars($link->error)?>
</p>
<?php
      }
      $subsubstmt->close();
    }
  } else {
    writeToLog($link, 'Error updating incident update message', $_SESSION['id'], 'FERR');
    writeToLog($link, $link->error, $_SESSION['id'], 'FERR');
?>
<p>
  Error adding incident update message: <?=htmlspecialchars($link->error)?>
</p>
<?php
  }
  $substmt->close();
?>
<?php
} else {
  writeToLog($link, 'Error while creating the incident', $_SESSION['id'], 'FERR');
  writeToLog($link, $link->error, $_SESSION['id'], 'FERR');
?>
<p>
  Error while creating the incident: <?=htmlspecialchars($link->error)?>
</p>
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
