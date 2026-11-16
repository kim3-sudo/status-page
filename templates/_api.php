<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Shared bootstrap for the JSON API endpoints (api/*). Emits no HTML.
require_once __DIR__ . '/../lib/bootstrap.php';
require_once(__DIR__ . '/_db_helpers.php');

/**
 * A short, non-reversible identifier for an API key, safe to write to the audit
 * log in place of the key itself.
 */
function apiKeyFingerprint(string $authkey): string {
  return 'key:' . substr(hash('sha256', $authkey), 0, 12);
}

/**
 * Returns the apikeys rows matching $authkey, using a prepared statement.
 * Callers check the row count: exactly one row = authenticated.
 */
function findApiKeyRows(mysqli $link, string $authkey): mysqli_result {
  $stmt = $link->prepare('SELECT apikeys_user_id, apikeys_authkey FROM apikeys WHERE apikeys_authkey = ?');
  $stmt->bind_param('s', $authkey);
  $stmt->execute();
  $result = $stmt->get_result();
  $stmt->close();
  return $result;
}
