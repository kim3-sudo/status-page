<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Uptime and planned-maintenance calculations.
//
// Pure functions: no database access, and the current time is only ever passed
// in as $now. Every timestamp is a Unix epoch integer (see lib/time.php for
// converting stored values).

// Statuses that count as downtime. Decision: planned maintenance (PLA) and
// degraded performance (DEG) do not reduce uptime.
const UPTIME_DOWNTIME_STATUSES = ['MIN', 'MAJ'];

/**
 * Status segments produced by one incident's updates.
 *
 * Each non-RES update starts a segment that runs until the next update, or until
 * $now if it is the last one. RES closes the incident and produces no segment.
 * Updates after $now are ignored, so a future-dated update cannot change the
 * present.
 *
 * @param list<array{ts:int, status:string}> $updates in any order
 * @return list<array{start:int, end:int, status:string}>
 */
function incidentSegments(array $updates, int $now): array
{
    $updates = array_values(array_filter($updates, fn (array $u): bool => $u['ts'] <= $now));
    usort($updates, fn (array $a, array $b): int => $a['ts'] <=> $b['ts']);

    $segments = [];
    $count = count($updates);
    for ($i = 0; $i < $count; $i++) {
        $update = $updates[$i];
        if ($update['status'] === 'RES') {
            continue;
        }
        $end = $i + 1 < $count ? $updates[$i + 1]['ts'] : $now;
        if ($end > $update['ts']) {
            $segments[] = ['start' => $update['ts'], 'end' => $end, 'status' => $update['status']];
        }
    }
    return $segments;
}

/**
 * Groups every incident's segments by the services it describes.
 * Services with no incidents are absent from the result.
 *
 * @param list<array{service_ids: list<int|string>, updates: list<array{ts:int, status:string}>}> $incidents
 * @return array<int, list<array{start:int, end:int, status:string}>> service id => segments
 */
function segmentsByService(array $incidents, int $now): array
{
    $byService = [];
    foreach ($incidents as $incident) {
        $segments = incidentSegments($incident['updates'], $now);
        foreach ($incident['service_ids'] as $serviceId) {
            foreach ($segments as $segment) {
                $byService[(int)$serviceId][] = $segment;
            }
        }
    }
    return $byService;
}

/**
 * Uptime percentage for one service over the window [$windowStart, $now].
 *
 * Only segments whose status is in $downtimeStatuses count. Overlapping downtime
 * is counted once. Downtime outside the window is ignored, so the result is
 * always between 0 and 100. An empty or inverted window returns 100.
 *
 * @param list<array{start:int, end:int, status:string}> $segments
 * @param list<string> $downtimeStatuses
 */
function uptimePercent(
    array $segments,
    int $windowStart,
    int $now,
    array $downtimeStatuses = UPTIME_DOWNTIME_STATUSES
): float {
    $windowLength = $now - $windowStart;
    if ($windowLength <= 0) {
        return 100.0;
    }

    $intervals = [];
    foreach ($segments as $segment) {
        if (!in_array($segment['status'], $downtimeStatuses, true)) {
            continue;
        }
        $start = max($segment['start'], $windowStart);
        $end = min($segment['end'], $now);
        if ($end > $start) {
            $intervals[] = [$start, $end];
        }
    }

    $downtime = unionLength($intervals);
    $percent = 100.0 * (1 - $downtime / $windowLength);
    return max(0.0, min(100.0, $percent));
}

/**
 * Total length covered by half-open [start, end) intervals, counting overlaps once.
 *
 * @param list<array{0:int, 1:int}> $intervals
 */
function unionLength(array $intervals): int
{
    usort($intervals, fn (array $a, array $b): int => $a[0] <=> $b[0]);

    $total = 0;
    $curStart = null;
    $curEnd = null;
    foreach ($intervals as [$start, $end]) {
        if ($curEnd === null || $start > $curEnd) {
            if ($curEnd !== null) {
                $total += $curEnd - $curStart;
            }
            $curStart = $start;
            $curEnd = $end;
        } else {
            $curEnd = max($curEnd, $end);
        }
    }
    if ($curEnd !== null) {
        $total += $curEnd - $curStart;
    }
    return $total;
}

/**
 * Planned-maintenance windows, from planned-maintenance rows across incidents.
 *
 * A PLA update opens a window for its incident. The next RES for that incident
 * closes it. A repeated PLA keeps the first start. A RES with no open window is
 * ignored.
 *
 * @param list<array{incident_id: int, ts: int, status: string}> $rows
 * @return list<array{incident_id: int, start: int, end: ?int}> end is null while open
 */
function maintenanceWindows(array $rows): array
{
    usort($rows, fn (array $a, array $b): int => [$a['incident_id'], $a['ts']] <=> [$b['incident_id'], $b['ts']]);

    $windows = [];
    $openIndex = [];
    foreach ($rows as $row) {
        $id = $row['incident_id'];
        if ($row['status'] === 'PLA') {
            if (!isset($openIndex[$id])) {
                $windows[] = ['incident_id' => $id, 'start' => $row['ts'], 'end' => null];
                $openIndex[$id] = count($windows) - 1;
            }
        } elseif ($row['status'] === 'RES' && isset($openIndex[$id])) {
            $windows[$openIndex[$id]]['end'] = $row['ts'];
            unset($openIndex[$id]);
        }
    }
    return $windows;
}

/**
 * Maintenance windows that contain $now. A window starting exactly at $now is
 * active. One ending exactly at $now is not.
 *
 * @param list<array{incident_id: int, ts: int, status: string}> $rows
 * @return list<array{incident_id: int, start: int, end: ?int}>
 */
function activeMaintenanceWindows(array $rows, int $now): array
{
    return array_values(array_filter(
        maintenanceWindows($rows),
        fn (array $w): bool => $w['start'] <= $now && ($w['end'] === null || $now < $w['end'])
    ));
}
