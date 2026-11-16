<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Upgrades a 0.2.x database to 0.3.0.
//
// 1. Adds incident_update.incident_update_utc. Existing rows keep the default
//    (false), which means they are site-local. New rows will be written as UTC
//    with the flag set (lib/time.php).
// 2. Creates incident_service, the one-row-per-pair table of which services an
//    incident affects and how severely. It replaces the comma-separated
//    incident.incident_describes_ids as the source of truth.
// 3. Backfills incident_service from the legacy CSV column. Resolved incidents
//    get severity MIN. Open incidents take the service's current severity when
//    that is a non-operational code. Dangling service ids are skipped.
// 4. Adds the (incident, timestamp) index used by the uptime queries.
// 5. On MySQL/MariaDB, converts tables from latin1 to utf8mb4. On PostgreSQL,
//    checks that the database uses UTF8.
// 6. Seeds the audit_policy setting as strict, if it is missing.
//
// Every step checks whether it has already run, so re-running after an
// interrupted upgrade is safe.

return function (Db $db): void {
  $mysql = $db->driver() === 'mysql';

  // 1. UTC flag. Existing rows keep false, which means site-local.
  if (!$db->hasColumn('incident_update', 'incident_update_utc')) {
    $db->runScript(
      'ALTER TABLE incident_update ADD COLUMN incident_update_utc BOOLEAN NOT NULL DEFAULT FALSE'
    );
  }

  // 2. Join table.
  if ($mysql) {
    $db->runScript(
      'CREATE TABLE IF NOT EXISTS incident_service (
        incident_id INT NOT NULL,
        service_id INT NOT NULL,
        severity VARCHAR(3) NOT NULL DEFAULT \'MIN\',
        PRIMARY KEY (incident_id, service_id),
        KEY incident_service_service_id (service_id),
        FOREIGN KEY (incident_id) REFERENCES incident (incident_id),
        FOREIGN KEY (service_id) REFERENCES services (service_id),
        FOREIGN KEY (severity) REFERENCES service_status (service_status_code)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
  } else {
    $db->runScript(
      'CREATE TABLE IF NOT EXISTS incident_service (
        incident_id INTEGER NOT NULL REFERENCES incident (incident_id),
        service_id INTEGER NOT NULL REFERENCES services (service_id),
        severity VARCHAR(3) NOT NULL DEFAULT \'MIN\' REFERENCES service_status (service_status_code),
        PRIMARY KEY (incident_id, service_id)
      )'
    );
    if (!$db->hasIndex('incident_service', 'incident_service_service_id')) {
      $db->runScript('CREATE INDEX incident_service_service_id ON incident_service (service_id)');
    }
  }

  // 3. Backfill from the legacy CSV. Insert-only, so existing rows are never overwritten.
  $serviceStatus = [];
  foreach ($db->all('SELECT service_id, service_status_short FROM services') as $row) {
    $serviceStatus[(int)$row['service_id']] = $row['service_status_short'];
  }
  $liveSeverities = ['MAJ', 'MIN', 'DEG', 'PLA'];

  foreach ($db->all('SELECT incident_id, incident_describes_ids, incident_status_short FROM incident') as $incident) {
    $isOpen = $incident['incident_status_short'] !== 'RES';
    $ids = array_filter(
        array_map('trim', explode(',', (string)$incident['incident_describes_ids'])),
        fn (string $id): bool => ctype_digit($id)
    );

    foreach ($ids as $id) {
      $serviceId = (int)$id;
      if (!array_key_exists($serviceId, $serviceStatus)) {
        continue;
      }
      $current = $serviceStatus[$serviceId];
      $severity = ($isOpen && in_array($current, $liveSeverities, true)) ? $current : 'MIN';

      $db->upsert(
        'incident_service',
        ['incident_id' => (int)$incident['incident_id'], 'service_id' => $serviceId, 'severity' => $severity],
        ['incident_id', 'service_id'],
        false
      );
    }
  }

  // 4. Index for the uptime and maintenance queries.
  if (!$db->hasIndex('incident_update', 'incident_update_incident_time')) {
    $db->runScript(
      'CREATE INDEX incident_update_incident_time ON incident_update (incident_update_incident_id, incident_update_timestamp)'
    );
  }

  // 5. Character set.
  if ($mysql) {
    $tables = ['log', 'users', 'settings', 'pes', 'incident_status', 'service_status', 'servicegroups',
               'services', 'incident', 'incident_update', 'apikeys', 'incident_service'];
    $convert = [];
    foreach ($tables as $table) {
      $collation = (string)$db->value(
        'SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
        [$table]
      );
      if (!str_starts_with($collation, 'utf8mb4')) {
        $convert[] = $table;
      }
    }
    if ($convert !== []) {
      // Foreign keys must be switched off while the referencing columns change charset together.
      $statements = ['SET FOREIGN_KEY_CHECKS = 0'];
      foreach ($convert as $table) {
        $statements[] = "ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
      }
      $statements[] = 'SET FOREIGN_KEY_CHECKS = 1';
      $db->runScript(implode(";\n", $statements) . ';');
    }
  } else {
    $encoding = strtoupper((string)$db->value('SHOW server_encoding'));
    if ($encoding !== 'UTF8') {
      throw new RuntimeException("PostgreSQL database must use UTF8 encoding, found $encoding.");
    }
  }

  // 6. Audit policy default. Insert-only, so an admin's choice is kept.
  $db->upsert('settings', ['setting_key' => 'audit_policy', 'setting_value' => 'strict'], ['setting_key'], false);
};
