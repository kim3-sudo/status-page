<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * All timestamps are small integers, so each case reads as a timeline.
 * Window [0, 100) unless a case says otherwise.
 */
final class UptimeTest extends TestCase
{
    private const DAY = 86400;

    // --- uptimePercent ---------------------------------------------------

    public function testNoSegmentsIsFullUptime(): void
    {
        $this->assertSame(100.0, uptimePercent([], 0, 100));
    }

    public function testOutageEndingBeforeWindowIsIgnored(): void
    {
        $segments = [['start' => -500, 'end' => -100, 'status' => 'MAJ']];
        $this->assertSame(100.0, uptimePercent($segments, 0, 100));
    }

    public function testOutageStartingBeforeWindowIsClipped(): void
    {
        $segments = [['start' => -50, 'end' => 20, 'status' => 'MAJ']];
        $this->assertSame(80.0, uptimePercent($segments, 0, 100));
    }

    public function testOutageRunningPastNowIsClipped(): void
    {
        $segments = [['start' => 90, 'end' => 500, 'status' => 'MAJ']];
        $this->assertSame(90.0, uptimePercent($segments, 0, 100));
    }

    public function testOverlappingOutagesCountOnce(): void
    {
        $segments = [
            ['start' => 10, 'end' => 30, 'status' => 'MAJ'],
            ['start' => 20, 'end' => 40, 'status' => 'MIN'],
        ];
        $this->assertSame(70.0, uptimePercent($segments, 0, 100));
    }

    public function testDegradedAndMaintenanceDoNotReduceUptime(): void
    {
        $segments = [
            ['start' => 0, 'end' => 50, 'status' => 'DEG'],
            ['start' => 0, 'end' => 50, 'status' => 'PLA'],
        ];
        $this->assertSame(100.0, uptimePercent($segments, 0, 100));
    }

    public function testMinorAndMajorOutagesBothCount(): void
    {
        $segments = [['start' => 0, 'end' => 25, 'status' => 'MIN']];
        $this->assertSame(75.0, uptimePercent($segments, 0, 100));
    }

    public function testEmptyOrInvertedWindowIsFullUptime(): void
    {
        $segments = [['start' => 0, 'end' => 50, 'status' => 'MAJ']];
        $this->assertSame(100.0, uptimePercent($segments, 100, 100));
        $this->assertSame(100.0, uptimePercent($segments, 100, 50));
    }

    public function testNeverNegativeEvenWhenDowntimeExceedsWindow(): void
    {
        // Downtime covering the whole window, and beyond it, clamps to 0 rather than going negative.
        // (The original formula returned -96.67% for a 60-day outage in a 30-day window.)
        $segments = [['start' => -60 * self::DAY, 'end' => 10 * self::DAY, 'status' => 'MAJ']];
        $this->assertSame(0.0, uptimePercent($segments, -30 * self::DAY, 0));
    }

    public function testThirtyDayWindowWithSixtyDayOutage(): void
    {
        // Outage from 60 days ago to 1 day ago; the window is the last 30 days.
        // The outage covers the window from 30 days ago to 1 day ago: 29 of 30 days.
        $now = 1000 * self::DAY;
        $segments = [['start' => $now - 60 * self::DAY, 'end' => $now - self::DAY, 'status' => 'MAJ']];
        $this->assertEqualsWithDelta(100.0 / 30, uptimePercent($segments, $now - 30 * self::DAY, $now), 1e-9);
    }

    // --- incidentSegments ------------------------------------------------

    public function testResolvedIncidentProducesOneSegment(): void
    {
        $updates = [['ts' => 10, 'status' => 'MAJ'], ['ts' => 30, 'status' => 'RES']];
        $this->assertSame([['start' => 10, 'end' => 30, 'status' => 'MAJ']], incidentSegments($updates, 100));
    }

    public function testOpenIncidentRunsUntilNow(): void
    {
        $updates = [['ts' => 10, 'status' => 'MAJ']];
        $this->assertSame([['start' => 10, 'end' => 60, 'status' => 'MAJ']], incidentSegments($updates, 60));
    }

    public function testSeverityChangeSplitsSegments(): void
    {
        $updates = [
            ['ts' => 10, 'status' => 'MAJ'],
            ['ts' => 20, 'status' => 'MIN'],
            ['ts' => 40, 'status' => 'RES'],
        ];
        $this->assertSame(
            [
                ['start' => 10, 'end' => 20, 'status' => 'MAJ'],
                ['start' => 20, 'end' => 40, 'status' => 'MIN'],
            ],
            incidentSegments($updates, 100)
        );
    }

    public function testReopenedIncidentHasTwoSegments(): void
    {
        $updates = [
            ['ts' => 10, 'status' => 'MAJ'],
            ['ts' => 20, 'status' => 'RES'],
            ['ts' => 50, 'status' => 'MAJ'],
        ];
        $this->assertSame(
            [
                ['start' => 10, 'end' => 20, 'status' => 'MAJ'],
                ['start' => 50, 'end' => 60, 'status' => 'MAJ'],
            ],
            incidentSegments($updates, 60)
        );
    }

    public function testResolutionOnlyIncidentHasNoSegments(): void
    {
        $this->assertSame([], incidentSegments([['ts' => 10, 'status' => 'RES']], 100));
    }

    public function testFutureDatedResolutionDoesNotCloseTheIncidentYet(): void
    {
        $updates = [['ts' => 10, 'status' => 'MAJ'], ['ts' => 200, 'status' => 'RES']];
        $this->assertSame([['start' => 10, 'end' => 100, 'status' => 'MAJ']], incidentSegments($updates, 100));
    }

