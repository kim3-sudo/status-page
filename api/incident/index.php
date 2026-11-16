<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/
?>
<?php
include('../../templates/_api.php');
require_once __DIR__ . '/../../lib/incidents.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $userid = apiAuthenticateRequest($db, 'incident API');
  if ($userid === null) {
    apiUnauthorized();
    exit;
  }

  header('Content-Type: application/json; charset=utf-8');
  if (isset($_GET['incident_id'])) {
    $incidentid = (int)$_GET['incident_id'];
    $rows = $db->all('SELECT incident_update_id, incident_update_timestamp, incident_update_status_short, incident_update_description FROM incident_update WHERE incident_update_incident_id = ?', [$incidentid]);
    if ($rows !== []) {
      $data = [];
      foreach ($rows as $row) {
        $data[] = ['incident_id' => $_GET['incident_id'], 'incident_update_id' => $row['incident_update_id'], 'incident_update_timestamp' => $row['incident_update_timestamp'], 'incident_update_status_short' => $row['incident_update_status_short'], 'incident_update_description' => $row['incident_update_description']];
      }
    } else {
      $data = ['responsecode' => 'Success', 'message' => 'No data'];
    }
    echo json_encode($data);
  } else {
    $rows = $db->all('SELECT incident_id, incident_date, incident_description, incident_status_short, incident_describes_ids FROM incident');
    if ($rows !== []) {
      $data = [];
      foreach ($rows as $row) {
        $data[] = ['incident_id' => $row['incident_id'], 'incident_date' => $row['incident_date'], 'incident_description' => $row['incident_description'], 'incident_status_short' => $row['incident_status_short'], 'incident_describes_ids' => $row['incident_describes_ids']];
      }
    } else {
      $data = ['responsecode' => 'Success', 'message' => 'No data'];
    }
    echo json_encode($data);
  }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $userid = apiAuthenticateRequest($db, 'incident API');
  if ($userid === null) {
    apiUnauthorized();
    exit;
  }

  header('Content-Type: application/json; charset=utf-8');
  if (!isset($_POST['type'])) {
    writeToLog($db, 'Bad request (missing type) when POSTing to /api/incident endpoint', $userid);
    http_response_code(400);
    echo json_encode(['responsecode' => 'Bad Request', 'message' => 'Missing type']);
  } elseif ($_POST['type'] !== 'incident') {
    writeToLog($db, 'Bad request (wrong type) when POSTing to /api/incident endpoint', $userid);
    http_response_code(400);
    echo json_encode(['responsecode' => 'Bad Request', 'message' => 'Wrong or unexpected type']);
  } else {
    try {
      $incidentid = createIncident($db, [
        'title' => $_POST['incident_description'] ?? '',
        'status' => $_POST['incident_status'] ?? '',
        'update' => $_POST['incident_update'] ?? '',
        'severity' => $_POST['outage_severity'] ?? '',
        'services' => $_POST['affected_services'] ?? [],
      ], $siteZone, $userid);
      $data = ['incident_id' => $incidentid, 'incident_description' => $_POST['incident_description'], 'incident_status' => $_POST['incident_status'], 'outage_severity' => $_POST['outage_severity'], 'affected_services' => implode(',', array_map('intval', (array)$_POST['affected_services']))];
      echo json_encode($data);
    } catch (IncidentValidationException $e) {
      http_response_code(400);
      echo json_encode(['responsecode' => 'Bad Request', 'message' => $e->getMessage()]);
    }
  }
} else {
  http_response_code(405);
  die('Method Not Allowed');
}
