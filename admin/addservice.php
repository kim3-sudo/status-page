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
<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
writeToLog($link, 'Creating new service', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
writeToLog($link, 'Adding new service ' . mysqli_real_escape_string($link, $_POST['newservicename']) . ' to group ' . mysqli_real_escape_string($link, $_POST['newserviceingroup']), $_SESSION['id']);
$sql = "INSERT INTO services (service_name, servicegroup_id, service_description, service_status_short, service_link) VALUE ('" . mysqli_real_escape_string($link, $_POST['newservicename']) . "', " . mysqli_real_escape_string($link, $_POST['newserviceingroup']) . ", '" . mysqli_real_escape_string($link, $_POST['newservicedescription']) . "', 'OPE', '" . mysqli_real_escape_string($link, $_POST['newservicelink']) . "')";
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Created new service', $_SESSION['id']);
  echo '<p>Created new service</p>';
} else {
  writeToLog($link, 'Failed to create new service', $_SESSION['id']);
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
      <a href="./" class="btn btn-primary">Admin Portal</a>
      <button class="btn btn-secondary" onclick="history.back()">Go Back</a>
      </div>
    </div>
  </div>
</div>
<?php
include('../templates/_footer.php');
?>
