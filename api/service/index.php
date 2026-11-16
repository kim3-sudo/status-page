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
include('../../lib/api.php');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  die('Method Not Allowed');
}

$userid = apiAuthenticateRequest($db, 'service API');
if ($userid === null) {
  apiUnauthorized();
  exit;
}

header('Content-Type: application/json; charset=utf-8');
// Same model as the status page, so the API and the page cannot disagree.
$now = time();
$snapshot = buildStatusSnapshot(loadStatusInputs($db, $siteZone), $now, $now);

if (isset($_GET['service_id'])) {
  $serviceid = (int)$_GET['service_id'];
  if (!isset($snapshot['services'][$serviceid])) {
    http_response_code(404);
    echo json_encode(['responsecode' => 'Not Found', 'message' => 'Unknown service']);
  } else {
    $service = $snapshot['services'][$serviceid];
    $summary = serviceStatusSummary($service['status']);
    $data = ['service_id' => (string)$service['id'], 'service_name' => $service['name'], 'service_description' => $service['description'], 'service_link' => $service['link'], 'service_status_short' => $service['status'], 'service_status' => $summary['brief']];
    echo json_encode($data);
  }
} else {
  $worst = $snapshot['banner'];
  $summary = serviceStatusSummary($worst);
  $data = ['service_status_short' => $worst, 'service_status_brief' => $summary['brief'], 'service_status_description' => $summary['description']];
  echo json_encode($data);
}
