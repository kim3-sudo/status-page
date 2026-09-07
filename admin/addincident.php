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
