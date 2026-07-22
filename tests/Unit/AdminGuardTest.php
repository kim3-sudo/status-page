<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once PROJECT_ROOT . '/admin/_guard_functions.php';

final class AdminGuardTest extends TestCase
{
    public function testRejectsSessionWithNoId(): void
    {
        $this->assertFalse(isAdminSessionValid([]));
    }

    public function testRejectsSessionWithOtherKeysButNoId(): void
    {
        $this->assertFalse(isAdminSessionValid(['firstname' => 'Ada', 'suflag' => 1]));
    }

    public function testAcceptsSessionWithId(): void
    {
        $this->assertTrue(isAdminSessionValid(['id' => 42]));
    }

    public function testAcceptsSessionWithFalsyButSetId(): void
    {
        // isset() is true even for id === 0 or id === '' — the guard cares
        // about presence, not truthiness, since user_id 0 could theoretically
        // be a valid row id depending on the schema's AUTO_INCREMENT start.
        $this->assertTrue(isAdminSessionValid(['id' => 0]));
    }
}
