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
if (!file_exists('templates/config.php')) {
  header('Location: /install');
}
include('templates/_header.php');

// Update planned maintenance statuses on page load.
// Services with a resolved planned maintenance update that is now in the past
// are flipped back to Operational.
$sql = "SELECT incident_update_incident_id FROM incident_update WHERE incident_update_timestamp < CURRENT_TIMESTAMP() AND incident_update_is_planned_maint IS NOT NULL AND incident_update_status_short = 'RES'";
$result = mysqli_query($link, $sql);
if (mysqli_num_rows($result) > 0) {
  while ($xincidentupdate = mysqli_fetch_assoc($result)) {
    $sql = "SELECT incident_describes_ids FROM incident WHERE incident_id = " . $xincidentupdate['incident_update_incident_id'] . " AND incident_date < CURRENT_TIMESTAMP()";
    $inner = mysqli_query($link, $sql);
    if (mysqli_num_rows($inner) > 0) {
      while ($xincident = mysqli_fetch_assoc($inner)) {
        foreach (explode(',', $xincident['incident_describes_ids']) as $value) {
          $link->query("UPDATE services SET service_status_short = 'OPE' WHERE service_id = " . $value . " AND service_status_short = 'PLA'");
          $link->query("UPDATE incident_update SET incident_update_is_planned_maint = NULL WHERE incident_update_timestamp < CURRENT_DATE()");
        }
      }
    }
  }
}
?>
<div class="bg-dark">
  <div class="container-sm">
    <div class="row">
      <div class="col">
<?php if (isset($_SESSION['firstname'])): ?>
        <div class="text-muted p-2 text-center">
          <p>You are authenticated as an admin. You may see elements that are not ordinarily visible.</p>
        </div>
<?php endif; ?>
        <div class="text-light p-2 text-center">
          <h1><?=getSetting($link, 'footer_org')?> Service Status</h1>
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
<?php
// Build a map of status code => count across all services
$statusarray = [];
$result = mysqli_query($link, "SELECT service_status_short, COUNT(*) FROM services GROUP BY service_status_short");
while ($servicecount = mysqli_fetch_assoc($result)) {
  $statusarray[$servicecount['service_status_short']] = $servicecount['COUNT(*)'];
}

if (array_key_exists('MAJ', $statusarray)) {
  echo '<h2><i class="text-danger fa-solid fa-triangle-exclamation"></i>&nbsp;Some systems experiencing major outages</h2>';
} elseif (array_key_exists('MIN', $statusarray)) {
  echo '<h2><i class="text-warning fa-solid fa-exclamation-circle"></i>&nbsp;Some systems experiencing minor outages</h2>';
} elseif (array_key_exists('DEG', $statusarray)) {
  echo '<h2><i class="text-warning fa-solid fa-exclamation-circle"></i>&nbsp;Some systems experiencing degraded performance</h2>';
} elseif (array_key_exists('PLA', $statusarray)) {
  // Check whether any maintenance window is currently active
  $inmaintenancewindow = false;
  $result = mysqli_query($link, "SELECT incident_update_incident_id FROM incident_update WHERE incident_update_status_short = 'PLA' AND incident_update_timestamp <= NOW()");
  while ($maintenancewindow = mysqli_fetch_assoc($result)) {
    $maintresult = mysqli_query($link, "SELECT incident_update_timestamp FROM incident_update WHERE incident_update_incident_id = " . $maintenancewindow['incident_update_incident_id'] . " AND incident_update_status_short = 'PLA' OR incident_update_status_short = 'RES' ORDER BY incident_update_timestamp ASC");
    $maintenancetimearray = [];
    while ($maintenancetimes = mysqli_fetch_assoc($maintresult)) {
      $maintenancetimearray[] = strtotime($maintenancetimes['incident_update_timestamp']);
    }
    if (isset($maintenancetimearray[7], $maintenancetimearray[8]) && $maintenancetimearray[7] < time() && time() < $maintenancetimearray[8]) {
      $inmaintenancewindow = true;
    }
  }
  if ($inmaintenancewindow) {
    echo '<h2><i class="text-info fa-solid fa-info-circle"></i>&nbsp;Some systems under planned maintenance</h2>';
  } else {
    echo '<h2><i class="fa-regular fa-check-circle"></i>&nbsp;All systems operational</h2>';
  }
} else {
  echo '<h2><i class="fa-regular fa-check-circle"></i>&nbsp;All systems operational</h2>';
}
?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$about = getSetting($link, 'about_this_site');
if ($about != ''):
?>
<div class="container pt-5">
  <div class="row">
    <div class="col">
      <h2 class="text-muted pb-2">About This Site</h2>
      <p class="text-muted"><?=$about?></p>
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
$result = mysqli_query($link, 'SELECT servicegroup_id, servicegroup_name FROM servicegroups ORDER BY servicegroup_name ASC');
$cleangroupnames = [];
if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $cleangroupname = strtolower(preg_replace('/\W/', '', $row['servicegroup_name']));
    $cleangroupnames[] = $cleangroupname;
