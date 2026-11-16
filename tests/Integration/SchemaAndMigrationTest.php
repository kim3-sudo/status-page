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

    // --- status read and write paths ------------------------------------

    public function testLoadStatusInputsReadsMembershipAndConvertsTimes(): void
    {
        $this->db->execute("INSERT INTO servicegroups (servicegroup_id, servicegroup_name) VALUES (1, 'Core')");
        $this->db->execute("INSERT INTO services (service_id, servicegroup_id, service_name, service_status_short) VALUES (1, 1, 'Mail', 'MAJ')");
        $this->db->execute("INSERT INTO incident (incident_id, incident_description, incident_status_short) VALUES (1, 'Mail down', 'INV')");
        $this->db->execute("INSERT INTO incident_service (incident_id, service_id, severity) VALUES (1, 1, 'MAJ')");
        // Written as UTC (flag set): 12:00 UTC.
        $this->db->execute("INSERT INTO incident_update (incident_update_incident_id, incident_update_status_short, incident_update_description, incident_update_timestamp, incident_update_utc) VALUES (1, 'INV', 'started', '2026-07-01 12:00:00', ?)", [1]);
        // Site-local legacy row: 13:00 in New York (EDT), which is 17:00 UTC.
        $this->db->execute("INSERT INTO incident_update (incident_update_incident_id, incident_update_status_short, incident_update_description, incident_update_timestamp, incident_update_utc) VALUES (1, 'RES', 'done', '2026-07-01 13:00:00', ?)", [0]);

        $inputs = loadStatusInputs($this->db, new \DateTimeZone('America/New_York'));

        $this->assertSame([1 => 'MAJ'], $inputs['incidents'][0]['services']);
        $updates = $inputs['incidents'][0]['updates'];
        $byStatus = array_column($updates, 'ts', 'status');
        $this->assertSame(gmmktime(12, 0, 0, 7, 1, 2026), $byStatus['INV']);
        $this->assertSame(gmmktime(17, 0, 0, 7, 1, 2026), $byStatus['RES']);
        $this->assertSame('Mail', $inputs['services'][0]['name']);
    }

    // --- API keys: migration 0.3.1 --------------------------------------

    public function testFreshSchemaStoresHashesNotKeys(): void
    {
        $this->assertTrue($this->db->hasColumn('apikeys', 'apikeys_key_hash'));
        $this->assertFalse($this->db->hasColumn('apikeys', 'apikeys_authkey'));
        $this->assertTrue($this->db->hasIndex('apikeys', 'apikeys_key_hash_unique'));
    }

    public function testMigration031HashesExistingKeysAndDropsPlaintext(): void
    {
        $this->simulateVersion030ApiKeys();
        $this->db->execute("INSERT INTO users (user_id, user_email) VALUES (1, 'owner@example.test')");
        $this->db->execute("INSERT INTO apikeys (apikeys_user_id, apikeys_authkey, apikeys_nickname) VALUES (1, 'legacy-key-one', 'a'), (1, 'legacy-key-two', 'b')");

        $this->runMigration('0.3.1');

        $hashes = array_column($this->db->all('SELECT apikeys_key_hash FROM apikeys ORDER BY apikeys_id'), 'apikeys_key_hash');
        $this->assertSame([hash('sha256', 'legacy-key-one'), hash('sha256', 'legacy-key-two')], $hashes);
        $this->assertFalse($this->db->hasColumn('apikeys', 'apikeys_authkey'));
        $this->assertSame('90', $this->db->value('SELECT setting_value FROM settings WHERE setting_key = ?', ['log_retention_days']));
    }

    public function testMigration031IsIdempotent(): void
    {
        $this->simulateVersion030ApiKeys();
        $this->db->execute("INSERT INTO users (user_id, user_email) VALUES (1, 'owner@example.test')");
        $this->db->execute("INSERT INTO apikeys (apikeys_user_id, apikeys_authkey) VALUES (1, 'legacy-key-one')");
        $this->runMigration('0.3.1');
        $this->runMigration('0.3.1');

        $this->assertSame(1, (int)$this->db->value('SELECT COUNT(*) FROM apikeys'));
        $this->assertSame(hash('sha256', 'legacy-key-one'), $this->db->value('SELECT apikeys_key_hash FROM apikeys'));
    }

    public function testMigration031RefusesDuplicateKeysAndKeepsThePlaintextColumn(): void
    {
        $this->simulateVersion030ApiKeys();
        $this->db->execute("INSERT INTO users (user_id, user_email) VALUES (1, 'owner@example.test')");
        $this->db->execute("INSERT INTO apikeys (apikeys_user_id, apikeys_authkey) VALUES (1, 'same-key'), (1, 'same-key')");

        try {
            $this->runMigration('0.3.1');
            $this->fail('the duplicate keys should stop the migration');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('appear more than once', $e->getMessage());
        }
        $this->assertTrue($this->db->hasColumn('apikeys', 'apikeys_authkey'), 'nothing is dropped when the migration stops');
        $this->assertFalse($this->db->hasIndex('apikeys', 'apikeys_key_hash_unique'), 'the unique index is not created');
    }

    public function testMigration031KeepsAnAdminsRetentionChoice(): void
    {
        $this->simulateVersion030ApiKeys();
        $this->db->upsert('settings', ['setting_key' => 'log_retention_days', 'setting_value' => '30'], ['setting_key']);

        $this->runMigration('0.3.1');

        $this->assertSame('30', $this->db->value('SELECT setting_value FROM settings WHERE setting_key = ?', ['log_retention_days']));
    }

    // --- audit log retention ---------------------------------------------

    public function testPruneRemovesOnlyEntriesOlderThanTheRetentionPeriod(): void
    {
        $now = time();
        $this->insertLogEntry('old entry', $now - 100 * 86400);
        $this->insertLogEntry('recent entry', $now - 86400);
        \SiteSettings::clear();
        $this->db->upsert('settings', ['setting_key' => 'log_retention_days', 'setting_value' => '90'], ['setting_key']);

        $removed = pruneAuditLog($this->db, $now);

        $this->assertSame(1, $removed);
        $this->assertSame(['recent entry'], array_column($this->db->all('SELECT log_entry FROM log'), 'log_entry'));
    }

    public function testRetentionZeroKeepsEverything(): void
    {
        $now = time();
        $this->insertLogEntry('ancient', $now - 3650 * 86400);
        \SiteSettings::clear();
        $this->db->upsert('settings', ['setting_key' => 'log_retention_days', 'setting_value' => '0'], ['setting_key']);

        $this->assertSame(0, pruneAuditLog($this->db, $now));
        $this->assertSame(1, (int)$this->db->value('SELECT COUNT(*) FROM log'));
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

    /** Removes the 0.3.1 API key change, leaving the 0.3.0 shape: plaintext keys, no hash. */
    private function simulateVersion030ApiKeys(): void
    {
        if ($this->driver === 'mysql') {
            $this->db->runScript('DROP INDEX apikeys_key_hash_unique ON apikeys');
        } else {
            $this->db->runScript('DROP INDEX IF EXISTS apikeys_key_hash_unique');
        }
        $this->db->runScript('ALTER TABLE apikeys DROP COLUMN apikeys_key_hash');
        $this->db->runScript('ALTER TABLE apikeys ADD COLUMN apikeys_authkey VARCHAR(128) DEFAULT NULL');
        \SiteSettings::clear();
    }

    private function insertLogEntry(string $entry, int $epoch): void
    {
        $this->db->execute(
            'INSERT INTO log (log_entry, log_user_id, log_type, log_timestamp) VALUES (?, ?, ?, ?)',
            [$entry, -1, 'INFO', gmdate('Y-m-d H:i:s', $epoch)]
        );
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
