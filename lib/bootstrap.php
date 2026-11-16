<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Loads site configuration and opens the database connection. Included by every
// page, API endpoint and CLI script that needs the database.
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/html.php';
require_once __DIR__ . '/status.php';
require_once __DIR__ . '/time.php';
require_once __DIR__ . '/uptime.php';
require_once __DIR__ . '/db_helpers.php';   // also loads audit.php

$configfile = __DIR__ . '/../templates/config.php';
if (!file_exists($configfile)) {
  throw new RuntimeException('templates/config.php is missing. Run the installer first.');
}
require_once $configfile;

// Installs made before DB_DRIVER existed are MySQL/MariaDB.
$dbdriver = defined('DB_DRIVER') ? DB_DRIVER : 'mysql';
$dbport = defined('DB_PORT') ? (int)DB_PORT : ($dbdriver === 'pgsql' ? 5432 : 3306);

$db = new Db($dbdriver, DB_SERVER, $dbport, DB_NAME, DB_USERNAME, DB_PASSWORD);

// Transitional: pages not yet moved to $db still use the mysqli handle $link.
// Remove this block once every caller uses $db (Step 8 of the plan).
if ($dbdriver === 'mysql' && !isset($link)) {
  $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, $dbport);
}

// Turns an unhandled exception into a response. Details go to the PHP error log,
// never to the page. A strict-audit failure gets a 503 that says the action was
// not performed. Anything else gets a generic 500.
set_exception_handler(function (Throwable $e): void {
  error_log('status-page: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

  if (PHP_SAPI === 'cli') {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
  }

  $isapi = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/');
  if ($e instanceof AuditException) {
    http_response_code(503);
    if ($isapi) {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['responsecode' => 'Service Unavailable', 'message' => 'Audit logging is unavailable, so the request was not processed.']);
    } else {
      echo '<h1>Action not performed</h1><p>The audit log could not be written, so this action was not completed. Contact the site administrator.</p>';
    }
    return;
  }

  http_response_code(500);
  if ($isapi) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['responsecode' => 'Internal Error', 'message' => 'An unexpected error occurred.']);
  } else {
    echo '<h1>Something went wrong</h1><p>An unexpected error occurred. Details have been written to the server log.</p>';
  }
});
