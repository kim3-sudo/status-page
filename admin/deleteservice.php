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
writeToLog($link, 'Deleting service', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$deleteserviceid = $_POST['deleteserviceid'];
writeToLog($link, 'Deleting service ' . $deleteserviceid, $_SESSION['id']);
$stmt = $link->prepare('DELETE FROM services WHERE service_id = ?');
$stmt->bind_param('i', $deleteserviceid);
if ($stmt->execute()) {
  writeToLog($link, 'Deleted the service', $_SESSION['id']);
  echo '<p>Deleted service</p>';
} else {
  writeToLog($link, 'Failed to delete the service', $_SESSION['id']);
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
