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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  http_response_code(405);
  die('Method Not Allowed');
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
  if (isset($_GET['authkey'])) {
    writeToLog($link, 'Matching authentication keys for service API via GET', -1);
    $sql = "SELECT apikeys_user_id, apikeys_authkey FROM apikeys WHERE apikeys_authkey = '" . mysqli_real_escape_string($link, $_GET['authkey']) . "'";
    $result = mysqli_query($link, $sql);
    if (mysqli_num_rows($result) == 1) {
      header('Content-Type: application/json; charset=utf-8');
      $userid = mysqli_fetch_assoc($result)['apikeys_user_id'];
      writeToLog($link, 'Matched authentication key for user', $userid);
      if (isset($_GET['service_id'])) {
        $sql = "SELECT service_id, service_name, service_description, service_status_short, service_link, service_status.service_status_description FROM services INNER JOIN service_status ON service_status.service_status_code = services.service_status_short WHERE service_id = " . mysqli_real_escape_string($link, $_GET['service_id']) . " LIMIT 1";
        $result = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($result);
        $data = ['service_id' => $row['service_id'], 'service_name' => $row['service_name'], 'service_description' => $row['service_description'], 'service_link' => $row['service_link'], 'service_status_short' => $row['service_status_short'], 'service_status' => $row['service_status_description']];
        echo json_encode($data);
      } else {
        $sql = "SELECT service_status_short, COUNT(*) FROM services GROUP BY service_status_short";
        $result = mysqli_query($link, $sql);
        $statusarray = array();
        if (mysqli_num_rows($result) > 0) {
          while ($servicecount = mysqli_fetch_assoc($result)) {
            $statusarray[$servicecount['service_status_short']] = $servicecount['COUNT(*)'];
          }
        }
        $data = [];
        if (array_key_exists('MAJ', $statusarray)) {
          $data = ['service_status_short' => 'MAJ', 'service_status_brief' => 'Major Outage', 'service_status_description' => 'Some systems are experiencing major outages.'];
        } elseif (array_key_exists('MIN', $statusarray)) {
          $data = ['service_status_short' => 'MIN', 'service_status_brief' => 'Minor Outage', 'service_status_description' => 'Some systems are experiencing minor outages.'];
        } elseif (array_key_exists('DEG', $statusarray)) {
          $data = ['service_status_short' => 'DEG', 'service_status_brief' => 'Degraded', 'service_status_description' => 'Some systems are experiencing degraded performance.'];
        } elseif (array_key_exists('PLA', $statusarray)) {
          $data = ['service_status_short' => 'PLA', 'service_status_brief' => 'Planned Maintenance', 'service_status_description' => 'Some systems are undergoing planned maintenance.'];
        } else {
          $data = ['service_status_short' => 'OPE', 'service_status_brief' => 'Operational', 'service_status_description' => 'All systems operational.'];
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
} else {
  http_response_code(405);
  die('Method Not Allowed');
}
?>
