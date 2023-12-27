  <div class="container collapse show notransition" id="addincident" data-bs-parent="#actions">
    <p>Welcome, <?=$_SESSION['firstname']?>!</p>
    <p>Add an incident</p>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#messagestylemodal">View Style Guide and Examples</button>
    <form action="addincident.php" method="post">
      <div class="mb-3">
        <label for="addincidentdescription" class="form-label">Incident Description<span class="required">*</span></label>
        <input type="text" class="form-control" id="addincidentdescription" name="addincidentdescription" aria-describedby="addincidentdescriptionhelp" maxlength="255" required placeholder="Wireless Connectivity Issue">
        <p id="addincidentdescriptionhelp" class="form-text">This is the incident's headline.</p>
      </div>
      <div class="mb-3">
        <label for="addincidentupdatedescription" class="form-label">Incident Update<span class="required">*</span></label>
        <textarea class="form-control" id="addincidentupdatedescription" name="addincidentupdatedescription" aria-describedby="addincidentupdatehelp" maxlength="2000" required placeholder="We have identified an issue with..."></textarea>
        <p id="addincidentupdatehelp" class="form-text">This is a description of symptoms and resolutions.</p>
      </div>
      <div class="mb-3">
        <label for="addincidentstatus" class="form-label">Incident Status<span class="required">*</span></label>
        <select id="addincidentstatus" name="addincidentstatus" class="form-control" required>
          <option disabled selected>Select one...</option>
<?php
$statussql = 'SELECT incident_status_code, incident_status_description FROM incident_status';
$statusresult = mysqli_query($link, $statussql);
if (mysqli_num_rows($statusresult) > 0) {
  while($statusrow = mysqli_fetch_assoc($statusresult)) {
    if ($statusrow['incident_status_code'] != 'PLA') {
      echo '<option value="' . $statusrow['incident_status_code'] . '">' . $statusrow['incident_status_description'] . '</option>';
    }
  }
} else {
  echo '<option disabled>No status codes fetched</option>';
}
?>
        </select>
      </div>
      <div class="mb-3">
        <label for="addincidentaffected" name="addincidentaffected" class="form-label">Affected Services<span class="required">*</span></label>
<?php
$servicesql = 'SELECT service_id, servicegroups.servicegroup_name, service_name FROM services INNER JOIN servicegroups ON services.servicegroup_id = servicegroups.servicegroup_id ORDER BY servicegroups.servicegroup_name ASC';
$serviceresult = mysqli_query($link, $servicesql);
if (mysqli_num_rows($statusresult) > 0) {
  while($servicerow = mysqli_fetch_assoc($serviceresult)) {
    echo '<div class="form-check"><input class="form-check-input" type="checkbox" value="' . $servicerow['service_id'] . '" id="servicecheck' . $servicerow['service_id'] . '" name="affectedservices[]"><label class="form-check-label" for="servicecheck' . $servicerow['service_id'] . '">' . $servicerow['servicegroup_name'] . ' - ' . $servicerow['service_name'] . '</label></div>';
  }
} else {
  echo '<div class="form-check"><input class="form-check-input" type="checkbox" value="" id="servicecheckdisabled" disabled required><label class="form-check-label" for="servicecheckdisabled"></div>';
}
?>
      </div>
      <div class="mb-3">
        <label for="outageseverity" name="outageseverity" class="form-label">Outage Severity<span class="required">*</span></label>
        <select id="outageseverity" name="outageseverity" class="form-control" required>
          <option disabled selected>Select one...</option>
<?php
$severitysql = 'SELECT service_status_code, service_status_description FROM service_status';
$severityresult = mysqli_query($link, $severitysql);
if (mysqli_num_rows($severityresult) > 0) {
  while($severityrow = mysqli_fetch_assoc($severityresult)) {
    if ($severityrow['service_status_code'] != 'PLA') {
      echo '<option value="' . $severityrow['service_status_code'] . '">' . $severityrow['service_status_description'] . '</option>';
    }
  }
} else {
  echo '<option disabled>No severity status codes fetched</option>';
}
?>
        </select>
      </div>
      <div class="mb-3">
        <label for="starttimestamp" class="form-label">Start Timestamp</label>
        <input type="datetime-local" id="starttimestamp" name="starttimestamp" class="form-control">
        <p class="text-muted"><small>Optional. If no timestamp is provided, the current timestamp will be used.</small></p>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
  </div>
