<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Upgrades a 0.3.1 database to 0.3.2. Removes two columns that nothing in the code
// reads any more:
//
// 1. services.service_status_short. Service status is derived from incidents when it
//    is read (lib/status_model.php), so the stored copy is gone.
// 2. incident.incident_describes_ids. The membership is in incident_service, which
//    has been the source of truth since 0.3.0. The incident API rebuilds the
//    comma-separated field from incident_service for clients that still read it.
//
// The data in these columns is not copied anywhere. incident_service already holds
// the membership, and the status is derived. The change cannot be undone, so take a
// backup before upgrading. Every step checks whether it has already run, so
// re-running after an interrupted upgrade is safe.

return function (Db $db): void {
  if ($db->hasColumn('services', 'service_status_short')) {
    if ($db->driver() === 'mysql') {
      // MySQL does not drop a foreign key with its column, so drop the key first.
      $keys = $db->all(
        'SELECT DISTINCT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
        ['services', 'service_status_short']
      );
      foreach ($keys as $key) {
        $db->runScript('ALTER TABLE services DROP FOREIGN KEY `' . str_replace('`', '', (string)$key['CONSTRAINT_NAME']) . '`');
      }
    }
    // PostgreSQL drops the constraint and index with the column. MySQL drops the index with it.
    $db->runScript('ALTER TABLE services DROP COLUMN service_status_short');
  }

  if ($db->hasColumn('incident', 'incident_describes_ids')) {
    $db->runScript('ALTER TABLE incident DROP COLUMN incident_describes_ids');
  }
};
