<?php

namespace Tests\Regression;

use PHPUnit\Framework\TestCase;

final class UpgradeScriptTest extends TestCase
{
    public function testUpgradeScriptRefusesNonCliExecution(): void
    {
        $contents = file_get_contents(PROJECT_ROOT . '/upgrade.php');
        $this->assertStringContainsString("php_sapi_name() !== 'cli'", $contents);
    }

    public function testEveryMigrationFileIsValidlyNamedAndReturnsACallable(): void
    {
        $files = glob(PROJECT_ROOT . '/install/migrations/*.php');
        $this->assertNotEmpty($files, 'expected at least one migration file to exist');

        foreach ($files as $file) {
            $version = basename($file, '.php');
            $this->assertNotFalse(
                version_compare($version, '0.0.0'),
                basename($file) . " isn't named as a valid version number"
            );

            $migration = require $file;
            $this->assertIsCallable($migration, basename($file) . ' must return a callable');
        }
    }

    public function testDatabaseVersionConstantHasAMatchingMigrationFile(): void
    {
        require_once PROJECT_ROOT . '/templates/_version.php';
        $this->assertFileExists(
            PROJECT_ROOT . '/install/migrations/' . DATABASE_VERSION . '.php',
            'DATABASE_VERSION in templates/_version.php should have a corresponding install/migrations/ file'
        );
    }
}
