<?php
session_start();
if (!file_exists('templates/config.php')) {
  header('Location: /install');
}
include('templates/_header.php');
// Update planned maintenance, triggered on page load
// This IS A REALLY SLOPPY WAY OF DOING THIS, but I'm too lazy to write a proper trigger
$sql = "SELECT incident_update_incident_id FROM incident_update WHERE incident_update_timestamp < CURRENT_DATE() AND incident_update_status_short = 'PLA'";
$result = mysqli_query($link, $sql);
if (mysqli_num_rows($result) > 0) {
  while ($xincidentupdate = mysqli_fetch_assoc($result)) {
    $sql = "SELECT incident_describes_ids FROM incident WHERE incident_id = " . $xincidentupdate['incident_update_incident_id'];
    $result = mysqli_query($link, $sql);
    if (mysqli_num_rows($result) > 0) {
      while ($xincident = mysqli_fetch_assoc($result)) {
        $xaffectedservices = explode(',', $xincident['incident_describes_ids']);
        foreach ($xaffectedservices as &$value) {
          $sql = "UPDATE services SET service_status_short = 'OPE' WHERE service_id = " . $value . " AND service_status_short = 'PLA'";
          if ($link->query($sql) === TRUE) {
            echo '<script>console.log("Changed status of "' . $value . ' to OPE from PLA")</script>';
          } else {
            echo '<script>console.log("Failed to change status of "' . $value . ' to OPE from PLA")</script>';
          }
        }
      }
    }
  }
}
?>
<div class="bg-dark">
  <div class="container-sm">
    <div class="row">
      <div class="col bg-secondary rounded-4 m-5">
        <div class="text-light p-4 text-center">
