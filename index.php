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

// The status page. Computation lives in lib/status_model.php; this file only lays
// out the result.

if (!file_exists('templates/config.php')) {
  header('Location: /install');
  exit;
}
require_once __DIR__ . '/admin/_guard_functions.php';
include('templates/_header.php');
require_once __DIR__ . '/templates/_incident_list.php';

$isAdmin = isAdminSessionValid($_SESSION);
$now = time();
$windowDays = max(0, (int)getSetting($db, 'incident_to_show_timerange'));
$futureDays = max(0, (int)getSetting($db, 'plannedfuturedays'));
$windowStart = $now - $windowDays * 86400;
$today = epochToSiteLocal($now, $siteZone)->format('Y-m-d');

$inputs = loadStatusInputs($db, $siteZone);
$snapshot = buildStatusSnapshot($inputs, $now, $windowStart);
$publicIncidents = incidentsForPage($inputs['incidents'], $today, $windowDays, $futureDays);
$futureOnlyIncidents = $isAdmin ? incidentsForPage($inputs['incidents'], $today, $windowDays, $futureDays, true) : [];

$banners = [
  'MAJ' => ['class' => 'text-danger',  'icon' => 'fa-triangle-exclamation', 'text' => 'Some systems experiencing major outages'],
  'MIN' => ['class' => 'text-warning', 'icon' => 'fa-exclamation-circle',    'text' => 'Some systems experiencing minor outages'],
  'DEG' => ['class' => 'text-warning', 'icon' => 'fa-exclamation-circle',    'text' => 'Some systems experiencing degraded performance'],
  'PLA' => ['class' => 'text-info',    'icon' => 'fa-info-circle',           'text' => 'Some systems under planned maintenance'],
  'OPE' => ['class' => '',             'icon' => 'fa-check-circle',          'text' => 'All systems operational'],
];
$banner = $banners[$snapshot['banner']];
$bannerIconClass = $banner['class'] === '' ? 'fa-regular' : 'fa-solid';

$about = getSetting($db, 'about_this_site');
$welcomeMessage = getSetting($db, 'welcome_message');
?>
<div class="bg-dark">
  <div class="container-sm">
    <div class="row">
      <div class="col">
<?php if ($isAdmin): ?>
        <div class="text-muted p-2 text-center">
          <p>You are authenticated as an admin. You may see elements that are not ordinarily visible.</p>
        </div>
<?php endif; ?>
        <div class="text-light p-2 text-center">
          <h1><?=e(getSetting($db, 'footer_org'))?> Service Status</h1>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="bg-dark">
  <div class="container-sm">
    <div class="row">
      <div class="col bg-secondary rounded-4 m-5">
        <div class="text-light p-4 text-center">
          <h2><i class="<?=$bannerIconClass?> <?=$banner['icon']?> <?=$banner['class']?>"></i>&nbsp;<?=e($banner['text'])?></h2>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($about !== ''): ?>
<div class="container pt-5">
  <div class="row">
    <div class="col">
      <h2 class="text-muted pb-2">About This Site</h2>
      <p class="text-muted"><?=e($about)?></p>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="">
  <div class="container-sm">
    <div class="row">
      <div class="col">
        <div id="accordionsearchbarcontainer">
          <input type="search" id="accordionsearchbar" placeholder="Search services..." onkeyup="evaluatesearch()">
        </div>
        <div class="accordion py-4 m-2" id="statusparent">
