<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class StatusModelTest extends TestCase
{
    // --- helpers ---------------------------------------------------------

    private function svc(int $id, ?string $stored, int $group = 1, string $name = ''): array
    {
        return ['id' => $id, 'name' => $name !== '' ? $name : "svc$id", 'description' => null,
                'link' => null, 'stored_status' => $stored, 'group_id' => $group];
    }

    /** @param array<int, string> $services service id => severity for this incident */
    private function inc(int $id, array $services, array $updates): array
    {
        return ['id' => $id, 'services' => $services, 'updates' => $updates];
    }

    private function upd(int $ts, string $status): array
    {
        return ['ts' => $ts, 'status' => $status];
    }

    private function inputs(array $groups, array $services, array $incidents): array
    {
        return ['groups' => $groups, 'services' => $services, 'incidents' => $incidents];
    }

    private const CORE = [['id' => 1, 'name' => 'Core']];

    // --- dbBool ----------------------------------------------------------

    public function testDbBoolAcceptsEveryDriverRepresentation(): void
    {
        foreach ([true, 1, '1', 't', 'true'] as $value) {
            $this->assertTrue(dbBool($value), var_export($value, true));
        }
        foreach ([false, 0, '0', 'f', '', null] as $value) {
            $this->assertFalse(dbBool($value), var_export($value, true));
        }
    }

    // --- planned maintenance rule ----------------------------------------

    public function testEndedMaintenanceIsNotShownAsCurrent(): void
    {
        $snapshot = buildStatusSnapshot($this->inputs(
            self::CORE,
            [$this->svc(1, 'PLA')],
            [$this->inc(5, [1 => 'PLA'], [$this->upd(10, 'PLA'), $this->upd(20, 'RES')])]
        ), 30, 0);

        $this->assertSame('OPE', $snapshot['services'][1]['status']);
        $this->assertSame('OPE', $snapshot['banner']);
    }

    public function testActiveMaintenanceShowsPlannedStatus(): void
    {
        $snapshot = buildStatusSnapshot($this->inputs(
            self::CORE,
            [$this->svc(1, 'PLA')],
            [$this->inc(5, [1 => 'PLA'], [$this->upd(10, 'PLA')])]
        ), 30, 0);

        $this->assertSame('PLA', $snapshot['services'][1]['status']);
        $this->assertSame('PLA', $snapshot['banner']);
    }

    public function testMaintenanceNeverHidesAnOutage(): void
    {
        $snapshot = buildStatusSnapshot($this->inputs(
            self::CORE,
            [$this->svc(1, 'MAJ')],
            [$this->inc(5, [1 => 'PLA'], [$this->upd(10, 'PLA')])]
        ), 30, 0);

        $this->assertSame('MAJ', $snapshot['services'][1]['status']);
    }

    public function testActiveMaintenanceOnOneServiceDoesNotAffectAnother(): void
    {
        $snapshot = buildStatusSnapshot($this->inputs(
            self::CORE,
            [$this->svc(1, 'PLA'), $this->svc(2, 'OPE')],
            [$this->inc(5, [1 => 'PLA'], [$this->upd(10, 'PLA')])]
        ), 30, 0);

        $this->assertSame('PLA', $snapshot['services'][1]['status']);
        $this->assertSame('OPE', $snapshot['services'][2]['status']);
    }

    // --- banner and groups -----------------------------------------------

    public function testBannerIsWorstAcrossAllServicesIncludingUngrouped(): void
    {
        $snapshot = buildStatusSnapshot($this->inputs(
            self::CORE,
            [$this->svc(1, 'DEG'), $this->svc(9, 'MAJ', 99)],
            []
        ), 30, 0);

        $this->assertSame('MAJ', $snapshot['banner']);
        $this->assertCount(1, $snapshot['groups']);
    }

    public function testGroupBadgeIsWorstMemberNotLastMember(): void
    {
        // Members in name order: the MAJ service comes first, a DEG one after it.
        // The old badge script showed whichever came last.
        $groups = [['id' => 1, 'name' => 'Core'], ['id' => 2, 'name' => 'Edge']];
        $services = [
            $this->svc(1, 'MAJ', 1, 'Alpha'),
            $this->svc(2, 'DEG', 1, 'Beta'),
            $this->svc(3, 'DEG', 2, 'Gamma'),
        ];
        $snapshot = buildStatusSnapshot($this->inputs($groups, $services, []), 30, 0);

        $this->assertSame('MAJ', $snapshot['groups'][0]['status']);
        $this->assertSame('DEG', $snapshot['groups'][1]['status']);
    }

    public function testGroupMembersKeepInputOrder(): void
    {
        $services = [$this->svc(7, 'OPE', 1, 'Aardvark'), $this->svc(2, 'OPE', 1, 'Zebra')];
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, $services, []), 30, 0);

        $this->assertSame([7, 2], array_column($snapshot['groups'][0]['services'], 'id'));
    }

    public function testEmptyGroupIsOperational(): void
    {
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, [], []), 30, 0);
        $this->assertSame('OPE', $snapshot['groups'][0]['status']);
        $this->assertSame('OPE', $snapshot['banner']);
    }

    public function testNullStoredStatusIsOperational(): void
    {
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, [$this->svc(1, null)], []), 30, 0);
        $this->assertSame('OPE', $snapshot['services'][1]['status']);
    }

    // --- uptime ----------------------------------------------------------

    public function testServiceUptimeCountsOutagesOnly(): void
    {
        $incidents = [
            $this->inc(1, [1 => 'MAJ'], [$this->upd(10, 'INV'), $this->upd(20, 'RES')]),
            $this->inc(2, [1 => 'PLA'], [$this->upd(50, 'PLA'), $this->upd(60, 'RES')]),
            $this->inc(3, [1 => 'DEG'], [$this->upd(70, 'INV'), $this->upd(75, 'RES')]),
        ];
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, [$this->svc(1, 'OPE')], $incidents), 100, 0);

        $this->assertEqualsWithDelta(90.0, $snapshot['services'][1]['uptime'], 1e-9);
    }

    public function testMinorOutageCountsAsDowntime(): void
    {
        $incidents = [$this->inc(1, [1 => 'MIN'], [$this->upd(0, 'INV'), $this->upd(25, 'RES')])];
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, [$this->svc(1, 'OPE')], $incidents), 100, 0);

        $this->assertEqualsWithDelta(75.0, $snapshot['services'][1]['uptime'], 1e-9);
    }

    public function testUptimeIsFullWhenNoIncidents(): void
    {
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, [$this->svc(1, 'OPE')], []), 100, 0);
        $this->assertSame(100.0, $snapshot['services'][1]['uptime']);
    }

    public function testResolutionOnlyIncidentDoesNotBreakTheSnapshot(): void
    {
        $snapshot = buildStatusSnapshot($this->inputs(
            self::CORE,
            [$this->svc(1, 'OPE')],
            [$this->inc(1, [1 => 'MAJ'], [$this->upd(10, 'RES')])]
        ), 100, 0);

        $this->assertSame(100.0, $snapshot['services'][1]['uptime']);
    }

    // --- incident list ---------------------------------------------------

    private function listed(int $id, string $date): array
    {
        return ['id' => $id, 'date' => $date, 'description' => "i$id", 'status' => 'INV',
                'status_name' => 'Investigating', 'updates' => []];
    }

    public function testPublicListUsesTheDisplayWindow(): void
    {
        $incidents = [
            $this->listed(1, '2026-07-04'), // 6 days back: outside a 5-day window
            $this->listed(2, '2026-07-05'), // on the oldest day: in
            $this->listed(3, '2026-07-10'),
            $this->listed(4, '2026-07-12'), // on the newest day: in
            $this->listed(5, '2026-07-13'), // beyond the horizon: out
        ];
        $public = incidentsForPage($incidents, '2026-07-10', 5, 2);

        $this->assertSame([4, 3, 2], array_column($public, 'id'));
    }

    public function testAdminExtrasAreOnlyBeyondTheHorizon(): void
    {
        $incidents = [$this->listed(1, '2026-07-12'), $this->listed(2, '2026-07-13'), $this->listed(3, '2026-08-01')];
        $extras = incidentsForPage($incidents, '2026-07-10', 5, 2, true);

        $this->assertSame([3, 2], array_column($extras, 'id'));
    }

    public function testListedUpdatesAreNewestFirst(): void
    {
        $incident = $this->listed(1, '2026-07-10');
        $incident['updates'] = [
            ['ts' => 100, 'status' => 'INV', 'status_name' => '', 'description' => ''],
            ['ts' => 300, 'status' => 'RES', 'status_name' => '', 'description' => ''],
            ['ts' => 200, 'status' => 'MON', 'status_name' => '', 'description' => ''],
        ];
        $listed = incidentsForPage([$incident], '2026-07-10', 5, 2);

        $this->assertSame([300, 200, 100], array_column($listed[0]['updates'], 'ts'));
    }

    public function testSameDayIncidentsAreNewestIdFirst(): void
    {
        $incidents = [$this->listed(1, '2026-07-10'), $this->listed(2, '2026-07-10')];
        $listed = incidentsForPage($incidents, '2026-07-10', 5, 2);

        $this->assertSame([2, 1], array_column($listed, 'id'));
    }
}
