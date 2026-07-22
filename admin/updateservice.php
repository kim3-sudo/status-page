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
writeToLog($link, 'Updating service', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$updateid = $_POST['updateid'];
$updatename = $_POST['updatename'];
$updategroup = $_POST['updategroup'];
$updatedescription = $_POST['updatedescription'];
$updatelink = $_POST['updatelink'];

writeToLog($link, 'Updating service ' . $updateid, $_SESSION['id']);
writeToLog($link, 'Updating service name to ' . $updatename, $_SESSION['id']);
$stmt = $link->prepare('UPDATE services SET service_name = ? WHERE service_id = ?');
$stmt->bind_param('si', $updatename, $updateid);
if ($stmt->execute()) {
  writeToLog($link, 'Updated service name', $_SESSION['id']);
  echo '<p>Updated service name</p>';
} else {
  writeToLog($link, 'Failed to update service name', $_SESSION['id'], 'WARN');
  echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
}
$stmt->close();
?>
<?php
writeToLog($link, 'Updating service group to ' . $updategroup, $_SESSION['id']);
$stmt = $link->prepare('UPDATE services SET servicegroup_id = ? WHERE service_id = ?');
$stmt->bind_param('ii', $updategroup, $updateid);
if ($stmt->execute()) {
  writeToLog($link, 'Updated service group', $_SESSION['id']);
  echo '<p>Updated service group</p>';
} else {
  writeToLog($link, 'Failed to update service group', $_SESSION['id'], 'WARN');
  echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
}
$stmt->close();
?>
<?php
writeToLog($link, 'Updating service description to ' . $updatedescription, $_SESSION['id']);
$stmt = $link->prepare('UPDATE services SET service_description = ? WHERE service_id = ?');
$stmt->bind_param('si', $updatedescription, $updateid);
if ($stmt->execute()) {
  writeToLog($link, 'Updated service description', $_SESSION['id']);
  echo '<p>Updated service description</p>';
} else {
  writeToLog($link, 'Failed to update service description', $_SESSION['id'], 'WARN');
  echo '<p>Error: ' . htmlspecialchars($link->error) . '</p>';
}
$stmt->close();
?>
<?php
writeToLog($link, 'Updating service link to ' . $updatelink, $_SESSION['id']);
$stmt = $link->prepare('UPDATE services SET service_link = ? WHERE service_id = ?');
$stmt->bind_param('si', $updatelink, $updateid);
if ($stmt->execute()) {
  writeToLog($link, 'Updated service link', $_SESSION['id']);
  echo '<p>Updated service link</p>';
} else {
  writeToLog($link, 'Failed to update service link', $_SESSION['id'], 'WARN');
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
