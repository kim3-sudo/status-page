<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// What the status page and the public API show, computed from the stored data.
//
// Split in two:
//   - loadStatusInputs() reads the database and converts timestamps to epochs.
//   - buildStatusSnapshot() and incidentsForPage() are pure. They take $now as a
//     parameter, so they can be tested with fixed times.
//
// Display rules:
//   - A service's status is derived from its incidents at the chosen time. Each
//     incident's latest update at or before that time gives its state for each
//     service: resolved means nothing; planned maintenance means PLA; anything
//     else means the service's severity for that incident. The worst state wins.
//     The stored services.service_status_short column is not read here.
//   - The banner and each group badge use the same worst-status rule as the
//     service cards, so they cannot disagree.
//   - Downtime for uptime comes from each incident's severity for each service
//     (incident_service.severity). Only MIN and MAJ count (lib/uptime.php).

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/status.php';
require_once __DIR__ . '/time.php';
require_once __DIR__ . '/uptime.php';

/** Reads a boolean from any driver's representation: true, 1, '1', 't', 'true'. */
function dbBool($value): bool
{
    return $value === true || $value === 1 || $value === '1' || $value === 't' || $value === 'true';
}

/**
 * Pure. Builds everything the status page's banner, groups, and services need.
 *
 * @param array{
 *   groups: list<array{id:int, name:string}>,
 *   services: list<array{id:int, name:string, description:?string, link:?string, group_id:int}>,
 *   incidents: list<array{id:int, services:array<int, string>,
 *                         updates:list<array{ts:int, status:string}>}>
 * } $inputs  services maps service id => severity for each incident
 * @return array{
 *   banner: string,
 *   groups: list<array{id:int, name:string, status:string, services:list<array<string,mixed>>}>,
 *   services: array<int, array{id:int, name:string, description:?string, link:?string, status:string, uptime:float}>
 * }
 */
function buildStatusSnapshot(array $inputs, int $now, int $windowStart): array
{
    $states = currentServiceStates($inputs['incidents'], $now);
    $segmentInputs = array_map(
        fn (array $incident): array => ['services' => $incident['services'], 'updates' => $incident['updates']],
        $inputs['incidents']
    );
    $downtimeSegments = segmentsByService($segmentInputs, $now);

    $services = [];
    foreach ($inputs['services'] as $service) {
        $id = $service['id'];
        $services[$id] = [
            'id' => $id,
            'name' => $service['name'],
            'description' => $service['description'],
            'link' => $service['link'],
            'group_id' => $service['group_id'],
            'status' => worstServiceStatus($states[$id] ?? []),
            'uptime' => uptimePercent($downtimeSegments[$id] ?? [], $windowStart, $now),
        ];
    }

    // Members keep the input order (by service name), not the id order of $services.
    $groups = [];
    foreach ($inputs['groups'] as $group) {
        $members = [];
        foreach ($inputs['services'] as $service) {
            $built = $services[$service['id']];
            if ($built['group_id'] === $group['id']) {
                $members[] = $built;
            }
        }
        $groups[] = [
            'id' => $group['id'],
            'name' => $group['name'],
            'status' => worstServiceStatus(array_column($members, 'status')),
            'services' => $members,
        ];
    }

    return [
        'banner' => worstServiceStatus(array_column($services, 'status')),
        'groups' => $groups,
        'services' => $services,
    ];
}

/**
 * Pure. The state each service is in at $now, from every incident's latest update
 * at or before $now. Resolved incidents contribute nothing. A planned-maintenance
 * update gives PLA. Any other open update gives the incident's severity for the
 * service. Only updates at or before $now count, so future-dated updates are ignored.
 *
 * @param list<array{services: array<int|string, string>, updates: list<array{ts:int, status:string}>}> $incidents
 * @return array<int, list<string>> service id => state codes
 */
function currentServiceStates(array $incidents, int $now): array
{
    $states = [];
    foreach ($incidents as $incident) {
        $latest = null;
        foreach ($incident['updates'] as $update) {
            if ($update['ts'] <= $now && ($latest === null || $update['ts'] >= $latest['ts'])) {
                $latest = $update;
            }
        }
        if ($latest === null || $latest['status'] === 'RES') {
            continue;
        }
        foreach ($incident['services'] as $serviceId => $severity) {
            $states[(int)$serviceId][] = $latest['status'] === 'PLA' ? 'PLA' : (string)$severity;
        }
    }
    return $states;
}

