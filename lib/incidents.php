<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// The incident write path, used by the admin pages and the incident API.
//
// Each public function:
//   1. validates its input, and throws IncidentValidationException on bad input,
//   2. records the request in the audit log, which strict auditing can refuse
//      before anything is written,
//   3. writes everything in one transaction. The outcome is recorded inside it, so
//      a failed audit write rolls the whole change back.
//
// Service status is not stored. It is derived when the page or API is read
// (lib/status_model.php).
//
// Times are entered in the site timezone and stored in UTC with
// incident_update_utc set (lib/time.php).
//
// Service membership and severity are written to incident_service.

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/db_helpers.php';
require_once __DIR__ . '/html.php';
require_once __DIR__ . '/sanitize.php';
require_once __DIR__ . '/status.php';
require_once __DIR__ . '/status_model.php';
require_once __DIR__ . '/time.php';

/** Bad input from an admin form or an API caller. The message is safe to show. */
final class IncidentValidationException extends InvalidArgumentException
{
}

/** Severities an outage can carry. PLA is only for planned maintenance; OPE means no impact. */
const INCIDENT_OUTAGE_SEVERITIES = ['MAJ', 'MIN', 'DEG'];
const INCIDENT_TITLE_MAX = 255;
const INCIDENT_UPDATE_MAX = 2000;

/** Returns the set of incident status codes from the database. @return array<string, true> */
function incidentStatusCodes(Db $db): array
{
    $codes = [];
    foreach ($db->all('SELECT incident_status_code FROM incident_status') as $row) {
        $codes[$row['incident_status_code']] = true;
    }
    return $codes;
}

/**
 * Checks a list of service ids: at least one, all positive, all present in the
 * services table. Returns them as unique ints.
 *
 * @param array<int|string, mixed> $ids
 * @return list<int>
 */
function requireServiceIds(Db $db, array $ids): array
{
    $clean = array_values(array_unique(array_map('intval', $ids)));
    $clean = array_values(array_filter($clean, fn (int $id): bool => $id > 0));
    if ($clean === []) {
        throw new IncidentValidationException('Choose at least one affected service.');
    }
    foreach ($clean as $id) {
        if ($db->value('SELECT COUNT(*) FROM services WHERE service_id = ?', [$id]) == 0) {
            throw new IncidentValidationException("Service $id does not exist.");
        }
    }
    return $clean;
}

/** Checks a title: required and within the column limit. */
function requireIncidentTitle(string $title): string
{
    $title = trim($title);
    if ($title === '' || mb_strlen($title) > INCIDENT_TITLE_MAX) {
        throw new IncidentValidationException('Enter a title of up to ' . INCIDENT_TITLE_MAX . ' characters.');
    }
    return $title;
}

