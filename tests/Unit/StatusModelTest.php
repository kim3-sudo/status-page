<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Service status is derived from incidents, so these tests build incidents rather
 * than setting a stored status. Times are small integers: "now" is usually 30 and
 * the window starts at 0.
 */
final class StatusModelTest extends TestCase
{
    private const NOW = 30;
    private const CORE = [['id' => 1, 'name' => 'Core']];

    private function svc(int $id, int $group = 1, string $name = ''): array
    {
        return ['id' => $id, 'name' => $name !== '' ? $name : "svc$id", 'description' => null,
                'link' => null, 'group_id' => $group];
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

    private function statusOf(array $incidents, ?array $services = null, int $now = self::NOW): string
    {
        $services ??= [$this->svc(1)];
        return buildStatusSnapshot($this->inputs(self::CORE, $services, $incidents), $now, 0)['services'][1]['status'];
    }

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

    // --- derived service state -------------------------------------------

    public function testServiceWithNoIncidentsIsOperational(): void
    {
        $this->assertSame('OPE', $this->statusOf([]));
    }

    public function testOpenOutageSetsItsSeverity(): void
    {
        $this->assertSame('MAJ', $this->statusOf([$this->inc(1, [1 => 'MAJ'], [$this->upd(10, 'INV')])]));
    }

    public function testResolvedOutageIsOperational(): void
    {
        $incident = $this->inc(1, [1 => 'MAJ'], [$this->upd(10, 'INV'), $this->upd(20, 'RES')]);
        $this->assertSame('OPE', $this->statusOf([$incident]));
    }

    public function testLatestUpdateDecidesTheState(): void
    {
        $incident = $this->inc(1, [1 => 'MAJ'], [$this->upd(10, 'INV'), $this->upd(20, 'RES'), $this->upd(25, 'INV')]);
        $this->assertSame('MAJ', $this->statusOf([$incident]));
    }

    public function testUpdatesAfterNowAreIgnored(): void
    {
        $incident = $this->inc(1, [1 => 'MAJ'], [$this->upd(10, 'INV'), $this->upd(50, 'RES')]);
        $this->assertSame('MAJ', $this->statusOf([$incident]));
    }

    public function testEndedMaintenanceIsNotCurrent(): void
    {
        $incident = $this->inc(1, [1 => 'PLA'], [$this->upd(10, 'PLA'), $this->upd(20, 'RES')]);
        $this->assertSame('OPE', $this->statusOf([$incident]));
    }

    public function testActiveMaintenanceShowsPlannedStatus(): void
    {
        $this->assertSame('PLA', $this->statusOf([$this->inc(1, [1 => 'PLA'], [$this->upd(10, 'PLA')])]));
    }

    public function testFutureMaintenanceIsNotCurrentYet(): void
    {
        $incident = $this->inc(1, [1 => 'PLA'], [$this->upd(50, 'PLA'), $this->upd(60, 'RES')]);
        $this->assertSame('OPE', $this->statusOf([$incident]));
    }

    public function testOutageIsNotHiddenByMaintenance(): void
    {
        $incidents = [
            $this->inc(1, [1 => 'MAJ'], [$this->upd(10, 'INV')]),
            $this->inc(2, [1 => 'PLA'], [$this->upd(15, 'PLA')]),
        ];
        $this->assertSame('MAJ', $this->statusOf($incidents));
    }

    public function testMaintenanceOnOneServiceDoesNotAffectAnother(): void
    {
        $services = [$this->svc(1), $this->svc(2)];
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, $services, [
            $this->inc(5, [1 => 'PLA'], [$this->upd(10, 'PLA')]),
        ]), self::NOW, 0);

