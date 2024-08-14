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
writeToLog($link, 'Existing service API key is being deleted', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
if (isset($_POST['keyid'])) {
  $sql = "DELETE FROM apikeys WHERE apikeys_id = " . mysqli_real_escape_string($link, $_POST['keyid']);
  if ($link->query($sql)) {
    writeToLog($link, 'Old service API key was deleted', $_SESSION['id']);
    echo '<p>The old service API key was deleted.</p>';
  } else {
    writeToLog($link, 'Failed to delete existing API key', $_SESSION['id'], 'WARN');
    echo '<p>Failed to delete existing API key!</p>';
  }
} else {
  writeToLog($link, 'No API key nickname', $_SESSION['id']);
  echo '<p>No API key nickname.</p>';
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