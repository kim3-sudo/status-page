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
  if (isset($_GET['authkey'])) {
    writeToLog($link, 'Matching authentication keys for incident API via GET', -1);
    $result = findApiKeyRows($link, $_GET['authkey']);
    if (mysqli_num_rows($result) == 1) {
      header('Content-Type: application/json; charset=utf-8');
      $userid = mysqli_fetch_assoc($result)['apikeys_user_id'];
      writeToLog($link, 'Matched authentication key for user', $userid);
      if (isset($_GET['incident_id'])) {
        $incidentid = (int)$_GET['incident_id'];
        $stmt = $link->prepare('SELECT incident_update_id, incident_update_timestamp, incident_update_status_short, incident_update_description FROM incident_update WHERE incident_update_incident_id = ?');
        $stmt->bind_param('i', $incidentid);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $data = array();
        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            array_push($data, ['incident_id' => $_GET['incident_id'], 'incident_update_id' => $row['incident_update_id'], 'incident_update_timestamp' => $row['incident_update_timestamp'], 'incident_update_status_short' => $row['incident_update_status_short'], 'incident_update_description' => $row['incident_update_description']]);
          }
        } else {
          $data = ['responsecode' => 'Success', 'message' => 'No data'];
        }
        echo json_encode($data);
      } else {
        $sql = "SELECT incident_id, incident_date, incident_description, incident_status_short, incident_describes_ids FROM incident";
        $result = mysqli_query($link, $sql);
        $data = array();
        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            array_push($data, ['incident_id' => $row['incident_id'], 'incident_date' => $row['incident_date'], 'incident_description' => $row['incident_description'], 'incident_status_short' => $row['incident_status_short'], 'incident_describes_ids' => $row['incident_describes_ids']]);
          }
        } else {
          $data = ['responsecode' => 'Success', 'message' => 'No data'];
        }
        echo json_encode($data);
      }
    } elseif (mysqli_num_rows($result) > 1) {
      http_response_code(401);
      writeToLog($link, 'Ambiguous authentication keys', -1);
      writeToLog($link, 'Rejected ' . apiKeyFingerprint($_GET['authkey']), -1);
      echo('Unauthorized');
    } else {
      http_response_code(401);
      writeToLog($link, 'No matching authentication key', -1);
      writeToLog($link, 'Rejected ' . apiKeyFingerprint($_GET['authkey']), -1);
      echo('Unauthorized');
    }
  } else {
    http_response_code(401);
    writeToLog($link, 'No authentication key provided', -1);
    echo('Unauthorized');
  }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['authkey'])) {
    writeToLog($link, 'Matching authentication keys for incident API via POST', -1);
    $result = findApiKeyRows($link, $_POST['authkey']);
    if (mysqli_num_rows($result) == 1) {
      header('Content-Type: application/json; charset=utf-8');
      $userid = (int)mysqli_fetch_assoc($result)['apikeys_user_id'];
      writeToLog($link, 'Matched authentication key for user', $userid);
      if (isset($_POST['type'])) {
        if ($_POST['type'] == 'incident') {
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
        } else {
          writeToLog($link, 'Bad request (wrong type) when POSTing to /api/incident endpoint', $userid);
          http_response_code(400);
          $data = ['responsecode' => 'Bad Request', 'message' => 'Wrong or unexpected type'];
          echo json_encode($data);
        }
      } else {
        writeToLog($link, 'Bad request (missing type) when POSTing to /api/incident endpoint', $userid);
        http_response_code(400);
        $data = ['responsecode' => 'Bad Request', 'message' => 'Missing type'];
        echo json_encode($data);
      }
    } elseif (mysqli_num_rows($result) > 1) {
      http_response_code(401);
      writeToLog($link, 'Ambiguous authentication keys', -1);
      writeToLog($link, 'Rejected ' . apiKeyFingerprint($_POST['authkey']), -1);
      echo('Unauthorized');
    } else {
      http_response_code(401);
      writeToLog($link, 'No matching authentication key', -1);
      writeToLog($link, 'Rejected ' . apiKeyFingerprint($_POST['authkey']), -1);
      echo('Unauthorized');
    }
  } else {
    http_response_code(401);
    writeToLog($link, 'No authentication key provided', -1);
    echo('Unauthorized');
  }
} else {
  http_response_code(405);
  die('Method Not Allowed');
}
?>
