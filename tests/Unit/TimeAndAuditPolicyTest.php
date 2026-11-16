<?php

namespace Tests\Unit;

use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TimeAndAuditPolicyTest extends TestCase
{
    // --- time -------------------------------------------------------------

    public function testSiteLocalRowIsReadInSiteZone(): void
    {
        $ny = new DateTimeZone('America/New_York');
        // 12:00 EDT on 1 July 2026 is 16:00 UTC.
        $epoch = storedTimestampToEpoch('2026-07-01 12:00:00', false, $ny);
        $this->assertSame(gmmktime(16, 0, 0, 7, 1, 2026), $epoch);
    }

    public function testUtcRowIgnoresSiteZone(): void
    {
        $ny = new DateTimeZone('America/New_York');
        $epoch = storedTimestampToEpoch('2026-07-01 16:00:00', true, $ny);
        $this->assertSame(gmmktime(16, 0, 0, 7, 1, 2026), $epoch);
    }

    public function testAdminInputIsStoredAsUtc(): void
    {
        $ny = new DateTimeZone('America/New_York');
        $this->assertSame('2026-07-01 16:00:00', siteLocalToUtcString('2026-07-01 12:00:00', $ny));
        // Standard time (EST, UTC-5) in January.
        $this->assertSame('2026-01-15 17:30:00', siteLocalToUtcString('2026-01-15 12:30:00', $ny));
    }

    public function testExplicitOffsetInInputIsRespected(): void
    {
        $ny = new DateTimeZone('America/New_York');
        $this->assertSame('2026-07-01 10:00:00', siteLocalToUtcString('2026-07-01T10:00:00+00:00', $ny));
    }

    public function testEpochRendersInSiteZone(): void
    {
        $ny = new DateTimeZone('America/New_York');
        $local = epochToSiteLocal(gmmktime(16, 0, 0, 7, 1, 2026), $ny);
        $this->assertSame('2026-07-01 12:00:00', $local->format('Y-m-d H:i:s'));
    }

    public function testRoundTripPreservesInstant(): void
    {
        $ny = new DateTimeZone('America/New_York');
        $utc = siteLocalToUtcString('2026-03-08 09:15:00', $ny);
        $back = epochToSiteLocal(storedTimestampToEpoch($utc, true, $ny), $ny);
        $this->assertSame('2026-03-08 09:15:00', $back->format('Y-m-d H:i:s'));
    }

    // --- audit policy -----------------------------------------------------

    #[DataProvider('policies')]
    public function testFailureActionFollowsPolicy(string $policy, string $expected): void
    {
        $this->assertSame($expected, auditFailureAction($policy));
    }

    public static function policies(): array
    {
        return [
            'strict fails the action' => ['strict', 'throw'],
            'soft logs and continues' => ['soft', 'log'],
            'empty fails closed' => ['', 'throw'],
            'unknown fails closed' => ['verbose', 'throw'],
            'case differs fails closed' => ['SOFT', 'throw'],
        ];
    }

    public function testOnlySuperusersMayChangeAuditPolicy(): void
    {
        $this->assertSame(
            'Only superusers can change the audit policy.',
            settingWriteError('audit_policy', 'soft', ['suflag' => 0])
        );
        $this->assertSame(
            'Only superusers can change the audit policy.',
            settingWriteError('audit_policy', 'soft', [])
        );
        $this->assertNull(settingWriteError('audit_policy', 'soft', ['suflag' => 1]));
    }

    public function testAuditPolicyValueIsValidated(): void
    {
        $this->assertSame(
            'Audit policy must be strict or soft.',
            settingWriteError('audit_policy', 'off', ['suflag' => 1])
        );
        $this->assertNull(settingWriteError('audit_policy', 'strict', ['suflag' => 1]));
    }

    public function testOtherSettingsAreUnaffectedByAuditRules(): void
    {
        $this->assertNull(settingWriteError('footer_org', 'Anything', []));
    }
}