?>
          <div class="accordion-item">
            <h2 class="accordion-header" id="<?=$cleangroupname?>Heading">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?=$cleangroupname?>panel" aria-expanded="false" aria-controls="<?=$cleangroupname?>panel">
                <span id="<?=$cleangroupname?>badge" class="badge text-bg-success">Operational</span>&nbsp;<?=$row['servicegroup_name']?>
              </button>
            </h2>
            <div id="<?=$cleangroupname?>panel" class="accordion-collapse collapse multi-collapse" aria-labelledby="<?=$cleangroupname?>Heading">
              <div class="accordion-body">
<?php
    $servicesql = 'SELECT service_id, service_name, service_description, service_link, service_status_short, service_status.service_status_description FROM services INNER JOIN servicegroups ON services.servicegroup_id = servicegroups.servicegroup_id INNER JOIN service_status ON service_status.service_status_code = services.service_status_short WHERE servicegroups.servicegroup_id = ' . $row['servicegroup_id'] . ' ORDER BY service_name ASC';
    $serviceresult = mysqli_query($link, $servicesql);
    if (mysqli_num_rows($serviceresult) > 0) {
      while ($service = mysqli_fetch_assoc($serviceresult)) {
        $cleanname = strtolower(preg_replace('/\W/', '', $service['service_name']));
        // Determine badge/icon and group badge update script based on status
        $statusBadges = [
          'OPE' => ['class' => 'text-bg-success', 'icon' => 'fa-check-circle',         'label' => 'Operational',          'tooltip' => 'The service is working normally and as expected.'],
          'DEG' => ['class' => 'text-bg-warning', 'icon' => 'fa-exclamation-circle',    'label' => 'Degraded Performance', 'tooltip' => 'The service may be experiencing degraded performance.'],
          'PLA' => ['class' => 'text-bg-info',    'icon' => 'fa-circle-info',           'label' => 'Planned Maintenance',  'tooltip' => 'There is planned maintenance for this system soon.'],
          'MIN' => ['class' => 'text-bg-warning', 'icon' => 'fa-exclamation-circle',    'label' => 'Minor Outage',         'tooltip' => 'The system may be experiencing a minor outage right now.'],
          'MAJ' => ['class' => 'text-bg-danger',  'icon' => 'fa-triangle-exclamation',  'label' => 'Major Outage',         'tooltip' => 'The system may be experiencing a major outage right now.'],
        ];
        $s = $statusBadges[$service['service_status_short']] ?? $statusBadges['OPE'];
?>
                <div class="container px-4 py-2">
                  <div class="row">
                    <div class="col-8">
                      <h6 id="<?=$cleanname?>heading" class="servicehead">
                        <span class="badge <?=$s['class']?>" data-bs-toggle="tooltip" data-bs-title="<?=$s['tooltip']?>"><i class="fa-solid <?=$s['icon']?>"></i>&nbsp;<?=$s['label']?></span>
<?php if ($service['service_status_short'] !== 'OPE'): ?>
                        <script>
                          (function() {
                            var b = document.getElementById("<?=$cleangroupname?>badge");
                            b.className = b.className.replace(/text-bg-\S+/, "<?=$s['class']?>");
                            b.innerHTML = "<?=$s['label']?>";
                          })();
                        </script>
<?php endif; ?>
                        &nbsp;<?=$service['service_name']?>
<?php if ($service['service_description'] != ''): ?>
                        &nbsp;<i class="text-muted fa-solid fa-question-circle" data-bs-toggle="tooltip" data-bs-title="<?=$service['service_description']?>"></i>
<?php endif; ?>
<?php if ($service['service_link'] != ''): ?>
                        &nbsp;<a href="<?=$service['service_link']?>"><i class="text-muted fa fa-external-link"></i></a>
<?php endif; ?>
                      </h6>
                    </div>
                    <div class="col-4 text-end">
                      <h6 class="text-muted">
<?php
        // Calculate uptime percentage over the configured timerange
        $itst = (int)getSetting($link, 'incident_to_show_timerange');
        $uptimesql = "SELECT DISTINCT incident_update_incident_id FROM incident_update INNER JOIN incident ON incident_update.incident_update_incident_id = incident.incident_id WHERE incident_update_timestamp > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL " . $itst . " DAY) AND incident_update_timestamp <= NOW() AND instr(concat(',', incident.incident_describes_ids, ','), '," . $service['service_id'] . ",') <> 0";
        $uptimeresult = mysqli_query($link, $uptimesql);
        if (mysqli_num_rows($uptimeresult) == 0) {
          echo '100.00';
        } else {
          $accumulatedtime = 0;
          $incidentidarray = [];
          while ($uptimerow = mysqli_fetch_assoc($uptimeresult)) {
            $incidentidarray[] = $uptimerow['incident_update_incident_id'];
          }
          foreach ($incidentidarray as $incidentid) {
            $incidentaccumresult = mysqli_query($link, "SELECT incident_update_timestamp, incident_update_status_short FROM incident_update WHERE incident_update_incident_id = " . $incidentid);
            $incidenttimes = [];
            $resolved = idate('U');
            while ($incidentaccumrow = mysqli_fetch_assoc($incidentaccumresult)) {
              if ($incidentaccumrow['incident_update_status_short'] == 'RES') {
                $resolved = strtotime($incidentaccumrow['incident_update_timestamp']);
              } else {
                $incidenttimes[] = strtotime($incidentaccumrow['incident_update_timestamp']);
              }
            }
            $accumulatedtime += abs($resolved - min($incidenttimes));
          }
          echo 100 - round(($accumulatedtime / ($itst * 24 * 60 * 60)) * 100, 2);
        }
?>% Uptime
                      </h6>
                    </div>
                  </div>
                </div>
<?php
      }
    } else {
      echo 'No services';
    }