/**
 * Pure. The incidents to list on the page, newest first.
 *
 * Public list: dated from $pastDays ago through $futureDays ahead of $today.
 * Admin-only extras ($beyondHorizonOnly): dated after that horizon, so they
 * don't repeat the public list.
 *
 * @param list<array{id:int, date:string, description:string, status:string,
 *                   status_name:string, updates:list<array{ts:int, status:string,
 *                   status_name:string, description:string}>}> $incidents
 * @return list<array<string, mixed>>  each updates list sorted newest first
 */
function incidentsForPage(
    array $incidents,
    string $today,
    int $pastDays,
    int $futureDays,
    bool $beyondHorizonOnly = false
): array {
    $base = new DateTimeImmutable($today);
    $oldest = $base->modify('-' . $pastDays . ' days')->format('Y-m-d');
    $newest = $base->modify('+' . $futureDays . ' days')->format('Y-m-d');

    $picked = array_filter($incidents, function (array $incident) use ($oldest, $newest, $beyondHorizonOnly): bool {
        $date = $incident['date'];
        return $beyondHorizonOnly
            ? $date > $newest
            : ($date >= $oldest && $date <= $newest);
    });

    foreach ($picked as &$incident) {
        usort($incident['updates'], fn (array $a, array $b): int => $b['ts'] <=> $a['ts']);
    }
    unset($incident);

    usort($picked, fn (array $a, array $b): int => [$b['date'], $b['id']] <=> [$a['date'], $a['id']]);
    return array_values($picked);
}

/**
 * Reads everything buildStatusSnapshot() and incidentsForPage() need. Fixed number
 * of queries, none per service. Timestamps are converted to epochs here.
 *
 * @return array{groups: list<array>, services: list<array>, incidents: list<array>}
 */
function loadStatusInputs(Db $db, DateTimeZone $siteZone): array
{
    $groups = [];
    foreach ($db->all('SELECT servicegroup_id, servicegroup_name FROM servicegroups ORDER BY servicegroup_name ASC') as $row) {
        $groups[] = ['id' => (int)$row['servicegroup_id'], 'name' => (string)$row['servicegroup_name']];
    }

    $services = [];
    foreach ($db->all('SELECT service_id, service_name, service_description, service_link, servicegroup_id FROM services ORDER BY service_name ASC') as $row) {
        $services[] = [
            'id' => (int)$row['service_id'],
            'name' => (string)$row['service_name'],
            'description' => $row['service_description'],
            'link' => $row['service_link'],
            'group_id' => (int)$row['servicegroup_id'],
        ];
    }

    $statusNames = [];
    foreach ($db->all('SELECT incident_status_code, incident_status_description FROM incident_status') as $row) {
        $statusNames[$row['incident_status_code']] = (string)$row['incident_status_description'];
    }

    $membership = [];
    foreach ($db->all('SELECT incident_id, service_id, severity FROM incident_service') as $row) {
        $membership[(int)$row['incident_id']][(int)$row['service_id']] = (string)$row['severity'];
    }

    $updatesByIncident = [];
    foreach ($db->all('SELECT incident_update_incident_id, incident_update_timestamp, incident_update_utc, incident_update_status_short, incident_update_description FROM incident_update') as $row) {
        $status = (string)$row['incident_update_status_short'];
        $updatesByIncident[(int)$row['incident_update_incident_id']][] = [
            'ts' => storedTimestampToEpoch((string)$row['incident_update_timestamp'], dbBool($row['incident_update_utc']), $siteZone),
            'status' => $status,
            'status_name' => $statusNames[$status] ?? '',
            'description' => (string)$row['incident_update_description'],
        ];
    }

    $incidents = [];
    foreach ($db->all('SELECT incident_id, incident_date, incident_description, incident_status_short FROM incident') as $row) {
        $id = (int)$row['incident_id'];
        $status = (string)$row['incident_status_short'];
        $incidents[] = [
            'id' => $id,
            'date' => (string)$row['incident_date'],
            'description' => (string)$row['incident_description'],
            'status' => $status,
            'status_name' => $statusNames[$status] ?? '',
            'services' => $membership[$id] ?? [],
            'updates' => $updatesByIncident[$id] ?? [],
        ];
    }

    return ['groups' => $groups, 'services' => $services, 'incidents' => $incidents];
}
