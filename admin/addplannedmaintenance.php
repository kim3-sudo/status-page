<?php
require('_guard.php');
include('../templates/_header.php');
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$addmaintstarttimestamp = $_POST['plannedmaintenancestart'];
$addmaintendtimestamp = $_POST['plannedmaintenanceend'];
$addmaintdescription = str_replace("<p>&nbsp;</p>", "", $_POST['plannedmaintenancedescription']);
$addmaintmessage = $_POST['plannedmaintenancemessage'];
if (isset($_POST['plannedmaintenanceaffectedservices'])) {
  $affectedservicesarray = $_POST['plannedmaintenanceaffectedservices'];
  $affectedservicesstr = implode(',', $affectedservicesarray);
} else {
  exit('Missing affected services');
}
$stmt = $link->prepare("INSERT INTO incident (incident_date, incident_description, incident_status_short, incident_describes_ids) VALUES (?, ?, 'RES', ?)");
$stmt->bind_param('sss', $addmaintstarttimestamp, $addmaintdescription, $affectedservicesstr);
if ($stmt->execute()) {
  $incidentid = $link->insert_id;
  $stmt->close();
  $substmt = $link->prepare("INSERT INTO incident_update (incident_update_timestamp, incident_update_status_short, incident_update_description, incident_update_incident_id, incident_update_is_planned_maint) VALUES (?, 'PLA', 'Planned maintenance begins', ?, 'Y')");
  $substmt->bind_param('si', $addmaintstarttimestamp, $incidentid);
  if ($substmt->execute()) {
    $substmt->close();
    $subsubstmt = $link->prepare("INSERT INTO incident_update (incident_update_timestamp, incident_update_status_short, incident_update_description, incident_update_incident_id, incident_update_is_planned_maint) VALUES (?, 'RES', ?, ?, 'Y')");
    $subsubstmt->bind_param('ssi', $addmaintendtimestamp, $addmaintmessage, $incidentid);
    if ($subsubstmt->execute()) {
      $subsubstmt->close();
      foreach ($affectedservicesarray as &$serviceid) {
        $svcstmt = $link->prepare("UPDATE services SET service_status_short = 'PLA' WHERE service_id = ?");
        $svcstmt->bind_param('i', $serviceid);
        echo 'Updating service ID ' . htmlspecialchars($serviceid);
        if ($svcstmt->execute()) {
?>
<p>Updated service <?=htmlspecialchars($serviceid)?>.</p>
<?php
        } else {
?>
<p>
  Error applying maintenance window to services: <?=htmlspecialchars($link->error)?>
</p>
<?php
        }
        $svcstmt->close();
      }
    } else {
?>
<p>
  Error adding maintenance window end: <?=htmlspecialchars($link->error)?>
<?php
      $subsubstmt->close();
    }
  } else {
?>
<p>
  Error adding maintenance window start: <?=htmlspecialchars($link->error)?>
</p>
<?php
    $substmt->close();
  }
?>
<?php
} else {
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
