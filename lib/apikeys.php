<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// API keys. A key is a random 128-character string shown to the admin once, when it
// is created. Only its SHA-256 hash is stored (apikeys.apikeys_key_hash), so a copy
// of the database does not contain usable keys. The key is high-entropy, so a plain
// fast hash is appropriate here, unlike a password.
//
// No database access and no output in this file, so it can be loaded anywhere.

/** The stored form of a key. Migration 0.3.1 uses this same function. */
function apiKeyHash(string $authkey): string
{
    return hash('sha256', $authkey);
}

/** A short identifier for a key, safe to write to the audit log in place of the key. */
function apiKeyFingerprint(string $authkey): string
{
    return 'key:' . substr(apiKeyHash($authkey), 0, 12);
}

/**
 * Pure. The key a request presents, and where it came from. Checked in this order:
 *
 *   bearer  - an "Authorization: Bearer <key>" header (preferred)
 *   form    - an authkey field in a POST body, as the incident API has always accepted
 *   query   - an authkey URL parameter. Deprecated: URLs are written to web server
 *             logs and browser history. Still accepted for one release.
 *
 * @param array<string, mixed> $server  $_SERVER
 * @param array<string, mixed> $get     $_GET
 * @param array<string, mixed> $post    $_POST
 * @return array{key: string, source: string}|null
 */
function apiKeyFromRequest(array $server, array $get, array $post): ?array
{
    $header = (string)($server['HTTP_AUTHORIZATION'] ?? $server['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    if (preg_match('/^Bearer\s+(\S+)\s*$/i', $header, $match) === 1) {
        return ['key' => $match[1], 'source' => 'bearer'];
    }
    if (isset($post['authkey']) && is_string($post['authkey']) && $post['authkey'] !== '') {
        return ['key' => $post['authkey'], 'source' => 'form'];
    }
    if (isset($get['authkey']) && is_string($get['authkey']) && $get['authkey'] !== '') {
        return ['key' => $get['authkey'], 'source' => 'query'];
    }
    return null;
}
