<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class StatusTest extends TestCase
{
    public function testNoServicesIsOperational(): void
    {
        $this->assertSame('OPE', worstServiceStatus([]));
    }

    public function testWorstStatusWins(): void
    {
        $this->assertSame('DEG', worstServiceStatus(['OPE', 'DEG']));
        $this->assertSame('MIN', worstServiceStatus(['DEG', 'MIN']));
        $this->assertSame('MAJ', worstServiceStatus(['MIN', 'MAJ', 'DEG']));
        $this->assertSame('DEG', worstServiceStatus(['PLA', 'DEG']));
        $this->assertSame('PLA', worstServiceStatus(['OPE', 'PLA']));
    }

    public function testUnknownCodesAreIgnored(): void
    {
        $this->assertSame('OPE', worstServiceStatus(['XXX']));
        $this->assertSame('PLA', worstServiceStatus(['XXX', 'PLA']));
    }

    public function testEveryRankedCodeHasDisplayAndSummary(): void
    {
        foreach (array_keys(SERVICE_STATUS_SEVERITY) as $code) {
            $this->assertArrayHasKey($code, SERVICE_STATUS_DISPLAY, "$code missing display entry");
            $this->assertArrayHasKey($code, SERVICE_STATUS_SUMMARY, "$code missing summary entry");
        }
    }

    public function testUnknownCodeDisplaysAsOperational(): void
    {
        $this->assertSame(SERVICE_STATUS_DISPLAY['OPE'], serviceStatusDisplay('XXX'));
        $this->assertSame(SERVICE_STATUS_DISPLAY['OPE'], serviceStatusDisplay(null));
        $this->assertSame(SERVICE_STATUS_SUMMARY['OPE'], serviceStatusSummary('XXX'));
    }

    public function testDisplayUsesStatusPageLabels(): void
    {
        $this->assertSame('Degraded Performance', serviceStatusDisplay('DEG')['label']);
        $this->assertSame('text-bg-danger', serviceStatusDisplay('MAJ')['class']);
    }

    public function testSummaryUsesApiWording(): void
    {
        $this->assertSame('Degraded', serviceStatusSummary('DEG')['brief']);
        $this->assertSame('Some systems are experiencing major outages.', serviceStatusSummary('MAJ')['description']);
    }
}
