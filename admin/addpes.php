<?php
session_start();
if (!isset($_SESSION['id'])) {
  header('Location: ../login.php');
}
include('../templates/_header.php');
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
if ($pesdate == "") {
  $sql = "INSERT INTO pes (pes_title, pes_issue_summary, pes_issue_service_impact) VALUES ('" . $pestitle . "', '" . $pessummary . "', '" . $pesimpact . "')";
} else {
  $sql = "INSERT INTO pes (pes_title, pes_date, pes_issue_summary, pes_issue_service_impact) VALUES ('" . $pestitle . "', '" . $pesdate . "', '" . $pessummary . "', '" . $pesimpact . "')";
}
if ($link->query($sql) === TRUE) {
  echo '<p>Added post-event summary</p>';
} else {
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
