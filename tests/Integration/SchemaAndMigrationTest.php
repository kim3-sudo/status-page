<?php

namespace Tests\Integration;

use Db;
use PHPUnit\Framework\TestCase;

/**
 * Applies the real schema files and the real 0.2.0 / 0.3.0 migrations to a live
 * server. Select the engine with TEST_DB_DRIVER=mysql or TEST_DB_DRIVER=pgsql, as in
 * DbIntegrationTest. The database named in the environment is wiped by these tests,
 * so point them only at a dedicated test database.
 */
final class SchemaAndMigrationTest extends TestCase
{
    private ?Db $db = null;
    private string $driver = '';

    protected function setUp(): void
    {
        $driver = getenv('TEST_DB_DRIVER') ?: '';
        if ($driver === '') {
            $this->markTestSkipped('TEST_DB_DRIVER not set; skipping schema integration tests.');
        }
        $this->driver = $driver;
        $prefix = $driver === 'mysql' ? 'TEST_MYSQL_' : 'TEST_PGSQL_';
        $this->db = new Db(
            $driver,
            (string)getenv($prefix . 'HOST'),
            $driver === 'mysql' ? 3306 : 5432,
            (string)getenv($prefix . 'NAME'),
            (string)getenv($prefix . 'USER'),
            (string)getenv($prefix . 'PASS')
        );
        $this->applyFreshSchema();
    }

    protected function tearDown(): void
    {
        // Leave the database empty so the next test class starts clean.
        if ($this->db !== null) {
            $this->db->runScript($this->driver === 'mysql'
                ? 'SET FOREIGN_KEY_CHECKS = 0; DROP TABLE IF EXISTS incident_service, incident_update, incident, services, servicegroups, service_status, incident_status, pes, settings, apikeys, users, log; SET FOREIGN_KEY_CHECKS = 1;'
                : 'DROP TABLE IF EXISTS incident_service, incident_update, incident, services, servicegroups, service_status, incident_status, pes, settings, apikeys, users, log CASCADE;');
        }
    }

    // --- fresh schema ----------------------------------------------------

    public function testFreshSchemaHasTheVersion030Shape(): void
    {
        $this->assertTrue($this->db->hasColumn('incident_update', 'incident_update_utc'));
        $this->assertTrue($this->db->hasColumn('incident_service', 'severity'));
        $this->assertTrue($this->db->hasIndex('incident_update', 'incident_update_incident_time'));
    }

    public function testFreshSchemaSeedsStatusCodes(): void
    {
        $this->assertSame(5, (int)$this->db->value('SELECT COUNT(*) FROM service_status'));
        $this->assertSame(5, (int)$this->db->value('SELECT COUNT(*) FROM incident_status'));
    }

    public function testFreshSchemaForeignKeysRejectUnknownServiceStatus(): void
    {
        $this->expectException(\Throwable::class);
        $this->db->execute("INSERT INTO services (service_name, service_status_short) VALUES ('x', 'ZZZ')");
    }

    // --- migration 0.3.0 from a 0.2.x state ------------------------------

    public function testMigrationBackfillsIncidentServiceFromCsv(): void
    {
        $this->simulateVersion020();
        $this->seedLegacyIncidents();

        $this->runMigration('0.3.0');

        $rows = $this->db->all(
            'SELECT incident_id, service_id, severity FROM incident_service ORDER BY incident_id, service_id'
        );
        $this->assertSame(
            [
                ['incident_id' => 1, 'service_id' => 1, 'severity' => 'MIN'],   // resolved: MIN
                ['incident_id' => 1, 'service_id' => 2, 'severity' => 'MIN'],
                ['incident_id' => 2, 'service_id' => 1, 'severity' => 'MAJ'],   // open: current service status
                ['incident_id' => 3, 'service_id' => 3, 'severity' => 'MIN'],   // open, but service is operational
            ],
            array_map(fn (array $r) => ['incident_id' => (int)$r['incident_id'], 'service_id' => (int)$r['service_id'], 'severity' => $r['severity']], $rows)
        );
    }

    public function testMigrationAddsUtcFlagDefaultingToSiteLocal(): void
    {
        $this->simulateVersion020();
        $this->seedLegacyIncidents();

        $this->runMigration('0.3.0');

        $flags = $this->db->all('SELECT incident_update_utc FROM incident_update');
        $this->assertNotEmpty($flags);
        foreach ($flags as $row) {
            $this->assertFalse((bool)$row['incident_update_utc'], 'existing rows are site-local');
        }
    }

