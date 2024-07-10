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
writeToLog($link, 'Creating new PES', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$pestitle = mysqli_real_escape_string($link, $_POST['pestitle']);
$pessummary = mysqli_real_escape_string($link, $_POST['pessummary']);
$pesimpact = mysqli_real_escape_string($link, $_POST['pesimpact']);
$pesdate = mysqli_real_escape_string($link, $_POST['pesdate']);
writeToLog($link, 'Adding PES dated ' . $pesdate . ' for '. $pestitle, $_SESSION['id']);
if ($pesdate == "") {
  $sql = "INSERT INTO pes (pes_title, pes_issue_summary, pes_issue_service_impact) VALUES ('" . $pestitle . "', '" . $pessummary . "', '" . $pesimpact . "')";
} else {
  $sql = "INSERT INTO pes (pes_title, pes_date, pes_issue_summary, pes_issue_service_impact) VALUES ('" . $pestitle . "', '" . $pesdate . "', '" . $pessummary . "', '" . $pesimpact . "')";
}
if ($link->query($sql) === TRUE) {
  writeToLog($link, 'Added PES successfully', $_SESSION['id']);
  echo '<p>Added post-event summary</p>';
} else {
  writeToLog($link, 'Failed to add PES', $_SESSION['id']);
  writeToLog($link, $link->error, $_SESSION['id']);
?>
<p>Error: <?=$sql?><br><?=$link->error?></p>
<?php
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
