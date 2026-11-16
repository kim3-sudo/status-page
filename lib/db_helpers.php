<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// No HTML output and no other side effects here. This file is included by
// contexts that must not emit HTML or do unrelated DB work (install/run.php,
// saml/index.php), in addition to the normal HTML-emitting templates/_header.php.

require_once __DIR__ . '/audit.php';

/**
 * Process-wide cache of the settings table. Loaded with one query on first use,
 * then served from memory until clear() is called after a write.
 */
final class SiteSettings
{
    private static ?array $values = null;

    /** @return array<string, string> */
    public static function load(Db $db): array
    {
        if (self::$values !== null) {
            return self::$values;
        }

        try {
            $values = [];
            foreach ($db->all('SELECT setting_key, setting_value FROM settings') as $row) {
                $values[$row['setting_key']] = $row['setting_value'] ?? '';
            }
        } catch (Throwable $e) {
            // Not cached: a later call may succeed once the table exists.
            return [];
        }

        self::$values = $values;
        return $values;
    }

    /** Drop the cache. Call after any write to the settings table. */
    public static function clear(): void
    {
        self::$values = null;
    }
}

if (!function_exists('writeToLog')) {
    /**
     * Appends an audit entry, under the site's audit policy (lib/audit.php).
     * Strict policy throws AuditException on failure. Soft policy logs the failure
     * and returns false.
     */
    function writeToLog(Db $db, $entry, $uid, $type = 'INFO'): bool {
        return auditWrite($db, $entry, $uid, $type);
    }
}

if (!function_exists('samlWriteToLog')) {
    /**
     * Same policy as writeToLog(). Under strict auditing a failed entry aborts the
     * SSO request with a 503 (see lib/bootstrap.php).
     */
    function samlWriteToLog(Db $db, $entry, $uid, $type = 'INFO'): void {
        auditWrite($db, $entry, $uid, $type);
    }
}

if (!function_exists('getSetting')) {
    function getSetting(Db $db, string $key): string {
        return SiteSettings::load($db)[$key] ?? '';
    }
}
