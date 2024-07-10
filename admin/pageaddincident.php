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
  <div class="container collapse show notransition" id="addincident" data-bs-parent="#actions">
    <h6 class="my-3">Welcome, <?=$_SESSION['firstname']?>!</h6>
    <h1 class="my-3">Add an incident</h1>
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#messagestylemodal">View Style Guide and Examples</button>
    <form action="addincident.php" method="post">
      <div class="mb-3">
        <label for="addincidentdescription" class="form-label">Incident Description<span class="required">*</span></label>
        <input type="text" class="form-control" id="addincidentdescription" name="addincidentdescription" aria-describedby="addincidentdescriptionhelp" maxlength="255" required placeholder="Wireless Connectivity Issue">
        <p id="addincidentdescriptionhelp" class="form-text">This is the incident's headline.</p>
      </div>
      <div class="mb-3">
        <label for="addincidentupdatedescription" class="form-label">Incident Update<span class="required">*</span></label>
        <textarea class="form-control tinymce" id="addincidentupdatedescription" name="addincidentupdatedescription" aria-describedby="addincidentupdatehelp" maxlength="2000" required placeholder="We have identified an issue with...">We have identified an issue with [what?]. When trying to [action], users may experience [symptoms]. We are working to identify and recify the issue as quickly as possible. We have not identified a workaround yet but are working to diagnose the issue. We will leave updates here when we learn more information and as we implement fixes.</textarea>
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
$servicegroupsql = 'SELECT servicegroups.servicegroup_id, servicegroups.servicegroup_name FROM servicegroups ORDER BY servicegroup_name ASC';
$servicegroupresult = mysqli_query($link, $servicegroupsql);
if (mysqli_num_rows($servicegroupresult) > 0) {
  echo '<div class="accordion" id="addincidentservices">';
  while ($servicegrouprow = mysqli_fetch_assoc($servicegroupresult)) {
    echo '<div class="accordion-item">';
    echo '<h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sg' . $servicegrouprow['servicegroup_id'] . '-collapse" aria-expanded="true" aria-controls="sg' . $servicegrouprow['servicegroup_id'] . '-collapse">';
    echo $servicegrouprow['servicegroup_name'];
    echo '</button></h2>';
    echo '<div id="sg' . $servicegrouprow['servicegroup_id'] . '-collapse" class="accordion-collapse collapse show">';
    echo '<div class="accordion-body">';
    $servicesql = 'SELECT service_id, servicegroups.servicegroup_name, service_name FROM services INNER JOIN servicegroups ON services.servicegroup_id = servicegroups.servicegroup_id WHERE servicegroups.servicegroup_id = ' . $servicegrouprow['servicegroup_id'] . ' ORDER BY servicegroups.servicegroup_name ASC';
    $serviceresult = mysqli_query($link, $servicesql);
    if (mysqli_num_rows($serviceresult) > 0) {
      while($servicerow = mysqli_fetch_assoc($serviceresult)) {
        echo '<div class="form-check"><input class="form-check-input" type="checkbox" value="' . $servicerow['service_id'] . '" id="servicecheck' . $servicerow['service_id'] . '" name="affectedservices[]"><label class="form-check-label" for="servicecheck' . $servicerow['service_id'] . '">' . $servicerow['service_name'] . '</label></div>';
      }
    } else {
      echo '<div class="form-check"><input class="form-check-input" type="checkbox" value="" id="servicecheckdisabled" disabled required><label class="form-check-label" for="servicecheckdisabled"></div>';
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
  }
  echo '</div>';
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
