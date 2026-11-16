<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Removes audit log entries older than the log_retention_days setting (default 90;
// 0 keeps everything). Run it from cron, for example once a day:
//
//   15 3 * * *  php /path/to/status-page/prune-log.php
//
// CLI only: it deletes data, so it has no web entry point.

if (php_sapi_name() !== 'cli') {
  http_response_code(403);
  die('This script must be run from the command line: php prune-log.php');
}

require_once __DIR__ . '/lib/bootstrap.php';

$removed = pruneAuditLog($db, time());
echo "Removed $removed audit log entries.\n";
exit(0);
