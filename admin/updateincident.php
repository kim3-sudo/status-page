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
$existingincident = mysqli_real_escape_string($link, $_POST['existingincident']);
$existingincidentupdate = mysqli_real_escape_string($link, $_POST['existingincidentupdate']);
$existingincidentstatus = mysqli_real_escape_string($link, $_POST['existingincidentstatus']);
$sql = "INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id) VALUES ('" . $existingincidentstatus . "', '" . $existingincidentupdate . "', " . $existingincident . ")";
if ($link->query($sql) === TRUE) {
  $addsql = "UPDATE incident SET incident_status_short = '" . $existingincidentstatus . "' WHERE incident_id = " . $existingincident;
  if ($link->query($addsql) === TRUE) {
    echo '<p>Updated incident status</p>';
  } else {
    echo '<p>Error: ' . $addsql . '<br>' . $link->error . '</p>';
  }
  if ($existingincidentstatus == 'RES') {
    // get the list of affected services
    $subsql = "SELECT incident_describes_ids FROM incident WHERE incident_id = " . $existingincident;
    $row = mysqli_fetch_assoc(mysqli_query($link, $subsql));
    $idsarray = preg_split("/\,/", $row['incident_describes_ids']);;
    // mark the affected services as operational
    foreach ($idsarray as &$activeserviceid) {
      $subsql = "UPDATE services SET service_status_short = 'OPE' WHERE service_id = " . $activeserviceid;
      if ($link->query($subsql) === TRUE) {
        echo '<p>Set service ' . $activeserviceid . ' to operational.</p>';
      } else {
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
