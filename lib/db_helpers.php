<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// No HTML output and no other side effects here. This file is included by
// contexts that must not emit HTML or do unrelated DB work (install/run.php,
// saml/index.php), in addition to the normal HTML-emitting templates/_header.php.
//
// These helpers still take the mysqli $link. Re-platforming them onto the Db
// class happens in Step 8, once the remaining mysqli callers are converted.

require_once __DIR__ . '/audit.php';

/**
 * Process-wide cache of the settings table. Loaded with one query on first use,
 * then served from memory until clear() is called after a write.
 */
final class SiteSettings
{
    private static ?array $values = null;

    /** @return array<string, string> */
    public static function load(mysqli $link): array
    {
        if (self::$values !== null) {
            return self::$values;
        }

        $result = $link->query('SELECT setting_key, setting_value FROM settings');
        if ($result === false) {
            // Not cached: a later call may succeed once the table exists.
            return [];
        }

        $values = [];
        while ($row = $result->fetch_assoc()) {
            $values[$row['setting_key']] = $row['setting_value'] ?? '';
        }
        $result->free();

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
    function writeToLog($link, $entry, $uid, $type = 'INFO'): bool {
        return auditWrite($link, $entry, $uid, $type);
    }
}

if (!function_exists('samlWriteToLog')) {
    // Same as writeToLog(), but tolerates a missing $link and swallows failures
    // instead of dying. Losing an audit entry mid-SSO-handshake shouldn't break
    // the handshake itself.
    function samlWriteToLog($link, $entry, $uid, $type = 'INFO') {
        if (!$link) {
            return;
        }
        // Same policy as writeToLog(). Under strict auditing a failed entry aborts
        // the SSO request with a 503 (see lib/bootstrap.php).
        auditWrite($link, $entry, $uid, $type);
    }
}

if (!function_exists('getSetting')) {
    function getSetting($link, $key) {
        return SiteSettings::load($link)[$key] ?? '';
    }
}