?>
              </div>
            </div>
          </div>
<?php
  }
} else {
  echo 'None found';
}
?>
        </div>
        <div class="text-center">
          <p><small class="text-muted"><em>Uptime is calculated as the percentage of time services were fully operational over the last <?=$itst?> days.</em></small></p>
          <button class="btn btn-primary m-4 btn-sm" type="button" data-bs-toggle="collapse" data-bs-target=".multi-collapse" aria-expanded="false" aria-controls="<?php foreach ($cleangroupnames as $cgn) { echo $cgn . 'panel '; } ?>">Expand/Collapse All</button>
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
$plannedfuturedays     = (int)getSetting($link, 'plannedfuturedays');
$incident_to_show_timerange = (int)getSetting($link, 'incident_to_show_timerange');

// Admin-only: show incidents further in the future than the public limit
if ($_SESSION['id'] != '') {
  $incidentsql = 'SELECT incident_id, incident_date, incident_description, incident_status_short, incident_status.incident_status_description FROM incident INNER JOIN incident_status ON incident_status.incident_status_code = incident.incident_status_short WHERE incident.incident_date >= DATE_ADD(CURDATE(), INTERVAL ' . $plannedfuturedays . ' DAY) ORDER BY incident_date DESC';
  $incidentresult = mysqli_query($link, $incidentsql);
  while ($incident = mysqli_fetch_assoc($incidentresult)) {
    $prettydate = date_format(date_create($incident['incident_date']), 'M. jS, Y');
?>
          <div class="relative incidents-monthly__item pt-2 pb-3 line">
            <p class="incidents-monthly__item__month text-black dark:text-white group-hover:opacity-100 ">
              <span><?=$prettydate?></span>
            </p>
            <div class="incident-details incident-item">
              <div class="flex items-start justify-between">
                <div class="incident-details__header">
                  <span>
                    <?= $incident['incident_status_short'] != 'RES' ? '<i class="fa-solid fa-check-circle"></i>' : '<i class="fa-solid fa-exclamation-circle"></i>' ?>
                  </span>
                  <span class="incident-name"><?=$incident['incident_description']?></span>
                </div>
              </div>
              <p><small class="text-muted">This element is not visible unless you are logged in because the event is more than <?=$plannedfuturedays?> days in the future.</small></p>
              <ul class="incident-details__updates">
<?php
    $updatesql = 'SELECT incident_update_timestamp, incident_update_description, incident_status.incident_status_description FROM incident_update INNER JOIN incident_status ON incident_update.incident_update_status_short = incident_status.incident_status_code WHERE incident_update_incident_id = ' . $incident['incident_id'] . ' ORDER BY incident_update_timestamp DESC';
    $updateresult = mysqli_query($link, $updatesql);
    if (mysqli_num_rows($updateresult) > 0) {
      $updatecount = 0;
      while ($update = mysqli_fetch_assoc($updateresult)) {
        $prettydate = date_format(date_create($update['incident_update_timestamp']), 'M jS, Y') . ' at ' . date_format(date_create($update['incident_update_timestamp']), 'g:i A');
        $statusClass = $updatecount === 0 ? 'update-list-item__status--desktop first' : 'update-list-item__status--desktop default stormtrooper';
        $adminNote   = $updatecount === 0 ? '<small class="text-muted">This is invisible to non-authenticated users.</small>' : '';
?>
                <li class="incident-update update-list-item">
                  <div class="update-list-item__inner-wrapper">
                    <div title="<?=$update['incident_status_description']?>" class="update-list-item__status <?=$statusClass?>"><?=$update['incident_status_description']?><?=$adminNote?></div>
                    <div class="update-list-item__message">
                      <span class="opacity-75 inline updated-list-item__date"><p><?=$prettydate?></p></span>
                      <div class="inline prose-sm prose dark:prose-invert">
                        <?php renderUpdateContent($update['incident_update_description']); ?>
                      </div>
                    </div>
                  </div>
                </li>
<?php
        $updatecount++;
      }
    } else {
      echo '<li><p>No updates</p></li>';
    }
?>
              </ul>
            </div>
          </div>
<?php
  }
}