<?php
$overallsql = "SELECT service_status_short, COUNT(*) FROM services GROUP BY service_status_short";
$result = mysqli_query($link, $overallsql);
$statusarray = array();
if (mysqli_num_rows($result) > 0) {
  while ($servicecount = mysqli_fetch_assoc($result)) {
    $statusarray[$servicecount['service_status_short']] = $servicecount['COUNT(*)'];
  }
}
if (array_key_exists('MAJ', $statusarray)) {
  echo '<h2><i class="text-danger fa-solid fa-info-circle"></i>&nbsp;Some systems experiencing major outages</h2>';
} else if (array_key_exists('MIN', $statusarray)) {
  echo '<h2><i class="text-warning fa-solid fa-info-circle"></i>&nbsp;Some systems experiencing minor outages</h2>';
} else if (array_key_exists('DEG', $statusarray)) {
  echo '<h2><i class="text-warning fa-solid fa-info-circle"></i>&nbsp;Some systems experiencing degraded performance</h2>';
} else if (array_key_exists('PLA', $statusarray)) {
  // Need a subquery here to detect if the planned maintenance window is now
  $sql = "SELECT incident_update_timestamp FROM incident_update WHERE incident_update_status_short = 'PLA' AND incident_update_timestamp < CURRENT_DATE()";
  $result = mysqli_query($link, $sql);
  $inmaintenancewindow = array();
  while ($maintenancewindow = mysqli_fetch_assoc($result)) {
    $sql = "SELECT incident_update_timestamp FROM incident_update WHERE incident_update_incident_id = " . $maintenancewindow['incident_update_incident_id'] . " AND incident_update_status_short = 'PLA' OR incident_update_status_short = 'RES' ORDER BY incident_update_timestamp ASC";
    $result = mysqli_query($link, $sql);
    $maintenancetimearray = array();
    while ($maintenancetimes = mysqli_fetch_assoc($result)) {
      array_push($maintenancetimearray, strtotime($maintenancetimes['incident_update_timestamp']));
    }
    if ($maintenancetimearray[0] < time() && time() < $maintenancetimearray[1]) {
      array_push($inmaintenancewindow, true);
    } else {
      array_push($inmaintenancewindow, false);
    }
  }
  if (in_array(true, $inmaintenancewindow)) {
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
$row = mysqli_fetch_assoc(mysqli_query($link, "SELECT setting_value FROM settings WHERE setting_key = 'about_this_site'"));
if ($row['setting_value'] != '') {
?>
<div class="container pt-5">
  <div class="row">
    <div class="col">
      <h2 class="text-muted pb-2">About This Site</h2>
      <p class="text-muted"><?=$row['setting_value']?></p>
    </div>
  </div>
</div>
<?php
}
?>
<div class="">
  <div class="container-sm">
    <div class="row">
      <div class="col">
        <div class="accordion py-4 m-2" id="statusparent">
<?php
$sql = 'SELECT servicegroup_id, servicegroup_name FROM servicegroups ORDER BY servicegroup_name ASC';
$result = mysqli_query($link, $sql);
if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $cleangroupname = strtolower(preg_replace('/\W/', '', $row['servicegroup_name']));
?>
          <div class="accordion-item">
            <h2 class="accordion-header" id="<?=$cleangroupname?>Heading">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?=$cleangroupname?>panel" aria-expanded="false" aria-controls="<?=$cleangroupname?>panel">
                <span id="<?=$cleangroupname?>badge" class="badge text-bg-success">Operational</span>&nbsp;<?=$row['servicegroup_name']?>
              </button>
            </h2>
            <div id="<?=$cleangroupname?>panel" class="accordion-collapse collapse" aria-labelledby="<?=$cleangroupname?>Heading">
              <div class="accordion-body">
<?php
$servicesql = 'SELECT service_id, service_name, service_description, service_status_short, service_status.service_status_description FROM services INNER JOIN servicegroups ON services.servicegroup_id = servicegroups.servicegroup_id INNER JOIN service_status ON service_status.service_status_code = services.service_status_short WHERE servicegroups.servicegroup_id = ' . $row['servicegroup_id'];
$serviceresult = mysqli_query($link, $servicesql);
if (mysqli_num_rows($serviceresult) > 0) {
  while ($service = mysqli_fetch_assoc($serviceresult)) {
?>
                <div class="container px-4 py-2">
                  <div class="row">
                    <div class="col-8">
                      <h6>
<?php
if ($service['service_status_short'] == 'OPE') {
?>
<span class="badge text-bg-success" data-bs-toggle="tooltip" data-bs-title="The service is working normally and as expected."><i class="fa-solid fa-check-circle"></i>&nbsp;Operational</span>
<?php
} else if ($service['service_status_short'] == 'DEG') {
?>
<span class="badge text-bg-warning" data-bs-toggle="tooltip" data-bs-title="The service may be experiencing degraded performance."><i class="fa-solid fa-circle-exclamation"></i>&nbsp;Degraded Performance</span>
<script>
document.getElementById("<?=$cleangroupname?>badge").classList.remove("text-bg-success");
document.getElementById("<?=$cleangroupname?>badge").classList.add("text-bg-warning");
document.getElementById("<?=$cleangroupname?>badge").innerHTML = "Degraded";
</script>
<?php
} else if ($service['service_status_short'] == 'PLA') {
  // Need a subquery here to detect if the maintenance window is now
?>
<span class="badge text-bg-info" data-bs-toggle="tooltip" data-bs-title="The system may be under planned maintenance right now. If it is not currently available, it will be soon."><i class="fa-solid fa-circle-exclamation"></i>&nbsp;Planned Maintenance</span>
<script>
document.getElementById("<?=$cleangroupname?>badge").classList.remove("text-bg-success");
document.getElementById("<?=$cleangroupname?>badge").classList.add("text-bg-info");
document.getElementById("<?=$cleangroupname?>badge").innerHTML = "Planned Maintenance";
</script>
<?php
} else if ($service['service_status_short'] == 'MIN') {
?>
<span class="badge text-bg-warning" data-bs-toggle="tooltip" data-bs-title="The system may be experiencing a minor outage right now."><i class="text-warning fa-solid fa-circle-exclamation"></i>&nbsp;Minor Outage</span>
<script>
document.getElementById("<?=$cleangroupname?>badge").classList.remove("text-bg-success");
document.getElementById("<?=$cleangroupname?>badge").classList.add("text-bg-warning");
document.getElementById("<?=$cleangroupname?>badge").innerHTML = "Minor Outage";
</script>
<?php
} else if ($service['service_status_short'] == 'MAJ') {
?>
<span class="badge text-bg-danger" data-bs-toggle="tooltip" data-bs-title="The system may be experiencing a major outage right now."><i class="fa-solid fa-triangle-exclamation"></i>&nbsp;Major Outage</span>
<script>
document.getElementById("<?=$cleangroupname?>badge").classList.remove("text-bg-success");
document.getElementById("<?=$cleangroupname?>badge").classList.add("text-bg-danger");
document.getElementById("<?=$cleangroupname?>badge").innerHTML = "Major Outage";
</script>
<?php
}
?>
                        &nbsp;<?=$service['service_name']?>
<?php
if ($service['service_description'] != '') {
?>
&nbsp;<i class="text-muted fa-solid fa-question-circle" data-bs-toggle="tooltip" data-bs-title="<?=$service['service_description']?>"></i>
<?php
}
?>
                      </h6>
                    </div>
                    <div class="col-4 text-end">
                      <h6 class="text-muted">
<?php
$uptimesql = "SELECT incident_update_timestamp, incident_update_status_short, incident.incident_describes_ids FROM incident_update INNER JOIN incident ON incident_update.incident_update_incident_id = incident.incident_id WHERE incident_update_timestamp > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 90 DAY) AND instr(concat(',', incident.incident_describes_ids, ','), '," . $service['service_id'] . ",') <> 0"; //incident.incident_describes_ids = '" . $service['service_id'] . "'
$uptimeresult = mysqli_query($link, $uptimesql);
if (mysqli_num_rows($uptimeresult) == 0) {
  echo '100.00';
} else {
  $uptimearray = array();
  while ($uptimerow = mysqli_fetch_assoc($uptimeresult)) {
    $uptimearray[$uptimerow['incident_update_status_short']] = $uptimerow['incident_update_timestamp'];
  }
  if (array_key_exists('RES', $uptimearray)) {
    if (array_key_exists('PLA', $uptimearray)) {
      $started = strtotime($uptimearray['PLA']);
    } else {
      $started = strtotime($uptimearray['IDE']);
    }
    echo '<script>console.log("Started on ' . $started . '")</script>';
    $resolved = strtotime($uptimearray['RES']);
    echo '<script>console.log("Resolved on ' . $resolved . '")</script>';
    $diff_seconds = abs($resolved - $started);
    $percentage = 100 - round(($diff_seconds/7776000) * 100, 2);
    echo $percentage;
  } else {
    $started = strtotime($uptimearray['IDE']);
    echo '<script>console.log("Started on ' . $started . '")</script>';
    $now = idate('U');
    echo '<script>console.log("Not resolved, current time is ' . $now . '")</script>';
    $diff_seconds = abs($now - $started);
    $percentage = 100 - round(($diff_seconds/7776000) * 100, 2);
    echo $percentage;
  }
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
          <p><small class="text-muted"><em>Uptime is calculated as the percentage of time services were fully operational over the last 90 days.</em></small></p>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col">
      <h4 class="text-uppercase text-muted">Messages</h4>
<?php
$incidentsql = 'SELECT incident_id, incident_date, incident_description, incident_status_short, incident_status.incident_status_description FROM incident INNER JOIN incident_status ON incident_status.incident_status_code = incident.incident_status_short ORDER BY incident_date DESC LIMIT 5';
$incidentresult = mysqli_query($link, $incidentsql);
if (mysqli_num_rows($incidentresult) > 0) {
  while ($incident = mysqli_fetch_assoc($incidentresult)) {
    $prettydate = date_format(date_create($incident['incident_date']), 'M jS, Y');
?>
        <div class="relative incidents-monthly__item pt-2 pb-3 line">
          <p class="incidents-monthly__item__month text-black dark:text-white group-hover:opacity-100 ">
            <span><?=$prettydate?></span>
          </p>
          <div class="incident-details incident-item">
            <div class="flex items-start justify-between">
                <div class="incident-details__header">
                  <span>
<?php
if ($incident['incident_status_short'] != 'RES') {
  echo '<i class="fa-solid fa-check-circle"></i>';
} else {
  echo '<i class="fa-solid fa-exclamation-circle"></i>';
}
?>
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
    if ($updatecount == 0) {
?>
                <li class="incident-update update-list-item">
                  <div class="update-list-item__inner-wrapper">
                    <div title="<?=$update['incident_status_description']?>" class="update-list-item__status update-list-item__status--desktop first"><?=$update['incident_status_description']?></div>
                    <div class="update-list-item__message">
                      <span class="opacity-75 inline updated-list-item__date">
                        <p><?=$prettydate?></p>
                      </span>
                      <div class="inline prose-sm prose dark:prose-invert">
                        <p><?=$update['incident_update_description']?></p>
                      </div>
                    </div>
                  </div>
                </li>
<?php
    } else {
?>
                <li class="incident-update update-list-item">
                  <div class="update-list-item__inner-wrapper">
                    <div title="<?=$update['incident_status_description']?>" class="update-list-item__status update-list-item__status--desktop default stormtrooper"><?=$update['incident_status_description']?></div>
                    <div class="update-list-item__message">
                      <span class="opacity-75 inline updated-list-item__date">
                        <p><?=$prettydate?></p>
                      </span>
                      <div class="inline prose-sm prose dark:prose-invert">
                        <p><?=$update['incident_update_description']?></p>
                      </div>
                    </div>
                  </div>
                </li>
<?php
    }
    $updatecount++;
  }
} else {
?>
<li><p>No updates</p></li>
<?php
}
?>
              </ul>
          </div>
        </div>
<?php
  }
} else {
?>
<p>No incidents to report</p>
<?php
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
include('templates/_footer.php');
?>
