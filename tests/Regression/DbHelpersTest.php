<?php

namespace Tests\Regression;

use PHPUnit\Framework\TestCase;

/**
 * writeToLog()/getSetting() used to be defined independently in three places
 * (templates/_header.php, install/run.php, saml/index.php as samlWriteToLog),
 * each building its own escaped-string-concatenation SQL. Guards against that
 * duplication and the string-concatenation pattern creeping back in.
 */
final class DbHelpersTest extends TestCase
{
    public function testDbHelpersUsesPreparedStatements(): void
    {
        $contents = file_get_contents(PROJECT_ROOT . '/templates/_db_helpers.php');
        $this->assertStringContainsString('->prepare(', $contents);
        $this->assertStringNotContainsString('mysqli_real_escape_string', $contents);
    }

    public function testWriteToLogIsNotRedefinedOutsideDbHelpers(): void
    {
        $offenders = [];
        foreach ($this->allPhpFilesExcludingVendor() as $file) {
            if (str_ends_with($file, '/templates/_db_helpers.php')) {
                continue;
            }
            if (preg_match('/function\s+(samlW|w)riteToLog\s*\(/', file_get_contents($file))) {
                $offenders[] = $file;
            }
        }
        $this->assertSame([], $offenders, 'writeToLog()/samlWriteToLog() should only be defined in templates/_db_helpers.php');
    }

    public function testGetSettingIsNotRedefinedOutsideDbHelpers(): void
    {
        $offenders = [];
        foreach ($this->allPhpFilesExcludingVendor() as $file) {
            if (str_ends_with($file, '/templates/_db_helpers.php')) {
                continue;
            }
            if (preg_match('/function\s+getSetting\s*\(/', file_get_contents($file))) {
                $offenders[] = $file;
            }
        }
        $this->assertSame([], $offenders, 'getSetting() should only be defined in templates/_db_helpers.php');
    }

    private function allPhpFilesExcludingVendor(): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(PROJECT_ROOT, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $fileInfo) {
            $path = $fileInfo->getPathname();
            if (str_contains($path, '/vendor/') || str_contains($path, '/tests/')) {
                continue;
            }
            if (str_ends_with($path, '.php')) {
                $files[] = $path;
            }
        }
        return $files;
    }
}
