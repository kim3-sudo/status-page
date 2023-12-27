  <div class="container collapse notransition" id="manageservices" data-bs-parent="#actions">
    <p>Welcome, <?=$_SESSION['firstname']?>!</p>
    <p>Manage services</p>
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
$servicegroupsql = 'SELECT servicegroup_id, servicegroup_name FROM servicegroups';
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
        <label for="newservicedescription" class="form-label">New Service Desciption</label>
        <textarea id="newservicedescription" name="newservicedescription" class="form-control" maxlength="255"></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <hr class="mt-3 mb-3">
    <table class="table table-striped table-hover">
      <tbody>
<?php
$servicesql = 'SELECT service_id, service_name, servicegroups.servicegroup_name FROM services INNER JOIN servicegroups ON servicegroups.servicegroup_id = services.servicegroup_id';
$serviceresult = mysqli_query($link, $servicesql);
if (mysqli_num_rows($serviceresult) > 0) {
  while ($servicerow = mysqli_fetch_assoc($serviceresult)) {
?>
<tr>
  <td><button class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#servicemodal<?=$servicerow['service_id']?>"><?=$servicerow['service_name']?></button></td>
  <td><?=$servicerow['servicegroup_name']?></td>
  <td><button type="button" class="link" data-bs-toggle="modal" data-bs-target="#servicemodal<?=$servicerow['service_id']?>delete"><i class="fa-solid fa-xmark text-danger"></i></button></td>
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
