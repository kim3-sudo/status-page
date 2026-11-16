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
    private ?string $lastError = null;

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

    /**
     * All rows. Boolean columns come back as 1 or 0 on both engines.
     * @return array<int, array<string, mixed>>
     */
    public function all(string $sql, array $params = []): array
    {
        $stmt = $this->run($sql, $params);
        $rows = $stmt->fetchAll();
        $boolColumns = $this->boolColumns($stmt);
        if ($boolColumns === []) {
            return $rows;
        }
        foreach ($rows as $i => $row) {
            $rows[$i] = $this->castBooleans($row, $boolColumns);
        }
        return $rows;
    }

    /** @return array<string, mixed>|null */
    public function one(string $sql, array $params = []): ?array
    {
        $rows = $this->all($sql, $params);
        return $rows[0] ?? null;
    }

    /** First column of the first row, or null when there are no rows. */
    public function value(string $sql, array $params = []): mixed
    {
        $stmt = $this->run($sql, $params);
        // fetch() rather than fetchColumn(): a boolean false column value is also false.
        $row = $stmt->fetch(PDO::FETCH_NUM);
        if ($row === false) {
            return null;
        }
        $value = $row[0];
        $meta = $stmt->columnCount() > 0 ? $stmt->getColumnMeta(0) : false;
        return ($meta !== false && ($meta['native_type'] ?? '') === 'bool') ? $this->boolToInt($value) : $value;
    }

    /** Runs a write statement and returns the number of affected rows. */
    public function execute(string $sql, array $params = []): int
    {
        return $this->run($sql, $params)->rowCount();
    }

    /**
     * Like execute(), but returns false instead of throwing on a database error.
     * The error text is kept for lastError(). Use it where a page reports failure
     * to the user and carries on.
     */
    public function tryExecute(string $sql, array $params = []): bool
    {
        $this->lastError = null;
        try {
            $this->execute($sql, $params);
            return true;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /** Error text from the last tryExecute() that failed, or null. */
    public function lastError(): ?string
    {
        return $this->lastError;
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
        // Bind PHP bools as 1/0: PDO turns false into '', which PostgreSQL rejects for boolean columns.
        $stmt->execute(array_map(
            fn (mixed $p): mixed => is_bool($p) ? (int)$p : $p,
            $params
        ));
        return $stmt;
    }

    /**
     * Names of the boolean columns in a result. Only PostgreSQL reports them as
     * boolean. MySQL BOOLEAN is TINYINT(1), which already comes back as an int.
     * @return array<string, true>
     */
    private function boolColumns(PDOStatement $stmt): array
    {
        if ($this->driver !== 'pgsql') {
            return [];
        }
        $names = [];
        for ($i = 0, $n = $stmt->columnCount(); $i < $n; $i++) {
            $meta = $stmt->getColumnMeta($i);
            if (($meta['native_type'] ?? '') === 'bool') {
                $names[$meta['name']] = true;
            }
        }
        return $names;
    }

    /** @param array<string, true> $boolColumns */
    private function castBooleans(array $row, array $boolColumns): array
    {
        foreach (array_keys($boolColumns) as $name) {
            if (array_key_exists($name, $row)) {
                $row[$name] = $this->boolToInt($row[$name]);
            }
        }
        return $row;
    }

    /** Accepts PostgreSQL's 't'/'f' text or a PHP bool, and returns 1 or 0. */
    private function boolToInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }
        return ($value === true || $value === 't' || $value === '1' || $value === 1) ? 1 : 0;
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
