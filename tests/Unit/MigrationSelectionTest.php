<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once PROJECT_ROOT . '/install/_migration_functions.php';

final class MigrationSelectionTest extends TestCase
{
    public function testSelectsOnlyVersionsNewerThanCurrent(): void
    {
        $result = selectMigrationsToApply(['0.1.0', '0.2.0', '0.3.0'], '0.1.0', '0.3.0');
        $this->assertSame(['0.2.0', '0.3.0'], $result);
    }

    public function testExcludesVersionsBeyondTarget(): void
    {
        $result = selectMigrationsToApply(['0.1.0', '0.2.0', '0.3.0'], '0.1.0', '0.2.0');
        $this->assertSame(['0.2.0'], $result);
    }

    public function testReturnsEmptyWhenAlreadyCurrent(): void
    {
        $result = selectMigrationsToApply(['0.1.0', '0.2.0'], '0.2.0', '0.2.0');
        $this->assertSame([], $result);
    }

    public function testOrdersResultsAscendingRegardlessOfInputOrder(): void
    {
        $result = selectMigrationsToApply(['0.3.0', '0.1.0', '0.2.0'], '0.0.0', '0.3.0');
        $this->assertSame(['0.1.0', '0.2.0', '0.3.0'], $result);
    }

    public function testHandlesMultiSegmentVersionsCorrectly(): void
    {
        // A naive string comparison would put '0.10.0' before '0.9.0'.
        $result = selectMigrationsToApply(['0.9.0', '0.10.0'], '0.0.0', '0.10.0');
        $this->assertSame(['0.9.0', '0.10.0'], $result);
    }
}
