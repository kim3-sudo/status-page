  <div class="container collapse notransition" id="managegroups" data-bs-parent="#actions">
    <p>Welcome, <?=$_SESSION['firstname']?>!</p>
    <p>Manage groups</p>
    <form action="addservicegroup.php" method="post">
      <div class="mb-3">
        <label for="newservicegroupname" class="form-label">New Service Group Name<span class="required">*</span></label>
        <input type="text" class="form-control" id="newservicegroupname" name="newservicegroupname" maxlength="64" required>
      </div>
      <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <hr class="mt-3 mb-3">
    <table class="table table-striped table-hover">
      <tbody>
<?php
$servicegroupsql = 'SELECT servicegroup_id, servicegroup_name FROM servicegroups ORDER BY servicegroup_name ASC';
$servicegroupresult = mysqli_query($link, $servicegroupsql);
if (mysqli_num_rows($servicegroupresult) > 0) {
  while ($servicegrouprow = mysqli_fetch_assoc($servicegroupresult)) {
?>
<tr>
  <td><button class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#servicegroup<?=$servicegrouprow['servicegroup_id']?>modal"><?=$servicegrouprow['servicegroup_name']?></button></td>
  <td><button type="button" class="link" data-bs-toggle="modal" data-bs-target="#servicegroup<?=$servicegrouprow['servicegroup_id']?>delete"><i class="fa-solid fa-xmark text-danger"></i></button></td>
</tr>
<?php
  }
} else {
  echo '<tr><td>No service groups fetched</td></tr>';
}
?>
      </tbody>
    </table>
  </div>