/** Sanitizes an update body and checks it is non-empty and within the column limit. */
function requireUpdateBody(string $raw): string
{
    $body = sanitizeUpdateBody($raw);
    // Markup alone (such as "<p> </p>") is not a message.
    $visible = preg_replace('/[\s\x{00a0}]+/u', '', html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($body === '' || $visible === '') {
        throw new IncidentValidationException('Enter an update message.');
    }
    if (mb_strlen($body) > INCIDENT_UPDATE_MAX) {
        throw new IncidentValidationException('The update message is too long (limit ' . INCIDENT_UPDATE_MAX . ' characters).');
    }
    return $body;
}

/** Checks an incident status code against the database. */
function requireIncidentStatus(Db $db, string $code): string
{
    if (!isset(incidentStatusCodes($db)[$code])) {
        throw new IncidentValidationException('Unknown incident status.');
    }
    return $code;
}

/** Checks an outage severity. Returns it unchanged. */
function requireOutageSeverity(string $code): string
{
    if (!in_array($code, INCIDENT_OUTAGE_SEVERITIES, true)) {
        throw new IncidentValidationException('Outage severity must be major (MAJ), minor (MIN) or degraded (DEG).');
    }
    return $code;
}

/**
 * Converts an optional site-local datetime to UTC for storage. Null or empty means
 * "not given" and returns null.
 */
function incidentTimeToUtc(?string $value, DateTimeZone $zone, string $field): ?string
{
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    try {
        return siteLocalToUtcString($value, $zone);
    } catch (Exception $e) {
        throw new IncidentValidationException("$field is not a valid date and time.");
    }
}

/** The site-local calendar date of a UTC timestamp. */
function localDateOf(string $utc, DateTimeZone $zone): string
{
    return (new DateTimeImmutable($utc, new DateTimeZone('UTC')))->setTimezone($zone)->format('Y-m-d');
}

/**
 * Creates an incident with its first update and its affected services.
 *
 * $input keys: title, status, update, severity, services (ids), start (site-local, optional).
 * Returns the new incident id.
 *
 * @param array<string, mixed> $input
 */
function createIncident(Db $db, array $input, DateTimeZone $zone, int $actorId, ?int $now = null): int
{
    $now ??= time();
    $title = requireIncidentTitle((string)($input['title'] ?? ''));
    $status = requireIncidentStatus($db, (string)($input['status'] ?? ''));
    $body = requireUpdateBody((string)($input['update'] ?? ''));
    $severity = requireOutageSeverity((string)($input['severity'] ?? ''));
    $serviceIds = requireServiceIds($db, (array)($input['services'] ?? []));
    $startUtc = incidentTimeToUtc($input['start'] ?? null, $zone, 'Start time') ?? gmdate('Y-m-d H:i:s', $now);

    writeToLog($db, 'Requested incident: ' . $title, $actorId);

    return $db->transaction(function (Db $db) use ($title, $status, $body, $severity, $serviceIds, $startUtc, $zone, $now, $actorId): int {
        $incidentId = $db->insert('incident', [
            'incident_date' => localDateOf($startUtc, $zone),
            'incident_description' => $title,
            'incident_status_short' => $status,
            ], 'incident_id');

        $db->insert('incident_update', [
            'incident_update_incident_id' => $incidentId,
            'incident_update_status_short' => $status,
            'incident_update_description' => $body,
            'incident_update_timestamp' => $startUtc,
            'incident_update_utc' => 1,
        ], 'incident_update_id');

        foreach ($serviceIds as $serviceId) {
            $db->upsert('incident_service', [
                'incident_id' => $incidentId,
                'service_id' => $serviceId,
                'severity' => $severity,
            ], ['incident_id', 'service_id']);
        }
        auditWrite($db, "Created incident $incidentId: $title", $actorId, 'INFO');
        return $incidentId;
    });
}

/**
 * Adds an update to an existing incident. The incident's current status changes
 * only if this update is the newest one.
 *
 * $input keys: status, update, timestamp (site-local, optional).
 *
 * @param array<string, mixed> $input
 */
function addIncidentUpdate(Db $db, int $incidentId, array $input, DateTimeZone $zone, int $actorId, ?int $now = null): void
{
    $now ??= time();
    if ($db->value('SELECT COUNT(*) FROM incident WHERE incident_id = ?', [$incidentId]) == 0) {
        throw new IncidentValidationException('That incident does not exist.');
    }
    $status = requireIncidentStatus($db, (string)($input['status'] ?? ''));
    $body = requireUpdateBody((string)($input['update'] ?? ''));
    $timestampUtc = incidentTimeToUtc($input['timestamp'] ?? null, $zone, 'Update time') ?? gmdate('Y-m-d H:i:s', $now);

    writeToLog($db, "Requested update on incident $incidentId", $actorId);

    $db->transaction(function (Db $db) use ($incidentId, $status, $body, $timestampUtc, $zone, $now, $actorId): void {
        $db->insert('incident_update', [
            'incident_update_incident_id' => $incidentId,
            'incident_update_status_short' => $status,
            'incident_update_description' => $body,
            'incident_update_timestamp' => $timestampUtc,
            'incident_update_utc' => 1,
        ], 'incident_update_id');

        // The incident takes this update's status only if it is the newest update.
        $thisEpoch = (new DateTimeImmutable($timestampUtc, new DateTimeZone('UTC')))->getTimestamp();
        $latestEpoch = max(loadUpdateEpochs($db, $incidentId, $zone));
        if ($thisEpoch >= $latestEpoch) {
            $db->execute('UPDATE incident SET incident_status_short = ? WHERE incident_id = ?', [$status, $incidentId]);
        }
        auditWrite($db, "Added update to incident $incidentId", $actorId, 'INFO');
    });
}

/**
 * Schedules planned maintenance: an incident with a start update, an end update,
 * and every affected service at severity PLA.
 *
 * $input keys: title, message, start, end (site-local), services (ids).
 * Returns the new incident id.
 *
 * @param array<string, mixed> $input
 */
function scheduleMaintenance(Db $db, array $input, DateTimeZone $zone, int $actorId, ?int $now = null): int
{
    $now ??= time();
    $title = requireIncidentTitle((string)($input['title'] ?? ''));
    $message = requireUpdateBody((string)($input['message'] ?? ''));
    $serviceIds = requireServiceIds($db, (array)($input['services'] ?? []));
    $startUtc = incidentTimeToUtc($input['start'] ?? null, $zone, 'Start time');
    $endUtc = incidentTimeToUtc($input['end'] ?? null, $zone, 'End time');
    if ($startUtc === null || $endUtc === null) {
        throw new IncidentValidationException('Enter both a start and an end time.');
    }
    if ($endUtc <= $startUtc) {
        throw new IncidentValidationException('The end time must be after the start time.');
    }
    // While the window has not ended, the incident is in planned maintenance; afterwards it is resolved.
    $status = strtotime($endUtc . ' UTC') > $now ? 'PLA' : 'RES';

    writeToLog($db, 'Requested maintenance: ' . $title, $actorId);

    return $db->transaction(function (Db $db) use ($title, $message, $serviceIds, $startUtc, $endUtc, $status, $zone, $now, $actorId): int {
        $incidentId = $db->insert('incident', [
            'incident_date' => localDateOf($startUtc, $zone),
            'incident_description' => $title,
            'incident_status_short' => $status,
            ], 'incident_id');

        $db->insert('incident_update', [
            'incident_update_incident_id' => $incidentId,
            'incident_update_status_short' => 'PLA',
            'incident_update_description' => 'Planned maintenance begins',
            'incident_update_timestamp' => $startUtc,
            'incident_update_is_planned_maint' => 'Y',
            'incident_update_utc' => 1,
        ], 'incident_update_id');

        $db->insert('incident_update', [
            'incident_update_incident_id' => $incidentId,
            'incident_update_status_short' => 'RES',
            'incident_update_description' => $message,
            'incident_update_timestamp' => $endUtc,
            'incident_update_is_planned_maint' => 'Y',
            'incident_update_utc' => 1,
        ], 'incident_update_id');

        foreach ($serviceIds as $serviceId) {
            $db->upsert('incident_service', [
                'incident_id' => $incidentId,
                'service_id' => $serviceId,
                'severity' => 'PLA',
            ], ['incident_id', 'service_id']);
        }
        auditWrite($db, "Scheduled maintenance $incidentId: $title", $actorId, 'INFO');
        return $incidentId;
    });
}

/**
 * Epoch timestamps of every update on one incident, converted per row
 * (site-local or UTC, by its flag).
 *
 * @return list<int>
 */
function loadUpdateEpochs(Db $db, int $incidentId, DateTimeZone $zone): array
{
    $epochs = [];
    foreach ($db->all('SELECT incident_update_timestamp, incident_update_utc FROM incident_update WHERE incident_update_incident_id = ?', [$incidentId]) as $row) {
        $epochs[] = storedTimestampToEpoch((string)$row['incident_update_timestamp'], dbBool($row['incident_update_utc']), $zone);
    }
    return $epochs;
}
