<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$addmaintstart = mysqli_real_escape_string($link, $_POST['plannedmaintenancestart']);
$addmaintstarttimestamp = strval($addmaintstart);
$addmaintend = mysqli_real_escape_string($link, $_POST['plannedmaintenanceend']);
$addmaintendtimestamp = strval($addmaintend);
$addmaintdescription = mysqli_real_escape_string($link, $_POST['plannedmaintenancedescription']);
$addmaintdescription = str_replace("<p>&nbsp;</p>", "", $addmaintdescription);
$addmaintmessage = mysqli_real_escape_string($link, $_POST['plannedmaintenancemessage']);
if (isset($_POST['plannedmaintenanceaffectedservices'])) {
  $affectedservicesarray = $_POST['plannedmaintenanceaffectedservices'];
  $affectedservicesstr = implode(',', $affectedservicesarray);
} else {
  exit('Missing affected services');
}
$sql = "INSERT INTO incident (incident_date, incident_description, incident_status_short, incident_describes_ids) VALUES ('" . $addmaintstarttimestamp . "', '" . $addmaintdescription . "', 'RES', '" . $affectedservicesstr . "')";
if ($link->query($sql) === TRUE) {
  $incidentid = $link->insert_id;
  $subsql = "INSERT INTO incident_update (incident_update_timestamp, incident_update_status_short, incident_update_description, incident_update_incident_id, incident_update_is_planned_maint) VALUES ('" . $addmaintstarttimestamp . "', 'PLA', 'Planned maintenance begins', '" . $incidentid . "', 'Y')";
  if ($link->query($subsql) === TRUE) {
    $subsubsql = "INSERT INTO incident_update (incident_update_timestamp, incident_update_status_short, incident_update_description, incident_update_incident_id, incident_update_is_planned_maint) VALUES ('" . $addmaintendtimestamp . "', 'RES', '" . $addmaintmessage . "', '" . $incidentid . "', 'Y')";
    if ($link->query($subsubsql) === TRUE) {
      foreach ($affectedservicesarray as &$serviceid) {
        $subsubsubsql = "UPDATE services SET service_status_short = 'PLA' WHERE service_id = " . $serviceid;
        echo 'Updating service ID ' . $serviceid;
        if ($link->query($subsubsubsql) === TRUE) {
?>
<p>Updated service <?=$serviceid?>.</p>
<?php
        } else {
?>
<p>
  Error applying maintenance window to services: <?=$subsubsubsql?>
  <br>
  <?=$link->error?>
</p>
<?php
        }
      }
    } else {
?>
<p>
  Error adding maintenance window end: <?=$subsubsql?>
  <br>
  <?=$link->error?>
<?php
    }
  } else {
?>
<p>
  Error adding maintenance window start: <?=$subsql?>
  <br>
  <?=$link->error?>
</p>
<?php
  }
?>
<?php
} else {
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
