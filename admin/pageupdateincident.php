  <div class="container collapse notransition" id="updateincident" data-bs-parent="#actions">
    <p>Welcome, <?=$_SESSION['firstname']?>!</p>
    <p>Update an incident</p>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#messagestylemodal">View Style Guide and Examples</button>
    <form action="updateincident.php" method="post">
      <div class="mb-3">
        <label for="existingincident" class="form-label">Existing Incident</label>
        <select id="existingincident" name="existingincident" class="form-control">
          <option selected disabled>Select one...</option>
<?php
$incidentsql = "SELECT incident_id, incident_date, incident_description FROM incident WHERE incident_status_short != 'RES'";
$incidentresult = mysqli_query($link, $incidentsql);
if (mysqli_num_rows($incidentresult) > 0) {
  while ($incidentrow = mysqli_fetch_assoc($incidentresult)) {
    echo '<option value="' . $incidentrow['incident_id'] . '">' . $incidentrow['incident_date'] . ' - ' . $incidentrow['incident_description'] . '</option>';
  }
} else {
  echo '<option disabled>No open incidents</option>';
}
?>
        </select>
      </div>
      <div class="mb-3">
        <label for="existingincidentupdate" class="form-label">Incident Update</label>
        <textarea maxlength="2000" class="form-control" id="existingincidentupdate" name="existingincidentupdate" placeholder="We are investigating the issue with..."></textarea>
      </div>
      <div class="mb-3">
        <label for="existingincidentstatus" class="form-label">Incident Status</label>
        <select id="existingincidentstatus" name="existingincidentstatus" class="form-control" required>
          <option disabled selected>Select one...</option>
<?php
$statussql = 'SELECT incident_status_code, incident_status_description FROM incident_status';
$statusresult = mysqli_query($link, $statussql);
if (mysqli_num_rows($statusresult) > 0) {
  while($statusrow = mysqli_fetch_assoc($statusresult)) {
    echo '<option value="' . $statusrow['incident_status_code'] . '">' . $statusrow['incident_status_description'] . '</option>';
  }
} else {
  echo '<option disabled>No status codes fetched</option>';
}
?>
        </select>
        <p class="form-text">The incident will remain open and will count against uptime until marked as resolved.</p>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <p class="form-text">On submit, an incident update will be issued. If the incident is resolved, the incident itself will also be marked as resolved, all affected services will be marked as operational, and uptime will be marked back as <em>up</em>.</p>
  </div>
