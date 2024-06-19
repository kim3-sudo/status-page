<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Adding a new incident', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$addincidentdescription = mysqli_real_escape_string($link, $_POST['addincidentdescription']);
writeToLog($link, $addincidentdescription, $_SESSION['id']);
$addincidentupdatedescription = mysqli_real_escape_string($link, $_POST['addincidentupdatedescription']);
writeToLog($link, $addincidentupdatedescription, $_SESSION['id']);
$addincidentstatus = mysqli_real_escape_string($link, $_POST['addincidentstatus']);
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
if ($_POST['starttimestamp'] != '') {
  writeToLog($link, 'Start timestamp is set to ' . mysqli_real_escape_string($link, $_POST['starttimestamp']), $_SESSION['id']);
  $starttimestamp = mysqli_real_escape_string($link, $_POST['starttimestamp']);
}
$outageseverity = mysqli_real_escape_string($link, $_POST['outageseverity']);
writeToLog($link, 'Outage severity is ' . mysqli_real_escape_string($link, $_POST['outageseverity']), $_SESSION['id']);
$sql = "INSERT INTO incident (incident_description, incident_status_short, incident_describes_ids) VALUES ('" . $addincidentdescription . "', '" . $addincidentstatus . "', '" . $affectedservicesstr . "')";
writeToLog($link, 'Executing an insert to the incident table now', $_SESSION['id']);
if ($link->query($sql) === TRUE) {
  $incidentid = $link->insert_id;
  if ($_POST['starttimestamp'] != '') {
    writeToLog($link, 'Start timestamp is set, so adding that timestamp to the query', $_SESSION['id']);
    $subsql = "INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id, incident_update_timestamp) VALUES ('" . $addincidentstatus . "', '" . $addincidentupdatedescription . "', '" . $incidentid . "', '" . $starttimestamp . "')";
  } else {
    writeToLog($link, 'Start timestamp is not set', $_SESSION['id']);
    $subsql = "INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id) VALUES ('" . $addincidentstatus . "', '" . $addincidentupdatedescription . "', '" . $incidentid . "')";
  }
  if ($link->query($subsql) === TRUE) {
    foreach ($affectedservicesarray as &$serviceid) {
      writeToLog($link, 'Updating ' . $serviceid . ' to ' . $outageseverity, $_SESSION['id']);
      $subsubsql = "UPDATE services SET service_status_short = '" . $outageseverity . "' WHERE service_id = " . $serviceid;
      echo 'Updating service ID ' . $serviceid;
      if ($link->query($subsubsql) === TRUE) {
?>
<p>Updated service <?=$serviceid?>.</p>
<?php
      } else {
        writeToLog($link, 'Error updating service outage severity level', $_SESSION['id'], 'FERR');
        writeToLog($link, $subsubsql, $_SESSION['id'], 'FERR');
?>
<p>
  Error updating service outage severity level: <?=$subsubsql?>
  <br>
  <?=$link->error?>
</p>
<?php
      }
    }
  } else {
    writeToLog($link, 'Error updating incident update message', $_SESSION['id'], 'FERR');
    writeToLog($link, $subsql, $_SESSION['id'], 'FERR');
?>
<p>
  Error adding incident update message: <?=$subsql?>
  <br>
  <?=$link->error?>
</p>
<?php
  }
?>
<?php
} else {
  writeToLog($link, 'Error while creating the incident', $_SESSION['id'], 'FERR');
  writeToLog($link, $sql, $_SESSION['id'], 'FERR');
?>
<p>
  Error while creating the incident: <?=$sql?>
  <br>
  <?=$link->error?>
</p>
<?php
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
