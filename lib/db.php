<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

/**
 * Database access over PDO for both supported engines:
 *   'mysql'  - MySQL or MariaDB
 *   'pgsql'  - PostgreSQL
 *
 * Rules for SQL written against this class:
 *   - Use '?' placeholders and pass values in $params. Never interpolate values.
 *   - Do not use vendor-specific functions (NOW(), CURDATE(), DATE_SUB(), instr(),
 *     concat(), ...). Compute dates in PHP and bind them. Stored and computed
 *     timestamps are UTC; convert to the site timezone only when rendering.
 *   - Table and column names interpolated by insert()/upsert() are checked against
 *     a strict identifier pattern.
 */
final class Db
{
    private PDO $pdo;
    private string $driver;

    public function __construct(
        string $driver,
        string $host,
        int $port,
        string $database,
        string $user,
        string $password
    ) {
        $dsn = match ($driver) {
            'mysql' => sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database),
            'pgsql' => sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $database),
            default => throw new InvalidArgumentException("Unsupported database driver: $driver"),
        };

        $this->pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $this->driver = $driver;

        $this->pdo->exec($driver === 'mysql' ? "SET time_zone = '+00:00'" : "SET TIME ZONE 'UTC'");
    }

    public function driver(): string
    {
        return $this->driver;
    }

    /** @return array<int, array<string, mixed>> */
    public function all(string $sql, array $params = []): array
    {
        return $this->run($sql, $params)->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function one(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /** First column of the first row, or null when there are no rows. */
    public function value(string $sql, array $params = []): mixed
    {
        $value = $this->run($sql, $params)->fetchColumn();
        return $value === false ? null : $value;
    }

    /** Runs a write statement and returns the number of affected rows. */
    public function execute(string $sql, array $params = []): int
    {
        return $this->run($sql, $params)->rowCount();
    }

    /**
     * Inserts one row and returns its generated id.
     * $idColumn is the auto-increment / serial primary key column.
     */
    public function insert(string $table, array $values, string $idColumn = 'id'): int
    {
        [$columns, $placeholders, $params] = $this->columnsAndValues($table, $values);
        $this->assertIdentifier($idColumn);

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        if ($this->driver === 'pgsql') {
            return (int)$this->value($sql . " RETURNING $idColumn", $params);
        }

        $this->execute($sql, $params);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Inserts a row, or handles the existing row whose $keys columns match.
     * $keys must be a unique key (or primary key) of $table.
     *
     * With $updateExisting true (the default) the existing row takes the new values.
     * With false it is left unchanged, which makes the call an idempotent
     * "insert if missing".
     */
    public function upsert(string $table, array $values, array $keys, bool $updateExisting = true): void
    {
        if ($keys === []) {
            throw new InvalidArgumentException('upsert() needs at least one key column');
        }
        [$columns, $placeholders, $params] = $this->columnsAndValues($table, $values);
        foreach ($keys as $key) {
            $this->assertIdentifier($key);
        }

        $updateColumns = $updateExisting ? array_values(array_diff(array_keys($values), $keys)) : [];
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        if ($this->driver === 'mysql') {
            $sql .= ' ON DUPLICATE KEY UPDATE ';
            // With nothing to update, a self-assignment keeps the existing row unchanged.
            $sql .= $updateColumns === []
                ? "{$keys[0]} = {$keys[0]}"
                : implode(', ', array_map(fn (string $c) => "$c = VALUES($c)", $updateColumns));
        } else {
            $sql .= ' ON CONFLICT (' . implode(', ', $keys) . ') ';
            $sql .= $updateColumns === []
                ? 'DO NOTHING'
                : 'DO UPDATE SET ' . implode(', ', array_map(fn (string $c) => "$c = EXCLUDED.$c", $updateColumns));
        }

        $this->execute($sql, $params);
    }

    /**
     * Runs a script of several statements with no parameters, such as a schema
     * file. Statements must be written for this driver. Use only for trusted,
     * repository-owned SQL, never for user input.
     */
    public function runScript(string $sql): void
    {
        $this->pdo->exec($sql);
    }

    /** True when $table has a column named $column in the current database/schema. */
    public function hasColumn(string $table, string $column): bool
    {
        $this->assertIdentifier($table);
        $this->assertIdentifier($column);
        $count = $this->driver === 'mysql'
            ? $this->value(
                'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$table, $column]
            )
            : $this->value(
                'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = ? AND column_name = ?',
                [$table, $column]
            );
        return (int)$count > 0;
    }

    /** True when $table has an index named $index in the current database/schema. */
    public function hasIndex(string $table, string $index): bool
    {
        $this->assertIdentifier($table);
        $this->assertIdentifier($index);
        $count = $this->driver === 'mysql'
            ? $this->value(
                'SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$table, $index]
            )
            : $this->value(
                'SELECT COUNT(*) FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ?',
                [$table, $index]
            );
        return (int)$count > 0;
    }

    /**
     * Runs $work inside a transaction. Commits when it returns, rolls back and
     * rethrows when it throws. A nested call joins the outer transaction (no
     * savepoints). Do not run schema changes inside it: MySQL commits implicitly
     * on DDL.
     */
    public function transaction(callable $work): mixed
    {
        if ($this->pdo->inTransaction()) {
            return $work($this);
        }

        $this->pdo->beginTransaction();
        try {
            $result = $work($this);
            $this->pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * Runs $work inside a savepoint when called within a transaction. If $work
     * throws, only the savepoint's changes are undone and the outer transaction stays
     * usable. PostgreSQL aborts the whole transaction on any error otherwise.
     * Outside a transaction it simply runs $work.
     */
    public function savepoint(callable $work): mixed
    {
        if (!$this->pdo->inTransaction()) {
            return $work($this);
        }

        $name = 'sp_' . bin2hex(random_bytes(6));
        $this->pdo->exec("SAVEPOINT $name");
        try {
            $result = $work($this);
            $this->pdo->exec("RELEASE SAVEPOINT $name");
            return $result;
        } catch (Throwable $e) {
            $this->pdo->exec("ROLLBACK TO SAVEPOINT $name");
            throw $e;
        }
    }

    private function run(string $sql, array $params): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** @return array{0: string, 1: string, 2: array<int, mixed>} */
    private function columnsAndValues(string $table, array $values): array
    {
        if ($values === []) {
            throw new InvalidArgumentException('insert/upsert needs at least one column');
        }
        $this->assertIdentifier($table);
        $columns = array_keys($values);
        foreach ($columns as $column) {
            $this->assertIdentifier($column);
        }

        return [
            implode(', ', $columns),
            implode(', ', array_fill(0, count($columns), '?')),
            array_values($values),
        ];
    }

    private function assertIdentifier(string $name): void
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
            throw new InvalidArgumentException("Invalid SQL identifier: $name");
        }
    }
}
