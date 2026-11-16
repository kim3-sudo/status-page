<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Shared bootstrap for the JSON API endpoints (api/*). Emits no HTML.
require_once __DIR__ . '/../lib/bootstrap.php';

/**
 * A short, non-reversible identifier for an API key, safe to write to the audit
 * log in place of the key itself.
 */
function apiKeyFingerprint(string $authkey): string {
  return 'key:' . substr(hash('sha256', $authkey), 0, 12);
}

/**
 * Looks up an API key. Returns the owning user id when exactly one key matches,
 * or null otherwise. Writes the audit entries for the attempt. Callers respond
 * with apiUnauthorized() on null.
 */
function authenticateApiKey(Db $db, string $authkey, string $context): ?int {
  writeToLog($db, "Matching authentication keys for $context", -1);
  $rows = $db->all('SELECT apikeys_user_id FROM apikeys WHERE apikeys_authkey = ?', [$authkey]);

  if (count($rows) === 1) {
    $userid = (int)$rows[0]['apikeys_user_id'];
    writeToLog($db, 'Matched authentication key for user', $userid);
    return $userid;
  }

  writeToLog($db, count($rows) > 1 ? 'Ambiguous authentication keys' : 'No matching authentication key', -1);
  writeToLog($db, 'Rejected ' . apiKeyFingerprint($authkey), -1);
  return null;
}

/** Sends the standard 401 response for a rejected API request. */
function apiUnauthorized(): void {
  http_response_code(401);
  echo 'Unauthorized';
}
