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
  <div class="container collapse notransition" id="manageservices" data-bs-parent="#actions">
    <h6 class="my-3">Welcome, <?=$_SESSION['firstname']?>!</h6>
    <h1 class="my-3">Manage services</h1>
    <form action="addservice.php" method="post">
      <div class="mb-3">
        <label for="newservicename" class="form-label">New Service Name<span class="required">*</span></label>
        <input type="text" class="form-control" id="newservicename" name="newservicename" maxlength="64" required placeholder="Service Name">
      </div>
      <div class="mb-3">
        <label for="newserviceingroup" class="form-label">New Service Group<span class="required">*</span></label>
        <select name="newserviceingroup" class="form-control" id="newserviceingroup" required>
          <option disabled selected>Select one...</option>
<?php
$servicegroupsql = 'SELECT servicegroup_id, servicegroup_name FROM servicegroups ORDER BY servicegroup_name ASC';
$servicegroupresult = mysqli_query($link, $servicegroupsql);
if (mysqli_num_rows($servicegroupresult) > 0) {
  while($servicegrouprow = mysqli_fetch_assoc($servicegroupresult)) {
    echo '<option value="' . $servicegrouprow['servicegroup_id'] . '">' . $servicegrouprow['servicegroup_name'] . '</option>';
  }
} else {
  echo '<option disabled>No service groups fetched</option>';
}
?>
        </select>
      </div>
      <div class="mb-3">
        <label for="newservicedescription" class="form-label">New Service Description</label>
        <textarea id="newservicedescription" name="newservicedescription" class="form-control" maxlength="255"></textarea>
      </div>
      <div class="mb-3">
        <label for="newservicelink" class="form-label">New Service External Link</label>
        <input type="url" name="newservicelink" id="newservicelink" class="form-control" maxlength="120">
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <hr class="mt-3 mb-3">
    <table class="table table-striped table-hover">
      <tbody>
<?php
$servicesql = 'SELECT service_id, service_name, servicegroups.servicegroup_name FROM services INNER JOIN servicegroups ON servicegroups.servicegroup_id = services.servicegroup_id ORDER BY servicegroups.servicegroup_name ASC, services.service_name ASC';
$serviceresult = mysqli_query($link, $servicesql);
if (mysqli_num_rows($serviceresult) > 0) {
  while ($servicerow = mysqli_fetch_assoc($serviceresult)) {
?>
<tr>
  <td><button class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#servicemodal<?=$servicerow['service_id']?>"><?=$servicerow['service_name']?></button></td>
  <td><?=$servicerow['servicegroup_name']?></td>
  <td><button type="button" class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#servicemodal<?=$servicerow['service_id']?>delete"><i class="fa-solid fa-xmark text-danger"></i></button></td>
</tr>
<?php
  }
} else {
  echo '<tr><td>No services fetched</td></tr>';
}
?>
      </tbody>
    </table>
  </div>
