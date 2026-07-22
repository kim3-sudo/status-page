<?php

namespace Tests\Regression;

use PHPUnit\Framework\TestCase;

/**
 * Guards against the "header('Location: ...') without exit" bug that let
 * unauthenticated requests execute the full privileged body of nearly every
 * admin/*.php file. Every entry point must route through admin/_guard.php,
 * which is the one place that actually exit()s.
 */
final class AdminGuardCoverageTest extends TestCase
{
    // Files that intentionally do not include _guard.php:
    // - _guard.php / _guard_functions.php: the guard itself
    // - logout.php: has its own correct die()-before-any-header check
    // - index.php: pure router, exits in both branches without needing the
    //   "isset($_SESSION['id']) === false" redirect-to-login behavior alone
    // - pes.php: empty file, no dynamic behavior
    // - _incidentstyleguide.php / _pesstyleguide.php: static HTML fragments,
    //   no session/DB access
    private const ALLOWLIST = [
        '_guard.php',
        '_guard_functions.php',
        'logout.php',
        'index.php',
        'pes.php',
        '_incidentstyleguide.php',
        '_pesstyleguide.php',
    ];

    public function testEveryAdminEntryPointReferencesGuard(): void
    {
        foreach (glob(PROJECT_ROOT . '/admin/*.php') as $file) {
            $basename = basename($file);
            if (in_array($basename, self::ALLOWLIST, true)) {
                continue;
            }
            $this->assertStringContainsString(
                '_guard.php',
                file_get_contents($file),
                $basename . ' does not appear to include admin/_guard.php'
            );
        }
    }

    public function testLogoutRejectsUnauthenticatedWithoutHeaderRedirect(): void
    {
        // logout.php is the one legitimate exception to "route through
        // _guard.php" — confirm it still exits correctly on its own.
        $contents = file_get_contents(PROJECT_ROOT . '/admin/logout.php');
        $this->assertMatchesRegularExpression(
            '/if\s*\(!isset\(\$_SESSION\[\'id\'\]\)\)\s*\{\s*http_response_code\(403\);\s*die\(/s',
            $contents,
            'logout.php should die() immediately for unauthenticated requests'
        );
    }
}