<?php
$panelIds = [];
if ($snapshot['groups'] === []) {
  echo 'None found';
}
foreach ($snapshot['groups'] as $group) {
  $panelId = 'group-' . $group['id'] . '-panel';
  $panelIds[] = $panelId;
  $groupBadge = serviceStatusDisplay($group['status']);
?>
          <div class="accordion-item">
            <h2 class="accordion-header" id="group-<?=$group['id']?>-heading">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?=$panelId?>" aria-expanded="false" aria-controls="<?=$panelId?>">
                <span class="badge <?=e($groupBadge['class'])?>"><?=e($groupBadge['label'])?></span>&nbsp;<?=e($group['name'])?>
              </button>
            </h2>
            <div id="<?=$panelId?>" class="accordion-collapse collapse multi-collapse" aria-labelledby="group-<?=$group['id']?>-heading">
              <div class="accordion-body">
<?php
  if ($group['services'] === []) {
    echo 'No services';
  }
  foreach ($group['services'] as $service) {
    $badge = serviceStatusDisplay($service['status']);
    $link = safeUrl($service['link']);
?>
                <div class="container px-4 py-2">
                  <div class="row">
                    <div class="col-8">
                      <h6 id="service-<?=$service['id']?>-heading" class="servicehead">
                        <span class="badge <?=e($badge['class'])?>" data-bs-toggle="tooltip" data-bs-title="<?=e($badge['tooltip'])?>"><i class="fa-solid <?=e($badge['icon'])?>"></i>&nbsp;<?=e($badge['label'])?></span>
                        &nbsp;<?=e($service['name'])?>
<?php if ((string)$service['description'] !== ''): ?>
                        &nbsp;<i class="text-muted fa-solid fa-question-circle" data-bs-toggle="tooltip" data-bs-title="<?=e($service['description'])?>"></i>
<?php endif; ?>
<?php if ($link !== null): ?>
                        &nbsp;<a href="<?=$link?>"><i class="text-muted fa fa-external-link"></i></a>
<?php endif; ?>
                      </h6>
                    </div>
                    <div class="col-4 text-end">
                      <h6 class="text-muted"><?=number_format($service['uptime'], 2)?>% Uptime</h6>
                    </div>
                  </div>
                </div>
<?php
  }
?>
              </div>
            </div>
          </div>
<?php
}
?>
        </div>
        <div class="text-center">
          <p><small class="text-muted"><em>Uptime is the percentage of the last <?=$windowDays?> days without a minor or major outage. Degraded performance and planned maintenance do not count as downtime.</em></small></p>
<?php if ($panelIds !== []): ?>
          <button class="btn btn-primary m-4 btn-sm" type="button" data-bs-toggle="collapse" data-bs-target=".multi-collapse" aria-expanded="false" aria-controls="<?=e(implode(' ', $panelIds))?>">Expand/Collapse All</button>
<?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <div class="row">
    <div class="col">
      <h2 class="text-uppercase text-muted">Messages</h2>
<?php
if ($isAdmin) {
  renderIncidentList($futureOnlyIncidents, true, $siteZone);
}
if ($publicIncidents === []) {
  echo '<p>No incidents to report</p>';
} else {
  renderIncidentList($publicIncidents, false, $siteZone);
}
?>
    </div>
  </div>
</div>

<div class="container mb-5">
  <div class="row">
    <div class="col pb-3">
      <a href="/pes">View post-event summaries <i class="fa fa-external-link"></i></a>
    </div>
  </div>
</div>

<?php if ($welcomeMessage !== ''): ?>
<div class="modal fade" id="welcomemodal" tabindex="-1" aria-labelledby="welcomemodallabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="welcomemodallabel">Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
      </div>
      <div class="modal-body">
        <p><?=e($welcomeMessage)?></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-secondary" onclick="dontshowmessageagain()">Close and don't show again</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  function getCookie(cname) {
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i].trimStart();
      if (c.indexOf(name) === 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }
  window.onload = () => {
    const welcomemodal = new bootstrap.Modal('#welcomemodal');
    if (getCookie('hidewelcomemodal') === 'true') {
      welcomemodal.hide();
    } else {
      welcomemodal.show();
    }
  };
  function dontshowmessageagain() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('welcomemodal'));
    modal.hide();
    const d = new Date();
    d.setTime(d.getTime() + (24 * 60 * 60 * 1000));
    document.cookie = "hidewelcomemodal=true;expires=" + d.toUTCString() + ";path=/";
  }
</script>
<?php endif; ?>
<?php include('templates/_footer.php'); ?>
