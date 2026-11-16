<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Shared bootstrap for the JSON API endpoints (api/*). Emits no HTML.
require_once __DIR__ . '/bootstrap.php';

/**
 * Authenticates the current API request. Returns the owning user id, or null when
 * the request has no key or the key does not match. Writes the audit entries for the
 * attempt. Callers respond with apiUnauthorized() on null.
 */
function apiAuthenticateRequest(Db $db, string $context): ?int {
  $presented = apiKeyFromRequest($_SERVER, $_GET, $_POST);
  if ($presented === null) {
    writeToLog($db, 'No authentication key provided', -1);
    return null;
  }
  if ($presented['source'] === 'query') {
    writeToLog($db, "Deprecated: $context received its key in the URL. Send it in the Authorization header instead.", -1, 'WARN');
  }
  return authenticateApiKey($db, $presented['key'], $context);
}

/**
 * Looks up a key by its hash. Returns the owning user id when exactly one key matches,
 * or null otherwise.
 */
function authenticateApiKey(Db $db, string $authkey, string $context): ?int {
  writeToLog($db, "Matching authentication keys for $context", -1);
  $rows = $db->all('SELECT apikeys_user_id FROM apikeys WHERE apikeys_key_hash = ?', [apiKeyHash($authkey)]);

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
