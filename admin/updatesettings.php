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
writeToLog($link, 'Updating setting value', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
writeToLog($link, 'Updating setting key ' . mysqli_real_escape_string($link, $_POST['setting_key']), $_SESSION['id']);
writeToLog($link, 'Updating setting value to ' . mysqli_real_escape_string($link, $_POST['setting_value']), $_SESSION['id']);
// Use INSERT … ON DUPLICATE KEY UPDATE so this works correctly for both:
//  (a) existing rows (standard update), and
//  (b) keys that were added after install (e.g. saml_email_attribute on older installs).
$sql = "INSERT INTO settings (setting_key, setting_value)
        VALUES ('" . mysqli_real_escape_string($link, $_POST['setting_key']) . "',
                '" . mysqli_real_escape_string($link, $_POST['setting_value']) . "')
        ON DUPLICATE KEY UPDATE
        setting_value = '" . mysqli_real_escape_string($link, $_POST['setting_value']) . "'";
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Updated setting', $_SESSION['id']);
  echo '<p>Updated setting</p>';
} else {
  writeToLog($link, 'Failed to update setting', $_SESSION['id'], 'WARN');
  echo '<p>Error: ' . $sql . '<br>' . $link->error . '</p>';
}
?>
      <a href="./" class="btn btn-primary">Admin Portal</a>
      <button class="btn btn-secondary" onclick="history.back()">Go Back</button>
      </div>
    </div>
  </div>
</div>
<?php
include('../templates/_footer.php');
?>
