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
  <div class="container collapse notransition" id="plannedmaintenance" data-bs-parent="#actions">
    <h6 class="my-3">Welcome, <?=$_SESSION['firstname']?>!</h6>
    <h1 class="my-3">Planned Maintenance</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#messagestylemodal">View Style Guide and Examples</button>
    <form action="addplannedmaintenance.php" method="post">
      <div class="mb-3">
        <label for="plannedmaintenancestart" class="form-label">Planned Maintenance Start<span class="required">*</span></label>
        <input type="datetime-local" id="plannedmaintenancestart" name="plannedmaintenancestart" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="plannedmaintenanceend" class="form-label">Planned Maintenance End<span class="required">*</span></label>
        <input type="datetime-local" id="plannedmaintenanceend" name="plannedmaintenanceend" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="plannedmaintenancedescription" class="form-label">Planned Maintenance Description<span class="required">*</span></label>
        <input type="text" id="plannedmaintenancedescription" name="plannedmaintenancedescription" maxlength="255" class="form-control" placeholder="Planned Maintenance: Network Maintenance..." required>
      </div>
      <div class="mb-3">
        <label for="plannedmaintenancemessage" class="form-label">Planned Maintenance Message<span class="required">*</span></label>
        <textarea id="plannedmaintenancemessage" name="plannedmaintenancemessage" maxlength="2000" class="form-control tinymce" required>A maintenance window has been planned for [month?] [day?], [year?] between [hour?]:[min?] [AM/PM]? and [hour?]:[min?] [AM/PM?]. During this time, [some?] services may be unavailable as we [what?]. If you have any questions about this maintenance, please contact <a href="mailto:">[who?]</a>.</textarea>
        <div class="alert alert-warning d-none" role="alert" id="plannedmaintenanceplaceholderwarning">
          <b>WARNING: It appears you might have a placeholder in your incident description.</b><br>Placeholders are marked with square brackets and question marks, like [this?]. Did you replace all of the placeholders yet?
        </div>
      </div>
      <div class="mb-3">
        <label for="plannedmaintenanceaffected" class="form-label">Affected Services<span class="required">*</span></label>
<?php
$servicegroupsql = 'SELECT servicegroups.servicegroup_id, servicegroups.servicegroup_name FROM servicegroups ORDER BY servicegroup_name ASC';
$servicegroupresult = mysqli_query($link, $servicegroupsql);
if (mysqli_num_rows($servicegroupresult) > 0) {
  echo '<div class="accordion" id="addpmservices">';
  while ($servicegrouprow = mysqli_fetch_assoc($servicegroupresult)) {
    echo '<div class="accordion-item">';
    echo '<h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sgp' . $servicegrouprow['servicegroup_id'] . '-collapse" aria-expanded="true" aria-controls="sgp' . $servicegrouprow['servicegroup_id'] . '-collapse">';
    echo $servicegrouprow['servicegroup_name'];
    echo '</button></h2>';
    echo '<div id="sgp' . $servicegrouprow['servicegroup_id'] . '-collapse" class="accordion-collapse collapse show">';
    echo '<div class="accordion-body">';
    $servicesql = 'SELECT service_id, servicegroups.servicegroup_name, service_name FROM services INNER JOIN servicegroups ON services.servicegroup_id = servicegroups.servicegroup_id WHERE servicegroups.servicegroup_id = ' . $servicegrouprow['servicegroup_id'] . ' ORDER BY servicegroups.servicegroup_name ASC';
    $serviceresult = mysqli_query($link, $servicesql);
    if (mysqli_num_rows($serviceresult) > 0) {
      while($servicerow = mysqli_fetch_assoc($serviceresult)) {
        echo '<div class="form-check"><input class="form-check-input" type="checkbox" value="' . $servicerow['service_id'] . '" id="serviceupdatecheck' . $servicerow['service_id'] . '" name="plannedmaintenanceaffectedservices[]"><label class="form-check-label" for="servicecheck' . $servicerow['service_id'] . '">' . $servicerow['service_name'] . '</label></div>';
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
      <button class="btn btn-primary" type="submit">Submit</button>
    </form>
  </div>