    public function testUpdatesAreOrderedBeforeUse(): void
    {
        $updates = [['ts' => 30, 'status' => 'RES'], ['ts' => 10, 'status' => 'MAJ']];
        $this->assertSame([['start' => 10, 'end' => 30, 'status' => 'MAJ']], incidentSegments($updates, 100));
    }

    // --- segmentsByService + uptime end to end ----------------------------

    public function testOverlappingIncidentsOnOneServiceAreMergedNotDoubleCounted(): void
    {
        $incidents = [
            ['service_ids' => [1], 'updates' => [['ts' => 10, 'status' => 'MAJ'], ['ts' => 12, 'status' => 'RES']]],
            ['service_ids' => [1, 2], 'updates' => [['ts' => 11, 'status' => 'MAJ'], ['ts' => 13, 'status' => 'RES']]],
        ];
        $byService = segmentsByService($incidents, 100);

        // Service 1: [10,12) and [11,13) merge to [10,13), so 3 units of downtime.
        $this->assertEqualsWithDelta(97.0, uptimePercent($byService[1], 0, 100), 1e-9);
        // Service 2 only sees the second incident: 2 units.
        $this->assertEqualsWithDelta(98.0, uptimePercent($byService[2], 0, 100), 1e-9);
    }

    public function testServicesWithoutIncidentsAreAbsent(): void
    {
        $incidents = [['service_ids' => [1], 'updates' => [['ts' => 10, 'status' => 'MAJ']]]];
        $byService = segmentsByService($incidents, 50);
        $this->assertArrayHasKey(1, $byService);
        $this->assertArrayNotHasKey(7, $byService);
    }

    public function testServiceIdsAreCastToInt(): void
    {
        $incidents = [['service_ids' => ['3'], 'updates' => [['ts' => 10, 'status' => 'MAJ']]]];
        $this->assertArrayHasKey(3, segmentsByService($incidents, 50));
    }

    // --- unionLength -----------------------------------------------------

    public function testUnionLengthHandlesDisjointAndNestedIntervals(): void
    {
        $this->assertSame(5, unionLength([[0, 2], [4, 7]]));
        $this->assertSame(10, unionLength([[0, 10], [2, 5], [8, 9]]));
        $this->assertSame(0, unionLength([]));
    }

    // --- maintenance -----------------------------------------------------

    public function testPlannedWindowOpensAndCloses(): void
    {
        $rows = [
            ['incident_id' => 5, 'ts' => 10, 'status' => 'PLA'],
            ['incident_id' => 5, 'ts' => 20, 'status' => 'RES'],
        ];
        $this->assertSame(
            [['incident_id' => 5, 'start' => 10, 'end' => 20]],
            maintenanceWindows($rows)
        );
    }

    public function testOpenWindowHasNullEnd(): void
    {
        $rows = [['incident_id' => 5, 'ts' => 10, 'status' => 'PLA']];
        $this->assertSame([['incident_id' => 5, 'start' => 10, 'end' => null]], maintenanceWindows($rows));
    }

    public function testRepeatedStartKeepsFirstAndResolutionWithoutWindowIsIgnored(): void
    {
        $rows = [
            ['incident_id' => 5, 'ts' => 10, 'status' => 'PLA'],
            ['incident_id' => 5, 'ts' => 12, 'status' => 'PLA'],
            ['incident_id' => 5, 'ts' => 20, 'status' => 'RES'],
            ['incident_id' => 6, 'ts' => 3, 'status' => 'RES'],
        ];
        $this->assertSame([['incident_id' => 5, 'start' => 10, 'end' => 20]], maintenanceWindows($rows));
    }

    public function testWindowsOfDifferentIncidentsDoNotInterfere(): void
    {
        $rows = [
            ['incident_id' => 1, 'ts' => 10, 'status' => 'PLA'],
            ['incident_id' => 2, 'ts' => 12, 'status' => 'PLA'],
            ['incident_id' => 1, 'ts' => 15, 'status' => 'RES'],
            ['incident_id' => 2, 'ts' => 30, 'status' => 'RES'],
        ];
        $this->assertSame(
            [
                ['incident_id' => 1, 'start' => 10, 'end' => 15],
                ['incident_id' => 2, 'start' => 12, 'end' => 30],
            ],
            maintenanceWindows($rows)
        );
    }

    public function testActiveWindowBoundaries(): void
    {
        $rows = [['incident_id' => 5, 'ts' => 10, 'status' => 'PLA'], ['incident_id' => 5, 'ts' => 20, 'status' => 'RES']];

        $this->assertCount(1, activeMaintenanceWindows($rows, 10), 'starts active at its start');
        $this->assertCount(1, activeMaintenanceWindows($rows, 19));
        $this->assertCount(0, activeMaintenanceWindows($rows, 20), 'not active at its end');
        $this->assertCount(0, activeMaintenanceWindows($rows, 9), 'not active before it starts');
    }

    public function testFutureScheduledWindowIsNotActiveYet(): void
    {
        $rows = [['incident_id' => 5, 'ts' => 50, 'status' => 'PLA'], ['incident_id' => 5, 'ts' => 60, 'status' => 'RES']];
        $this->assertCount(0, activeMaintenanceWindows($rows, 15));
    }

    public function testOpenWindowIsActiveUntilClosed(): void
    {
        $rows = [['incident_id' => 5, 'ts' => 10, 'status' => 'PLA']];
        $this->assertCount(1, activeMaintenanceWindows($rows, 1000000));
    }
}
