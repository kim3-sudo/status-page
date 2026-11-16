<?php

namespace Tests\Integration;

use Db;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Runs the Db wrapper against a real server. Select the engine with
 * TEST_DB_DRIVER=mysql or TEST_DB_DRIVER=pgsql. Connection settings come from
 * TEST_MYSQL_* or TEST_PGSQL_* (HOST, USER, PASS, NAME). Without TEST_DB_DRIVER
 * the whole class is skipped, so the default test run needs no database.
 */
final class DbIntegrationTest extends TestCase
{
    private const TABLE = 'statuspage_db_smoke';

    private ?Db $db = null;
    private string $driver = '';

    protected function setUp(): void
    {
        $driver = getenv('TEST_DB_DRIVER') ?: '';
        if ($driver === '') {
            $this->markTestSkipped('TEST_DB_DRIVER not set; skipping database integration tests.');
        }
        if (!in_array($driver, ['mysql', 'pgsql'], true)) {
            throw new RuntimeException("TEST_DB_DRIVER must be 'mysql' or 'pgsql', got '$driver'.");
        }

        $prefix = $driver === 'mysql' ? 'TEST_MYSQL_' : 'TEST_PGSQL_';
        $this->driver = $driver;
        $this->db = new Db(
            $driver,
            (string)getenv($prefix . 'HOST'),
            $driver === 'mysql' ? 3306 : 5432,
            (string)getenv($prefix . 'NAME'),
            (string)getenv($prefix . 'USER'),
            (string)getenv($prefix . 'PASS')
        );

        $this->db->execute('DROP TABLE IF EXISTS ' . self::TABLE);
        $idType = $driver === 'mysql' ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'SERIAL PRIMARY KEY';
        $this->db->execute(
            'CREATE TABLE ' . self::TABLE . " (id $idType, name VARCHAR(64) NOT NULL UNIQUE, note VARCHAR(255), flag BOOLEAN)"
        );
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            $this->db->execute('DROP TABLE IF EXISTS ' . self::TABLE);
        }
    }

    public function testReportsConfiguredDriver(): void
    {
        $this->assertSame($this->driver, $this->db->driver());
    }

    public function testSessionTimezoneIsUtc(): void
    {
        if ($this->driver === 'mysql') {
            $this->assertSame('+00:00', $this->db->value('SELECT @@session.time_zone'));
        } else {
            $this->assertSame('UTC', strtoupper((string)$this->db->value('SHOW TIME ZONE')));
        }
    }

    public function testInsertReturnsGeneratedIds(): void
    {
        $first = $this->db->insert(self::TABLE, ['name' => 'alpha', 'note' => 'a']);
        $second = $this->db->insert(self::TABLE, ['name' => 'beta', 'note' => 'b']);

        $this->assertGreaterThan(0, $first);
        $this->assertGreaterThan($first, $second);
    }

    public function testOneAllAndValueBindParameters(): void
    {
        $this->db->insert(self::TABLE, ['name' => 'alpha']);
        $this->db->insert(self::TABLE, ['name' => 'beta']);

        $rows = $this->db->all('SELECT name FROM ' . self::TABLE . ' WHERE name LIKE ? ORDER BY name', ['%a']);
        $this->assertSame(['alpha', 'beta'], array_column($rows, 'name'));

        $this->assertSame(['name' => 'alpha'], $this->db->one('SELECT name FROM ' . self::TABLE . ' WHERE name = ?', ['alpha']));
        $this->assertNull($this->db->one('SELECT name FROM ' . self::TABLE . ' WHERE name = ?', ['missing']));
        $this->assertSame(2, (int)$this->db->value('SELECT COUNT(*) FROM ' . self::TABLE));
    }

    public function testParametersAreNeverInterpolatedIntoSql(): void
    {
        $this->db->insert(self::TABLE, ['name' => 'alpha']);

        $payload = "x' OR '1'='1";
        $this->assertSame([], $this->db->all('SELECT name FROM ' . self::TABLE . ' WHERE name = ?', [$payload]));
    }

    public function testExecuteReturnsAffectedRowCount(): void
    {
        $this->db->insert(self::TABLE, ['name' => 'alpha', 'note' => 'old']);
        $this->db->insert(self::TABLE, ['name' => 'beta', 'note' => 'old']);

        $affected = $this->db->execute('UPDATE ' . self::TABLE . ' SET note = ? WHERE note = ?', ['new', 'old']);
        $this->assertSame(2, $affected);
    }

    public function testTransactionCommitsOnSuccess(): void
    {
        $this->db->transaction(function (Db $db) {
            $db->insert(self::TABLE, ['name' => 'alpha']);
            $db->insert(self::TABLE, ['name' => 'beta']);
        });

        $this->assertSame(2, (int)$this->db->value('SELECT COUNT(*) FROM ' . self::TABLE));
    }

    public function testTransactionRollsBackAndRethrowsOnFailure(): void
    {
        try {
            $this->db->transaction(function (Db $db) {
                $db->insert(self::TABLE, ['name' => 'alpha']);
                throw new RuntimeException('boom');
            });
            $this->fail('transaction() should rethrow the exception');
        } catch (RuntimeException $e) {
            $this->assertSame('boom', $e->getMessage());
        }

        $this->assertSame(0, (int)$this->db->value('SELECT COUNT(*) FROM ' . self::TABLE));
    }

    public function testUpsertInsertsThenUpdatesOnKeyConflict(): void
    {
        $this->db->upsert(self::TABLE, ['name' => 'alpha', 'note' => 'first'], ['name']);
        $this->db->upsert(self::TABLE, ['name' => 'alpha', 'note' => 'second'], ['name']);

        $rows = $this->db->all('SELECT name, note FROM ' . self::TABLE);
        $this->assertCount(1, $rows);
        $this->assertSame('second', $rows[0]['note']);
    }

    public function testUpsertWithOnlyKeyColumnsKeepsExistingRow(): void
    {
        $this->db->upsert(self::TABLE, ['name' => 'alpha'], ['name']);
        $this->db->upsert(self::TABLE, ['name' => 'alpha'], ['name']);

        $this->assertSame(1, (int)$this->db->value('SELECT COUNT(*) FROM ' . self::TABLE));
    }

    public function testRejectsInvalidIdentifiers(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->db->insert(self::TABLE, ['name; DROP TABLE x' => 'alpha']);
    }

    public function testUtf8RoundTrip(): void
    {
        $text = "café — 日本語 🙂";
        $this->db->insert(self::TABLE, ['name' => 'unicode', 'note' => $text]);

        $this->assertSame($text, $this->db->value('SELECT note FROM ' . self::TABLE . ' WHERE name = ?', ['unicode']));
    }

    public function testBooleanColumnReadsAsTruthy(): void
    {
        // Engines return booleans differently (MySQL 0/1, PostgreSQL bool).
        // Callers must cast; Step 8 normalizes this in the Db layer.
        $this->db->insert(self::TABLE, ['name' => 'on', 'flag' => 1]);
        $this->db->insert(self::TABLE, ['name' => 'off', 'flag' => 0]);

        $this->assertTrue((bool)$this->db->value('SELECT flag FROM ' . self::TABLE . ' WHERE name = ?', ['on']));
        $this->assertFalse((bool)$this->db->value('SELECT flag FROM ' . self::TABLE . ' WHERE name = ?', ['off']));
    }
}
