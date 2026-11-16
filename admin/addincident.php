<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/
?>
<?php
require('_guard.php');
include('../templates/_header.php');
require_once __DIR__ . '/../lib/incidents.php';
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
try {
  $incidentid = createIncident($db, [
    'title' => $_POST['addincidentdescription'] ?? '',
    'status' => $_POST['addincidentstatus'] ?? '',
    'update' => str_replace('<p>&nbsp;</p>', '', (string)($_POST['addincidentupdatedescription'] ?? '')),
    'severity' => $_POST['outageseverity'] ?? '',
    'services' => $_POST['affectedservices'] ?? [],
    'start' => $_POST['starttimestamp'] ?? '',
  ], $siteZone, (int)$_SESSION['id']);
  echo '<p>Created incident ' . $incidentid . ' and set the affected services.</p>';
} catch (IncidentValidationException $e) {
  echo '<p class="text-danger">' . e($e->getMessage()) . '</p>';
}
?>
      <a href="./" class="btn btn-primary">Admin Portal</a>
      <button class="btn btn-secondary" data-history-back>Go Back</button>
      </div>
    </div>
  </div>
</div>
<?php
include('../templates/_footer.php');
?>
