<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Audit logging policy.
//
// The `audit_policy` setting chooses what happens when an audit entry cannot be
// written:
//   strict (default) - the audit entry is required. The request fails with
//                      AuditException, and lib/bootstrap.php responds with a 503 that
//                      says the action was not performed.
//   soft             - the request continues. The failure is written to the PHP
//                      error log.
// Missing, unreadable or unknown values are treated as strict.
//
// Limitation: strict guarantees that an entry exists before the action only for
// entries written before the action. Entries written after a successful database
// change cannot undo that change. Step 7 moves those writes into transactions.

// Requires getSetting() and the Db handle, both provided by lib/db_helpers.php.

/** Raised when strict auditing is on and an audit entry cannot be written. */
final class AuditException extends RuntimeException
{
}

const AUDIT_POLICY_SETTING = 'audit_policy';
const AUDIT_POLICY_STRICT = 'strict';
const AUDIT_POLICY_SOFT = 'soft';

/** Pure: what to do when an audit write fails under $policy ('throw' or 'log'). */
function auditFailureAction(string $policy): string
{
    return $policy === AUDIT_POLICY_SOFT ? 'log' : 'throw';
}

/** Inserts one row into the log table. Throws on failure, so callers must catch. */
function auditInsert(Db $db, string $entry, $uid, string $type): void
{
    $db->execute(
        'INSERT INTO log (log_entry, log_user_id, log_type) VALUES (?, ?, ?)',
        [$entry, (int)$uid, $type]
    );
}

/** The active policy. Anything other than exactly 'soft' is strict. */
function auditPolicy(Db $db): string
{
    try {
        return getSetting($db, AUDIT_POLICY_SETTING) === AUDIT_POLICY_SOFT
            ? AUDIT_POLICY_SOFT
            : AUDIT_POLICY_STRICT;
    } catch (Throwable $e) {
        return AUDIT_POLICY_STRICT;
    }
}

/**
 * Writes one audit entry and returns true on success. On failure it throws
 * AuditException under strict policy, or logs and returns false under soft policy.
 */
function auditWrite(Db $db, string $entry, $uid, string $type): bool
{
    $entry = substr($entry, 0, 139);
    try {
        // Inside a transaction, a failed insert must not abort the rest of it (PostgreSQL does).
        $db->savepoint(fn () => auditInsert($db, $entry, $uid, $type));
        return true;
    } catch (Throwable $e) {
        $reason = $e->getMessage();
    }

    $message = "audit entry not written (type $type): $entry [$reason]";
    if (auditFailureAction(auditPolicy($db)) === 'throw') {
        throw new AuditException($message);
    }
    error_log('status-page: ' . $message);
    return false;
}

/**
 * Validates a settings write before it happens. Returns an error message, or null
 * when the write may proceed. Only superusers may change the audit policy, and
 * only to 'strict' or 'soft'.
 *
 * @param array<string, mixed> $session
 */
function settingWriteError(string $key, string $value, array $session): ?string
{
    if ($key !== AUDIT_POLICY_SETTING) {
        return null;
    }
    if ((int)($session['suflag'] ?? 0) !== 1) {
        return 'Only superusers can change the audit policy.';
    }
    if (!in_array($value, [AUDIT_POLICY_STRICT, AUDIT_POLICY_SOFT], true)) {
        return 'Audit policy must be strict or soft.';
    }
    return null;
}

const LOG_RETENTION_SETTING = 'log_retention_days';
const LOG_RETENTION_DEFAULT_DAYS = 90;

/**
 * Pure. How many days of audit log to keep, from the setting's value. Null means keep
 * everything ('0'). A blank or non-numeric value falls back to the default.
 */
function logRetentionDays(string $value): ?int
{
    $value = trim($value);
    if ($value === '' || !ctype_digit($value)) {
        return LOG_RETENTION_DEFAULT_DAYS;
    }
    $days = (int)$value;
    return $days === 0 ? null : $days;
}

/**
 * Deletes audit entries older than the retention period. Returns the number of rows
 * removed. Entries are stamped in UTC by the database, so the cutoff is computed in UTC.
 */
function pruneAuditLog(Db $db, int $nowEpoch): int
{
    $days = logRetentionDays(getSetting($db, LOG_RETENTION_SETTING));
    if ($days === null) {
        return 0;
    }
    $cutoff = gmdate('Y-m-d H:i:s', $nowEpoch - $days * 86400);
    return $db->execute('DELETE FROM log WHERE log_timestamp < ?', [$cutoff]);
}
