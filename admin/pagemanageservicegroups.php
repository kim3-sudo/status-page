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
  <div class="container collapse notransition" id="managegroups" data-bs-parent="#actions">
    <h6 class="my-3">Welcome, <?=$_SESSION['firstname']?>!</h6>
    <h1 class="my-3">Manage service groups</h1>
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
  <td><button type="button" class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#servicegroup<?=$servicegrouprow['servicegroup_id']?>delete"><i class="fa-solid fa-xmark text-danger"></i></button></td>
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
