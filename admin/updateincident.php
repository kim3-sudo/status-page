<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Updating an incident', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$existingincident = mysqli_real_escape_string($link, $_POST['existingincident']);
$existingincidentupdate = mysqli_real_escape_string($link, $_POST['existingincidentupdate']);
$existingincidentstatus = mysqli_real_escape_string($link, $_POST['existingincidentstatus']);
writeToLog($link, 'Updating incident ' . $existingincident, $_SESSION['id']);
writeToLog($link, $existingincidentupdate, $_SESSION['id']);
writeToLog($link, $existingincidentstatus, $_SESSION['id']);
if ($_POST['updatetimestamp'] != '') {
  writeToLog($link, 'Incident update has a timestamp, using timestamped query', $_SESSION['id']);
  $updatetimestamp = mysqli_real_escape_string($link, $_POST['updatetimestamp']);
  $sql = "INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id, incident_update_timestamp) VALUES ('" . $existingincidentstatus . "', '" . $existingincidentupdate . "', " . $existingincident . ", '" . $updatetimestamp . "')";
} else {
  writeToLog($link, 'Incident update has no timestamp', $_SESSION['id']);
  $sql = "INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id) VALUES ('" . $existingincidentstatus . "', '" . $existingincidentupdate . "', " . $existingincident . ")";
}
writeToLog($link, 'Executing update query', $_SESSION['id']);
if ($link->query($sql) === TRUE) {
  $addsql = "UPDATE incident SET incident_status_short = '" . $existingincidentstatus . "' WHERE incident_id = " . $existingincident;
  if ($link->query($addsql) === TRUE) {
    writeToLog($link, 'Updated incident status', $_SESSION['id']);
    echo '<p>Updated incident status</p>';
  } else {
    writeToLog($link, 'Failed to update incident status', $_SESSION['id']);
    echo '<p>Error: ' . $addsql . '<br>' . $link->error . '</p>';
  }
  if ($existingincidentstatus == 'RES') {
    writeToLog($link, 'Incident was marked as resolved', $_SESSION['id']);
    // get the list of affected services
    writeToLog($link, 'Parsing incident affected services', $_SESSION['id']);
    $subsql = "SELECT incident_describes_ids FROM incident WHERE incident_id = " . $existingincident;
    $row = mysqli_fetch_assoc(mysqli_query($link, $subsql));
    writeToLog($link, $row['incident_describes_ids'], $_SESSION['id']);
    $idsarray = preg_split("/\,/", $row['incident_describes_ids']);;
    // mark the affected services as operational
    foreach ($idsarray as &$activeserviceid) {
      writeToLog($link, 'Marking service ' . $activeserviceid . ' as operational', $_SESSION['id']);
      $subsql = "UPDATE services SET service_status_short = 'OPE' WHERE service_id = " . $activeserviceid;
      if ($link->query($subsql) === TRUE) {
        writeToLog($link, 'Marked service as operational', $_SESSION['id']);
        echo '<p>Set service ' . $activeserviceid . ' to operational.</p>';
      } else {
        writeToLog($link, 'Failed to mark service as operational', $_SESSION['id']);
        echo '<p>Error: ' . $subsql . '<br>' . $link->error . '</p>';
      }
    }
  }
?>
<p>Incident update added successfully</p>
<?php
} else {
?>
<p>Error: <?=$sql?><br><?=$link->error?></p>
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
