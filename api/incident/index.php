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
include('../../templates/_api.php');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (isset($_GET['authkey'])) {
    writeToLog($link, 'Matching authentication keys for incident API via GET', -1);
    $sql = "SELECT apikeys_user_id, apikeys_authkey FROM apikeys WHERE apikeys_authkey = '" . mysqli_real_escape_string($link, $_GET['authkey']) . "'";
    $result = mysqli_query($link, $sql);
    if (mysqli_num_rows($result) == 1) {
      header('Content-Type: application/json; charset=utf-8');
      $userid = mysqli_fetch_assoc($result)['apikeys_user_id'];
      writeToLog($link, 'Matched authentication key for user', $userid);
      if (isset($_GET['incident_id'])) {
        $sql = "SELECT incident_update_id, incident_update_timestamp, incident_update_status_short, incident_update_description FROM incident_update WHERE incident_update_incident_id = " . mysqli_real_escape_string($link, $_GET['incident_id']);
        $result = mysqli_query($link, $sql);
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
      writeToLog($link, $_GET['authkey'], -1);
      echo('Unauthorized');
    } else {
      http_response_code(401);
      writeToLog($link, 'No matching authentication key', -1);
      writeToLog($link, $_GET['authkey'], -1);
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
    $sql = "SELECT apikeys_user_id, apikeys_authkey FROM apikeys WHERE apikeys_authkey = '" . mysqli_real_escape_string($link, $_POST['authkey']) . "'";
    $result = mysqli_query($link, $sql);
    if (mysqli_num_rows($result) == 1) {
      header('Content-Type: application/json; charset=utf-8');
      $userid = mysqli_fetch_assoc($result)['apikeys_user_id'];
      writeToLog($link, 'Matched authentication key for user', $userid);
      if (isset($_POST['type'])) {
        if ($_POST['type'] == 'incident') {
          writeToLog($link, 'Requested to add incident', $userid);
          if (isset($_POST['incident_description']) && isset($_POST['incident_update'] && isset($_POST['incident_status'] && isset($_POST['affected_services'] && isset($_POST['outage_severity'])) {
            writeToLog($link, 'Incident API call was correctly formed', $userid);
            $incident_description = mysqli_real_escape_string($link, $_POST['incident_description']);
            $incident_update = mysqli_real_escape_string($link, $_POST['incident_update']);
            $incident_status = mysqli_real_escape_string($link, $_POST['incident_status']);
            $outage_severity = mysqli_real_escape_string($link, $_POST['outage_severity']);
            $affectedservicesarray = $_POST['affected_services'];
            $affectedservicesstr = implode(',', $affectedservicesarray);
            writeToLog($link, 'Affected services are:', $_SESSION['id']);
            writeToLog($link, $affectedservicesstr, $_SESSION['id']);
            $sql = "INSERT INTO incident (incident_description, incident_status_short, incident_describes_ids) VALUES ('" . $incident_description . "', '" . $incident_status . "', '" . $affected_services . "')";
            writeToLog($link, 'Executing an insert to the incident table now', $userid);
            if ($link->query($sql) === TRUE) {
              $incidentid = $link->insert_id;
              $subsql = "INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id) VALUES ('" . $incident_status . "', '" . $incident_update . "', '" . $incidentid . "')";
              if ($link->query($subsql) === TRUE) {
                foreach ($affectedservicesarray as &$serviceid) {
                  writeToLog($link, 'Updating ' . $serviceid . ' to ' . $outageseverity, $userid);
                  $subsubsql = "UPDATE services SET service_status_short = '" . $outage_severity . "' WHERE service_id = " . $serviceid;
                  if ($link->query($subsubsql) === TRUE) {
                    writeToLog($link, 'Updated services', $userid);
                    $data = ['incident_description' => $incident_description, 'incident_update' => $incident_update, 'incident_status' => $incident_status, 'affected_services' => $affected_services, 'outage_severity' => $outage_severity, 'incident_id' => $incidentid];
                    echo json_encode($data);
                  } else {
                    writeToLog($link, 'Error updating service outage severity level', $userid, 'FERR');
                    writeToLog($link, $subsubsql, $userid, 'FERR');
                    http_response_code(500);
                    $data = ['responsecode' => 'Internal Error', 'message' => 'Error updating service outage severity level'];
                    echo json_encode($data);
                  }
                }
              } else {
                writeToLog($link, 'Error updating incident update message', $userid, 'FERR');
                writeToLog($link, $subsql, $_SESSION['id'], 'FERR');
                http_response_code(500);
                $data = ['responsecode' => 'Internal Error', 'message' => 'Error updating incident update message'];
                echo json_encode($data);
              }
            } else {
              writeToLog($link, 'Error while creating the incident', $userid, 'FERR');
              writeToLog($link, $sql, $userid, 'FERR');
              http_response_code(500);
              data = ['responsecode' => 'Internal Error', 'message' => 'Error while creating the incident'];
              echo json_encode($data);
            }
          } else {
            writeToLog($link, 'Incident API call not correctly formed', $userid);
            http_response_code(400);
            $data = ['responsecode' => 'Bad Request', 'message' => 'Missing incident information'];
            echo json_encode($data);
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
      writeToLog($link, $_POST['authkey'], -1);
      echo('Unauthorized');
    } else {
      http_response_code(401);
      writeToLog($link, 'No matching authentication key', -1);
      writeToLog($link, $_POST['authkey'], -1);
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
