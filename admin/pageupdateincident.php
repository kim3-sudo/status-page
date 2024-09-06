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
  <div class="container collapse notransition" id="updateincident" data-bs-parent="#actions">
    <h6 class="my-3">Welcome, <?=$_SESSION['firstname']?>!</h6>
    <h1 class="my-3">Update an incident</h1>
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#messagestylemodal">View Style Guide and Examples</button>
    <form action="updateincident.php" method="post">
      <div class="mb-3">
        <label for="existingincident" class="form-label">Existing Incident<span class="required">*</span></label>
        <select id="existingincident" name="existingincident" class="form-control" required>
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
        <label for="existingincidentupdate" class="form-label">Incident Update<span class="required">*</span></label>
        <textarea maxlength="2000" class="form-control tinymce" id="existingincidentupdate" name="existingincidentupdate" placeholder="We are investigating the issue with...">We have implemented a fix and have determined that the [issue?] has been resolved. Users who continue to have issues should contact the help desk for further assistance. We apologize for this disruption in service, and we appreciate your patience and understanding. We will continue to work and improve to make sure this doesn't happen again.</textarea>
        <div class="alert alert-warning d-none" role="alert" id="existingincidentplaceholderwarning">
          <b>WARNING: It appears you might have a placeholder in your incident description.</b><br>Placeholders are marked with square brackets and question marks, like [this?]. Did you replace all of the placeholders yet?
        </div>
      </div>
      <div class="mb-3">
        <label for="existingincidentstatus" class="form-label">Incident Status<span class="required">*</span></label>
        <select id="existingincidentstatus" name="existingincidentstatus" class="form-control" required>
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
        <p class="text-muted"><small>The incident will remain open and will count against uptime until marked as resolved.</small></p>
      </div>
      <div class="mb-3">
        <label for="updatetimestamp" class="form-label">Update Timestamp</label>
        <input type="datetime-local" id="updatetimestamp" name="updatetimestamp" class="form-control">
        <p class="text-muted"><small>Optional. If no timestamp is provided, the current timestamp will be used.</small></p>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <p class="form-text">On submit, an incident update will be issued. If the incident is resolved, the incident itself will also be marked as resolved, all affected services will be marked as operational, and uptime will be marked back as <em>up</em>.</p>
  </div>
