<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Writes to the incident-to-service relationship. Step 7 moves the incident writers
// into transactions that use this helper.

require_once __DIR__ . '/db.php';

/**
 * Records which services an incident affects, and at what severity, in
 * incident_service. Existing rows for the same incident and service are updated.
 * Ids that are not positive integers are skipped.
 *
 * Accepts a Db or a mysqli connection. The mysqli path uses MySQL/MariaDB syntax,
 * matching the other mysqli writers until Step 8.
 *
 * @param array<int|string, mixed> $serviceIds
 */
function saveIncidentServices($link, int $incidentId, array $serviceIds, string $severity): void
{
    $ids = array_unique(array_filter(array_map('intval', $serviceIds), fn (int $id): bool => $id > 0));
    foreach ($ids as $serviceId) {
        if ($link instanceof Db) {
            $link->upsert(
                'incident_service',
                ['incident_id' => $incidentId, 'service_id' => $serviceId, 'severity' => $severity],
                ['incident_id', 'service_id']
            );
            continue;
        }
        $stmt = $link->prepare(
            'INSERT INTO incident_service (incident_id, service_id, severity) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE severity = VALUES(severity)'
        );
        $stmt->bind_param('iis', $incidentId, $serviceId, $severity);
        $stmt->execute();
        $stmt->close();
    }
}
