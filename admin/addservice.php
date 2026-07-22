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
require('_guard.php');
include('../templates/_header.php');
writeToLog($link, 'Creating new service', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$newservicename = $_POST['newservicename'];
$newserviceingroup = $_POST['newserviceingroup'];
$newservicedescription = $_POST['newservicedescription'];
$newservicelink = $_POST['newservicelink'];
writeToLog($link, 'Adding new service ' . $newservicename . ' to group ' . $newserviceingroup, $_SESSION['id']);
$stmt = $link->prepare("INSERT INTO services (service_name, servicegroup_id, service_description, service_status_short, service_link) VALUES (?, ?, ?, 'OPE', ?)");
$stmt->bind_param('siss', $newservicename, $newserviceingroup, $newservicedescription, $newservicelink);
if ($stmt->execute()) {
  writeToLog($link, 'Created new service', $_SESSION['id']);
  echo '<p>Created new service</p>';
} else {
  writeToLog($link, 'Failed to create new service', $_SESSION['id']);
  echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
}
$stmt->close();
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
