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
include('../../templates/_api.php');
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
      $userid = mysqli_fetch_assoc($result)['apikeys_user_id'];
      writeToLog($link, 'Matched authentication key for user', $userid);
      if (isset($_POST['type'])) {
        if ($_POST['type'] == 'incident') {
          writeToLog($link, 'Requested to add incident', $userid);
          if (isset($_POST['incident_description']) && isset($_POST['incident_update']) && isset($_POST['incident_status']) && isset($_POST['affected_services']) && isset($_POST['outage_severity'])) {
            writeToLog($link, 'Incident API call was correctly formed', $userid);
            $incident_description = (string)$_POST['incident_description'];
            $incident_update = (string)$_POST['incident_update'];
            $incident_status = (string)$_POST['incident_status'];
            $outage_severity = (string)$_POST['outage_severity'];
            $affectedservicesarray = array_map('intval', (array)$_POST['affected_services']);
            $affectedservicesstr = implode(',', $affectedservicesarray);
            writeToLog($link, 'Affected services are:', $userid);
            writeToLog($link, $affectedservicesstr, $userid);
            writeToLog($link, 'Executing an insert to the incident table now', $userid);
            $incstmt = $link->prepare('INSERT INTO incident (incident_description, incident_status_short, incident_describes_ids) VALUES (?, ?, ?)');
            $incstmt->bind_param('sss', $incident_description, $incident_status, $affectedservicesstr);
            if ($incstmt->execute()) {
              $incidentid = $link->insert_id;
              $incstmt->close();
              saveIncidentServices($link, (int)$incidentid, $affectedservicesarray, $outage_severity);
              $updstmt = $link->prepare('INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id) VALUES (?, ?, ?)');
              $updstmt->bind_param('ssi', $incident_status, $incident_update, $incidentid);
              if ($updstmt->execute()) {
                $updstmt->close();
                $svcstmt = $link->prepare('UPDATE services SET service_status_short = ? WHERE service_id = ?');
                $allservicesupdated = true;
                foreach ($affectedservicesarray as $serviceid) {
                  writeToLog($link, 'Updating ' . $serviceid . ' to ' . $outage_severity, $userid);
                  $svcstmt->bind_param('si', $outage_severity, $serviceid);
                  if (!$svcstmt->execute()) {
                    $allservicesupdated = false;
                    writeToLog($link, 'Error updating service outage severity level', $userid, 'FERR');
                    break;
                  }
                  writeToLog($link, 'Updated services', $userid);
                }
                $svcstmt->close();
                if ($allservicesupdated) {
                  $data = ['incident_description' => $incident_description, 'incident_update' => $incident_update, 'incident_status' => $incident_status, 'affected_services' => $affectedservicesstr, 'outage_severity' => $outage_severity, 'incident_id' => $incidentid];
                } else {
                  http_response_code(500);
                  $data = ['responsecode' => 'Internal Error', 'message' => 'Error updating service outage severity level'];
                }
                echo json_encode($data);
              } else {
                writeToLog($link, 'Error updating incident update message', $userid, 'FERR');
                writeToLog($link, $link->error, $userid, 'FERR');
                http_response_code(500);
                $data = ['responsecode' => 'Internal Error', 'message' => 'Error updating incident update message'];
                echo json_encode($data);
              }
            } else {
              $incstmt->close();
              writeToLog($link, 'Error while creating the incident', $userid, 'FERR');
              writeToLog($link, $link->error, $userid, 'FERR');
              http_response_code(500);
              $data = ['responsecode' => 'Internal Error', 'message' => 'Error while creating the incident'];
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
