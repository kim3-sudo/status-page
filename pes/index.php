<?php
session_start();
include_once('../templates/_header.php');
?>
<div class="container py-5">
  <div class="row">
    <div class="col">
<?php
?>
      <h1><?=e(getSetting($db, 'footer_org'))?> Post-Event Summaries</h1>
    </div>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col">
<?php
?>
      <p class="pt-3 pb-3"><?=e(getSetting($db, 'pes_description'))?></p>
      <ul>
<?php
$rows = $db->all('SELECT pes_id, pes_title, pes_date, pes_issue_summary, pes_issue_service_impact FROM pes');
if ($rows !== []) {
  foreach ($rows as $row) {
?>
<li>
  <button class="btn btn-link p-0 align-baseline" data-bs-toggle="modal" data-bs-target="#pes<?=e($row['pes_id'])?>modal"><?=e($row['pes_title'])?></button>, <?=e($row['pes_date'])?>
</li>
<div class="modal fade" id="pes<?=e($row['pes_id'])?>modal" tabindex="-1" aria-labelledby="pes<?=e($row['pes_id'])?>modallabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pes<?=e($row['pes_id'])?>modallabel"><?=e($row['pes_title'])?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="container">
          <div class="row">
            <div class="col">
              <h3><?=e($row['pes_title'])?></h3>
              <h5><em><?=e($row['pes_date'])?></em></h5>
              <h4 class="pt-3">Issue Summary</h4>
              <p class="lh-lg pt-3" style="text-align: justify;"><?=e($row['pes_issue_summary'])?></p>
              <h4 class="pt-3">Issue Service Impact</h4>
              <p class="lh-lg pt-3" style="text-align: justify;"><?=e($row['pes_issue_service_impact'])?></p>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
  }
} else {
  echo '<li>No post-event summaries available for viewing</li>';
}
?>
      </ul>
    </div>
  </div>
</div>
<div class="container py-3 mb-5">
  <div class="row">
    <div class="col">
      <a href="/">Return to service status <i class="fa fa-external-link"></i></a>
    </div>
  </div>
</div>
<?php
include_once('../templates/_footer.php');
?>