// Public incidents within the configured time range
$incidentsql = 'SELECT incident_id, incident_date, incident_description, incident_status_short, incident_status.incident_status_description FROM incident INNER JOIN incident_status ON incident_status.incident_status_code = incident.incident_status_short WHERE incident.incident_date >= DATE_ADD(CURDATE(), INTERVAL -' . $incident_to_show_timerange . ' DAY) AND incident.incident_date <= DATE_ADD(CURDATE(), INTERVAL ' . $plannedfuturedays . ' DAY) ORDER BY incident_date DESC';
$incidentresult = mysqli_query($link, $incidentsql);
if (mysqli_num_rows($incidentresult) > 0) {
  while ($incident = mysqli_fetch_assoc($incidentresult)) {
    $prettydate = date_format(date_create($incident['incident_date']), 'M. jS, Y');
?>
          <div class="relative incidents-monthly__item pt-2 pb-3 line">
            <p class="incidents-monthly__item__month text-black dark:text-white group-hover:opacity-100 ">
              <span><?=$prettydate?></span>
            </p>
            <div class="incident-details incident-item">
              <div class="flex items-start justify-between">
                <div class="incident-details__header">
                  <span>
                    <?= $incident['incident_status_short'] === 'RES' ? '<i class="fa-solid fa-check-circle"></i>' : '<i class="fa-solid fa-exclamation-circle"></i>' ?>
                  </span>
                  <span class="incident-name"><?=$incident['incident_description']?></span>
                </div>
              </div>
              <ul class="incident-details__updates">
<?php
    $updatesql = 'SELECT incident_update_timestamp, incident_update_description, incident_status.incident_status_description FROM incident_update INNER JOIN incident_status ON incident_update.incident_update_status_short = incident_status.incident_status_code WHERE incident_update_incident_id = ' . $incident['incident_id'] . ' ORDER BY incident_update_timestamp DESC';
    $updateresult = mysqli_query($link, $updatesql);
    if (mysqli_num_rows($updateresult) > 0) {
      $updatecount = 0;
      while ($update = mysqli_fetch_assoc($updateresult)) {
        $prettydate = date_format(date_create($update['incident_update_timestamp']), 'M jS, Y') . ' at ' . date_format(date_create($update['incident_update_timestamp']), 'g:i A');
        $statusClass = $updatecount === 0 ? 'update-list-item__status--desktop first' : 'update-list-item__status--desktop default stormtrooper';
?>
                <li class="incident-update update-list-item">
                  <div class="update-list-item__inner-wrapper">
                    <div title="<?=$update['incident_status_description']?>" class="update-list-item__status <?=$statusClass?>"><?=$update['incident_status_description']?></div>
                    <div class="update-list-item__message">
                      <span class="opacity-75 inline updated-list-item__date"><p><?=$prettydate?></p></span>
                      <div class="inline prose-sm prose dark:prose-invert">
                        <?php renderUpdateContent($update['incident_update_description']); ?>
                      </div>
                    </div>
                  </div>
                </li>
<?php
        $updatecount++;
      }
    } else {
      echo '<li><p>No updates</p></li>';
    }
?>
              </ul>
            </div>
          </div>
<?php
  }
} else {
  echo '<p>No incidents to report</p>';
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

<?php
$welcome_message = getSetting($link, 'welcome_message');
if ($welcome_message != ''):
?>
<div class="modal fade" id="welcomemodal" tabindex="-1" aria-labelledby="welcomemodallabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="welcomemodallabel">Message</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
      </div>
      <div class="modal-body">
        <p><?=$welcome_message?></p>
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