    public function testMigrationIsIdempotentAndKeepsEditedSeverities(): void
    {
        $this->simulateVersion020();
        $this->seedLegacyIncidents();
        $this->runMigration('0.3.0');

        // An admin-edited row must survive a re-run.
        $this->db->execute('UPDATE incident_service SET severity = ? WHERE incident_id = 1 AND service_id = 1', ['DEG']);
        $this->runMigration('0.3.0');

        $this->assertSame(4, (int)$this->db->value('SELECT COUNT(*) FROM incident_service'));
        $this->assertSame('DEG', $this->db->value(
            'SELECT severity FROM incident_service WHERE incident_id = ? AND service_id = ?', [1, 1]
        ));
    }

    public function testMigrationSeedsAuditPolicyWithoutOverwritingAnAdminChoice(): void
    {
        $this->simulateVersion020();
        $this->seedLegacyIncidents();
        $this->db->upsert('settings', ['setting_key' => 'audit_policy', 'setting_value' => 'soft'], ['setting_key']);

        $this->runMigration('0.3.0');

        $this->assertSame('soft', $this->db->value(
            'SELECT setting_value FROM settings WHERE setting_key = ?', ['audit_policy']
        ));
    }

    public function testMigrationSeedsAuditPolicyWhenMissing(): void
    {
        $this->simulateVersion020();
        $this->seedLegacyIncidents();

        $this->runMigration('0.3.0');

        $this->assertSame('strict', $this->db->value(
            'SELECT setting_value FROM settings WHERE setting_key = ?', ['audit_policy']
        ));
    }

    public function testMigrationConvertsLegacyCharsetOnMysql(): void
    {
        if ($this->driver !== 'mysql') {
            $this->markTestSkipped('Character-set conversion applies to MySQL/MariaDB only.');
        }
        $this->simulateVersion020();
        $this->db->runScript('ALTER TABLE pes CONVERT TO CHARACTER SET latin1');
        $this->runMigration('0.3.0');

        $collation = (string)$this->db->value(
            'SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            ['pes']
        );
        $this->assertStringStartsWith('utf8mb4', $collation);
    }

    public function testMigration020SeedsSamlSettingsWithoutOverwriting(): void
    {
        $this->db->upsert('settings', ['setting_key' => 'enable_sso', 'setting_value' => '1'], ['setting_key']);

        $migrate = require PROJECT_ROOT . '/install/migrations/0.2.0.php';
        $migrate($this->db);
        $migrate($this->db); // re-run

        $this->assertSame('1', $this->db->value('SELECT setting_value FROM settings WHERE setting_key = ?', ['enable_sso']));
        $this->assertSame(8, (int)$this->db->value(
            "SELECT COUNT(*) FROM settings WHERE setting_key IN ('enable_sso','entity_id','name_id_format','saml_email_attribute','service_provider_base_url','slo_service','sso_service','x509cert')"
        ));
    }

    // --- helpers ---------------------------------------------------------

    private function applyFreshSchema(): void
    {
        $this->db->runScript((string)file_get_contents(PROJECT_ROOT . '/install/schema/' . $this->driver . '.sql'));
    }

    /** Removes the 0.3.0 additions, leaving the shape a 0.2.x install has. */
    private function simulateVersion020(): void
    {
        $this->db->runScript('DROP TABLE IF EXISTS incident_service');
        if ($this->driver === 'mysql') {
            $this->db->runScript('DROP INDEX incident_update_incident_time ON incident_update');
        } else {
            $this->db->runScript('DROP INDEX IF EXISTS incident_update_incident_time');
        }
        $this->db->runScript('ALTER TABLE incident_update DROP COLUMN incident_update_utc');
    }

    /** Services 1 (MAJ), 2 and 3 (OPE). Incident 1 resolved, 2 open, 3 open. */
    private function seedLegacyIncidents(): void
    {
        $this->db->execute("INSERT INTO services (service_id, service_name, service_status_short) VALUES (1, 'a', 'MAJ'), (2, 'b', 'OPE'), (3, 'c', 'OPE')");
        $this->db->execute("INSERT INTO incident (incident_id, incident_description, incident_status_short, incident_describes_ids) VALUES
            (1, 'resolved', 'RES', '1,2'),
            (2, 'open major', 'INV', '1'),
            (3, 'open messy ids', 'INV', '3,999,abc,')");
        $this->db->execute("INSERT INTO incident_update (incident_update_status_short, incident_update_description, incident_update_incident_id) VALUES
            ('INV', 'started', 1), ('RES', 'done', 1),
            ('INV', 'started', 2),
            ('INV', 'started', 3)");
    }

    private function runMigration(string $version): void
    {
        $migrate = require PROJECT_ROOT . '/install/migrations/' . $version . '.php';
        $migrate($this->db);
    }
}