        $this->assertSame('PLA', $snapshot['services'][1]['status']);
        $this->assertSame('OPE', $snapshot['services'][2]['status']);
    }

    public function testSeverityIsKeptPerServiceOnOneIncident(): void
    {
        $services = [$this->svc(1), $this->svc(2)];
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, $services, [
            $this->inc(1, [1 => 'MAJ', 2 => 'DEG'], [$this->upd(10, 'INV')]),
        ]), self::NOW, 0);

        $this->assertSame('MAJ', $snapshot['services'][1]['status']);
        $this->assertSame('DEG', $snapshot['services'][2]['status']);
    }

    public function testCurrentServiceStatesListsEveryOpenState(): void
    {
        $incidents = [
            $this->inc(1, [1 => 'MAJ'], [$this->upd(10, 'INV')]),
            $this->inc(2, [1 => 'PLA', 2 => 'DEG'], [$this->upd(12, 'PLA')]),
            $this->inc(3, [2 => 'MIN'], [$this->upd(5, 'INV'), $this->upd(8, 'RES')]),
        ];
        $states = currentServiceStates($incidents, self::NOW);

        $this->assertSame(['MAJ', 'PLA'], $states[1]);
        // Planned maintenance applies PLA to every service on that incident, so service 2 is PLA too.
        $this->assertSame(['PLA'], $states[2]);
    }

    // --- banner and groups -----------------------------------------------

    public function testBannerIsWorstAcrossAllServicesIncludingUngrouped(): void
    {
        $services = [$this->svc(1), $this->svc(9, 99)];
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, $services, [
            $this->inc(1, [1 => 'DEG'], [$this->upd(10, 'INV')]),
            $this->inc(2, [9 => 'MAJ'], [$this->upd(10, 'INV')]),
        ]), self::NOW, 0);

        $this->assertSame('MAJ', $snapshot['banner']);
        $this->assertCount(1, $snapshot['groups']);
    }

    public function testGroupBadgeIsWorstMemberNotLastMember(): void
    {
        // Members in name order: the MAJ service comes first, a DEG one after it.
        $groups = [['id' => 1, 'name' => 'Core'], ['id' => 2, 'name' => 'Edge']];
        $services = [$this->svc(1, 1, 'Alpha'), $this->svc(2, 1, 'Beta'), $this->svc(3, 2, 'Gamma')];
        $snapshot = buildStatusSnapshot($this->inputs($groups, $services, [
            $this->inc(1, [1 => 'MAJ'], [$this->upd(10, 'INV')]),
            $this->inc(2, [2 => 'DEG'], [$this->upd(10, 'INV')]),
            $this->inc(3, [3 => 'DEG'], [$this->upd(10, 'INV')]),
        ]), self::NOW, 0);

        $this->assertSame('MAJ', $snapshot['groups'][0]['status']);
        $this->assertSame('DEG', $snapshot['groups'][1]['status']);
    }

    public function testGroupMembersKeepInputOrder(): void
    {
        $services = [$this->svc(7, 1, 'Aardvark'), $this->svc(2, 1, 'Zebra')];
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, $services, []), self::NOW, 0);

        $this->assertSame([7, 2], array_column($snapshot['groups'][0]['services'], 'id'));
    }

    public function testEmptyGroupIsOperational(): void
    {
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, [], []), self::NOW, 0);
        $this->assertSame('OPE', $snapshot['groups'][0]['status']);
        $this->assertSame('OPE', $snapshot['banner']);
    }

    // --- uptime ----------------------------------------------------------

    public function testServiceUptimeCountsOutagesOnly(): void
    {
        $incidents = [
            $this->inc(1, [1 => 'MAJ'], [$this->upd(10, 'INV'), $this->upd(20, 'RES')]),
            $this->inc(2, [1 => 'PLA'], [$this->upd(50, 'PLA'), $this->upd(60, 'RES')]),
            $this->inc(3, [1 => 'DEG'], [$this->upd(70, 'INV'), $this->upd(75, 'RES')]),
        ];
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, [$this->svc(1)], $incidents), 100, 0);

        $this->assertEqualsWithDelta(90.0, $snapshot['services'][1]['uptime'], 1e-9);
    }

    public function testMinorOutageCountsAsDowntime(): void
    {
        $incidents = [$this->inc(1, [1 => 'MIN'], [$this->upd(0, 'INV'), $this->upd(25, 'RES')])];
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, [$this->svc(1)], $incidents), 100, 0);

        $this->assertEqualsWithDelta(75.0, $snapshot['services'][1]['uptime'], 1e-9);
    }

    public function testUptimeIsFullWhenNoIncidents(): void
    {
        $snapshot = buildStatusSnapshot($this->inputs(self::CORE, [$this->svc(1)], []), 100, 0);
        $this->assertSame(100.0, $snapshot['services'][1]['uptime']);
    }

    public function testResolutionOnlyIncidentDoesNotBreakTheSnapshot(): void
    {
        $snapshot = buildStatusSnapshot($this->inputs(
            self::CORE,
            [$this->svc(1)],
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
