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
$addincidentdescription = mysqli_real_escape_string($link, $_POST['addincidentdescription']);
$addincidentupdatedescription = mysqli_real_escape_string($link, $_POST['addincidentupdatedescription']);
$addincidentstatus = mysqli_real_escape_string($link, $_POST['addincidentstatus']);
if (isset($_POST['affectedservices'])) {
  $affectedservicesarray = $_POST['affectedservices'];
  $affectedservicesstr = implode(',', $affectedservicesarray);
} else {
  exit('Missing affected services');
}
$outageseverity = mysqli_real_escape_string($link, $_POST['outageseverity']);
$sql = "INSERT INTO incident (incident_description, incident_status_short, incident_describes_ids) VALUES ('" . $addincidentdescription . "', '" . $addincidentstatus . "', '" . $affectedservicesstr . "')";
if ($link->query($sql) === TRUE) {
  $incidentid = $link->insert_id;
  $subsql = "INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id) VALUES ('" . $addincidentstatus . "', '" . $addincidentupdatedescription . "', '" . $incidentid . "')";
  if ($link->query($subsql) === TRUE) {
    foreach ($affectedservicesarray as &$serviceid) {
      $subsubsql = "UPDATE services SET service_status_short = '" . $outageseverity . "' WHERE service_id = " . $serviceid;
      echo 'Updating service ID ' . $serviceid;
      if ($link->query($subsubsql) === TRUE) {
?>
<p>Updated service <?=$serviceid?>.</p>
<?php
      } else {
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
