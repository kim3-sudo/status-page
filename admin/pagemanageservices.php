<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Permission is hereby granted, free of charge, to any person obtaining a copy of 
  this software and associated documentation files (the “Software”), to deal in 
  the Software without restriction, including without limitation the rights to 
  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies 
  of the Software, and to permit persons to whom the Software is furnished to do 
  so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all 
  copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
  SOFTWARE.
*/
?>
<?php require('_guard.php'); ?>
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
      <thead>
        <tr>
          <th>Service</th>
          <th>Service Group</th>
          <th>Delete</th>
        </tr>
      </thead>
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
