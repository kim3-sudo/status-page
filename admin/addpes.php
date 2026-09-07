<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Permission is hereby granted, free of charge, to any person obtaining a copy of 
  this software and associated documentation files (the “Software”), to deal in 
  the Software without restriction, including without limitation the rights to 
  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies 
  of the Software, and to permit persons to whom the Software is furnished to do 
  so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all 
  copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
  SOFTWARE.
*/
?>
<?php
require('_guard.php');
include('../templates/_header.php');
writeToLog($link, 'Creating new PES', $_SESSION['id']);
?>
<div class="d-flex flex-row">
  <div class="container">
    <div class="row">
      <div class="col">
<?php
$pestitle = $_POST['pestitle'];
$pessummary = $_POST['pessummary'];
$pesimpact = $_POST['pesimpact'];
$pesdate = $_POST['pesdate'];
writeToLog($link, 'Adding PES dated ' . $pesdate . ' for '. $pestitle, $_SESSION['id']);
if ($pesdate == "") {
  $stmt = $link->prepare('INSERT INTO pes (pes_title, pes_issue_summary, pes_issue_service_impact) VALUES (?, ?, ?)');
  $stmt->bind_param('sss', $pestitle, $pessummary, $pesimpact);
} else {
  $stmt = $link->prepare('INSERT INTO pes (pes_title, pes_date, pes_issue_summary, pes_issue_service_impact) VALUES (?, ?, ?, ?)');
  $stmt->bind_param('ssss', $pestitle, $pesdate, $pessummary, $pesimpact);
}
if ($stmt->execute()) {
  writeToLog($link, 'Added PES successfully', $_SESSION['id']);
  echo '<p>Added post-event summary</p>';
} else {
  writeToLog($link, 'Failed to add PES', $_SESSION['id']);
  writeToLog($link, $link->error, $_SESSION['id']);
?>
<p>Error: <?=htmlspecialchars($link->error)?></p>
<?php
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
