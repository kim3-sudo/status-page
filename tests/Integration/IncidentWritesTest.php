<?php

namespace Tests\Integration;

use AuditException;
use Db;
use DateTimeZone;
use IncidentValidationException;
use PHPUnit\Framework\TestCase;
use SiteSettings;

/**
 * Exercises the incident write path against a live server. Select the engine with
 * TEST_DB_DRIVER=mysql or TEST_DB_DRIVER=pgsql, as in the other integration tests.
 * The database named in the environment is wiped.
 */
final class IncidentWritesTest extends TestCase
{
    private const NOW = 1782928800;          // 2026-07-01 18:00:00 UTC
    private ?Db $db = null;
    private string $driver = '';
    private DateTimeZone $zone;

    protected function setUp(): void
    {
        $driver = getenv('TEST_DB_DRIVER') ?: '';
        if ($driver === '') {
            $this->markTestSkipped('TEST_DB_DRIVER not set; skipping incident write tests.');
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
        $this->zone = new DateTimeZone('America/New_York');

        $this->db->runScript((string)file_get_contents(PROJECT_ROOT . '/install/schema/' . $driver . '.sql'));
        $this->db->execute("INSERT INTO servicegroups (servicegroup_id, servicegroup_name) VALUES (1, 'Core')");
        $this->db->execute("INSERT INTO services (service_id, servicegroup_id, service_name, service_status_short) VALUES (1, 1, 'Mail', 'OPE'), (2, 1, 'Web', 'OPE')");
        SiteSettings::clear();
    }

    protected function tearDown(): void
    {
        if ($this->db === null) {
            return;
        }
        $this->removeLogBlocker();
        $this->db->runScript($this->driver === 'mysql'
            ? 'SET FOREIGN_KEY_CHECKS = 0; DROP TABLE IF EXISTS incident_service, incident_update, incident, services, servicegroups, service_status, incident_status, pes, settings, apikeys, users, log; SET FOREIGN_KEY_CHECKS = 1;'
            : 'DROP TABLE IF EXISTS incident_service, incident_update, incident, services, servicegroups, service_status, incident_status, pes, settings, apikeys, users, log CASCADE;');
        SiteSettings::clear();
    }

    // --- create ----------------------------------------------------------

    public function testCreateIncidentWritesEverythingTogether(): void
    {
        $id = createIncident($this->db, [
            'title' => 'Mail is down',
            'status' => 'INV',
            'update' => '<p>Investigating delivery.</p>',
            'severity' => 'MAJ',
            'services' => ['1', 2],
            'start' => '2026-07-01T12:00',                // 12:00 in New York
        ], $this->zone, 1, self::NOW);

        $incident = $this->db->one('SELECT * FROM incident WHERE incident_id = ?', [$id]);
        $this->assertSame('Mail is down', $incident['incident_description']);
        $this->assertSame('INV', $incident['incident_status_short']);
        $this->assertSame('1,2', $incident['incident_describes_ids'], 'legacy column still written');
        $this->assertSame('2026-07-01', substr((string)$incident['incident_date'], 0, 10));

        $update = $this->db->one('SELECT incident_update_timestamp, incident_update_utc FROM incident_update WHERE incident_update_incident_id = ?', [$id]);
        $this->assertSame('2026-07-01 16:00:00', substr((string)$update['incident_update_timestamp'], 0, 19), 'stored in UTC');
        $this->assertTrue(dbBool($update['incident_update_utc']));

        $this->assertSame(
            [['service_id' => 1, 'severity' => 'MAJ'], ['service_id' => 2, 'severity' => 'MAJ']],
            array_map(fn (array $r) => ['service_id' => (int)$r['service_id'], 'severity' => $r['severity']],
                $this->db->all('SELECT service_id, severity FROM incident_service WHERE incident_id = ? ORDER BY service_id', [$id]))
        );
        $this->assertSame('MAJ', $this->db->value('SELECT service_status_short FROM services WHERE service_id = 1'));
    }

    public function testSanitizesTheUpdateBodyOnWrite(): void
    {
        $id = createIncident($this->db, [
            'title' => 'Slow pages',
            'status' => 'INV',
            'update' => '<p>Looking into it.</p><script>steal()</script>',
            'severity' => 'MIN',
            'services' => [2],
        ], $this->zone, 1, self::NOW);

        $body = (string)$this->db->value('SELECT incident_update_description FROM incident_update WHERE incident_update_incident_id = ?', [$id]);
        $this->assertStringContainsString('Looking into it.', $body);
        $this->assertStringNotContainsString('<script', $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidCreates')]
    public function testInvalidInputIsRejectedBeforeAnyWrite(array $override): void
    {
        $base = ['title' => 'Mail', 'status' => 'INV', 'update' => 'Text', 'severity' => 'MAJ', 'services' => [1]];
        try {
            createIncident($this->db, array_merge($base, $override), $this->zone, 1, self::NOW);
            $this->fail('expected a validation error');
        } catch (IncidentValidationException $e) {
            $this->assertNotSame('', $e->getMessage());
        }
        $this->assertSame(0, (int)$this->db->value('SELECT COUNT(*) FROM incident'));
        $this->assertSame(0, (int)$this->db->value('SELECT COUNT(*) FROM incident_service'));
    }

    public static function invalidCreates(): array
    {
        return [
            'unknown service' => [['services' => [99]]],
            'no services' => [['services' => []]],
            'severity OPE' => [['severity' => 'OPE']],
            'severity PLA' => [['severity' => 'PLA']],
            'unknown status' => [['status' => 'NOPE']],
            'empty title' => [['title' => '   ']],
            'title too long' => [['title' => str_repeat('x', 256)]],
            'empty update' => [['update' => '<p> </p>']],
            'bad start time' => [['start' => 'not a time']],
            'too many services for the legacy column' => [['services' => range(1000000001, 1000000012)]],
        ];
    }

    // --- add update ------------------------------------------------------

    public function testNewestUpdateSetsTheIncidentStatus(): void
    {
        $id = createIncident($this->db, ['title' => 'Web slow', 'status' => 'INV', 'update' => 'Checking', 'severity' => 'MIN', 'services' => [2]], $this->zone, 1, self::NOW);

        addIncidentUpdate($this->db, $id, ['status' => 'RES', 'update' => 'Fixed'], $this->zone, 1, self::NOW + 3600);

        $this->assertSame('RES', $this->db->value('SELECT incident_status_short FROM incident WHERE incident_id = ?', [$id]));
        $this->assertSame('OPE', $this->db->value('SELECT service_status_short FROM services WHERE service_id = 2'));
    }

    public function testBackdatedUpdateDoesNotChangeTheCurrentStatus(): void
    {
        $id = createIncident($this->db, ['title' => 'Web slow', 'status' => 'INV', 'update' => 'Checking', 'severity' => 'MIN', 'services' => [2]], $this->zone, 1, self::NOW);
        addIncidentUpdate($this->db, $id, ['status' => 'RES', 'update' => 'Fixed'], $this->zone, 1, self::NOW + 3600);

        // An earlier update added afterwards must not reopen the incident.
        addIncidentUpdate($this->db, $id, ['status' => 'INV', 'update' => 'Late note', 'timestamp' => '2026-07-01T13:30'], $this->zone, 1, self::NOW + 7200);

        $this->assertSame('RES', $this->db->value('SELECT incident_status_short FROM incident WHERE incident_id = ?', [$id]));
    }

    public function testUpdateToMissingIncidentIsRejected(): void
    {
        $this->expectException(IncidentValidationException::class);
        addIncidentUpdate($this->db, 404, ['status' => 'RES', 'update' => 'x'], $this->zone, 1, self::NOW);
    }

    // --- maintenance -----------------------------------------------------

    public function testScheduledMaintenanceIsNotCurrentBeforeItStarts(): void
    {
        scheduleMaintenance($this->db, [
            'title' => 'Mail upgrade',
            'message' => 'Upgrade complete.',
            'start' => '2026-07-02T09:00',
            'end' => '2026-07-02T11:00',
            'services' => [1],
        ], $this->zone, 1, self::NOW);

        $inputs = loadStatusInputs($this->db, $this->zone);
        $this->assertSame('OPE', buildStatusSnapshot($inputs, self::NOW, 0)['services'][1]['status']);
        // Inside the window it shows as planned maintenance, without any stored change.
        $during = gmmktime(14, 0, 0, 7, 2, 2026);   // 10:00 in New York, inside 09:00-11:00
        $this->assertSame('PLA', buildStatusSnapshot($inputs, $during, 0)['services'][1]['status']);
    }

    public function testMaintenanceEndMustFollowStart(): void
    {
        $this->expectException(IncidentValidationException::class);
        scheduleMaintenance($this->db, [
            'title' => 'Backwards', 'message' => 'x', 'start' => '2026-07-02T11:00', 'end' => '2026-07-02T09:00', 'services' => [1],
        ], $this->zone, 1, self::NOW);
    }

    // --- audit and transactions ------------------------------------------

    public function testStrictAuditFailureInsideTheTransactionRollsEverythingBack(): void
    {
        $this->blockLogEntries("Created incident%");

        try {
            createIncident($this->db, ['title' => 'Mail', 'status' => 'INV', 'update' => 'x', 'severity' => 'MAJ', 'services' => [1]], $this->zone, 1, self::NOW);
            $this->fail('strict audit failure should abort the write');
        } catch (AuditException $e) {
            $this->assertStringContainsString('audit entry not written', $e->getMessage());
        }

        $this->assertSame(0, (int)$this->db->value('SELECT COUNT(*) FROM incident'), 'no incident written');
        $this->assertSame(0, (int)$this->db->value('SELECT COUNT(*) FROM incident_update'), 'no update written');
        $this->assertSame(0, (int)$this->db->value('SELECT COUNT(*) FROM incident_service'), 'no service links written');
        $this->assertSame('OPE', $this->db->value('SELECT service_status_short FROM services WHERE service_id = 1'), 'service status untouched');
        $this->assertSame(1, (int)$this->db->value("SELECT COUNT(*) FROM log WHERE log_entry LIKE 'Requested incident%'"), 'the attempt is still recorded');
    }

    public function testSoftAuditFailureLetsTheWriteComplete(): void
    {
        $this->db->upsert('settings', ['setting_key' => 'audit_policy', 'setting_value' => 'soft'], ['setting_key']);
        SiteSettings::clear();
        $this->blockLogEntries("Created incident%");

        $id = createIncident($this->db, ['title' => 'Mail', 'status' => 'INV', 'update' => 'x', 'severity' => 'MAJ', 'services' => [1]], $this->zone, 1, self::NOW);

        $this->assertGreaterThan(0, $id);
        $this->assertSame(1, (int)$this->db->value('SELECT COUNT(*) FROM incident'));
    }

    /** Makes the log refuse rows whose text matches $pattern (SQL LIKE), on both engines. */
    private function blockLogEntries(string $pattern): void
    {
        if ($this->driver === 'mysql') {
            $this->db->runScript(
                "CREATE TRIGGER statuspage_block_log BEFORE INSERT ON log FOR EACH ROW
                 BEGIN
                   IF NEW.log_entry LIKE '$pattern' THEN
                     SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'blocked by test';
                   END IF;
                 END"
            );
        } else {
            $this->db->runScript(
                "CREATE OR REPLACE FUNCTION statuspage_block_log() RETURNS trigger AS \$\$
                 BEGIN
                   IF NEW.log_entry LIKE '$pattern' THEN
                     RAISE EXCEPTION 'blocked by test';
                   END IF;
                   RETURN NEW;
                 END \$\$ LANGUAGE plpgsql;
                 CREATE TRIGGER statuspage_block_log BEFORE INSERT ON log FOR EACH ROW EXECUTE FUNCTION statuspage_block_log();"
            );
        }
    }

    private function removeLogBlocker(): void
    {
        if ($this->driver === 'mysql') {
            $this->db->runScript('DROP TRIGGER IF EXISTS statuspage_block_log');
        } else {
            $this->db->runScript('DROP FUNCTION IF EXISTS statuspage_block_log() CASCADE');
        }
    }
}
