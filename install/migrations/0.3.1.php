<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Upgrades a 0.3.0 database to 0.3.1.
//
// 1. Adds apikeys.apikeys_key_hash (SHA-256 of the key, see lib/apikeys.php).
// 2. Stops if two keys are identical. Such keys cannot be told apart once hashed,
//    and the old code already refused them as ambiguous. Resolve them in the admin
//    portal, then run the upgrade again.
// 3. Fills the hash for every existing key, then adds a unique index on it.
// 4. Drops the plaintext apikeys.apikeys_authkey column. This cannot be undone:
//    after the upgrade, the code no longer reads plaintext keys. Keys that already
//    exist keep working, because their hash is computed from the stored key.
// 5. Seeds log_retention_days (90 days), if it is missing.
//
// Every step checks whether it has already run, so re-running after an
// interrupted upgrade is safe.

require_once __DIR__ . '/../../lib/apikeys.php';

return function (Db $db): void {
  if (!$db->hasColumn('apikeys', 'apikeys_key_hash')) {
    $db->runScript('ALTER TABLE apikeys ADD COLUMN apikeys_key_hash VARCHAR(64) DEFAULT NULL');
  }

  if ($db->hasColumn('apikeys', 'apikeys_authkey')) {
    // 2. Refuse before changing anything if identical plaintext keys exist.
    $duplicates = (int)$db->value(
        'SELECT COUNT(*) FROM (SELECT apikeys_authkey FROM apikeys WHERE apikeys_authkey IS NOT NULL GROUP BY apikeys_authkey HAVING COUNT(*) > 1) AS dup'
    );
    if ($duplicates > 0) {
      throw new RuntimeException(
        "$duplicates API key value(s) appear more than once in the apikeys table. Delete the extra copies in the admin portal, then run the upgrade again."
      );
    }

    // 3a. Fill the hash for each existing key.
    foreach ($db->all('SELECT apikeys_id, apikeys_authkey FROM apikeys WHERE apikeys_key_hash IS NULL AND apikeys_authkey IS NOT NULL') as $row) {
      $db->execute(
        'UPDATE apikeys SET apikeys_key_hash = ? WHERE apikeys_id = ?',
        [apiKeyHash((string)$row['apikeys_authkey']), (int)$row['apikeys_id']]
      );
    }
  }

  // 3b. Lookups go through the hash, so it must be unique.
  if (!$db->hasIndex('apikeys', 'apikeys_key_hash_unique')) {
    $db->runScript('CREATE UNIQUE INDEX apikeys_key_hash_unique ON apikeys (apikeys_key_hash)');
  }

  // 4. Remove the plaintext keys, but only once every key has a hash.
  if ($db->hasColumn('apikeys', 'apikeys_authkey')) {
    $unhashed = (int)$db->value(
        'SELECT COUNT(*) FROM apikeys WHERE apikeys_key_hash IS NULL AND apikeys_authkey IS NOT NULL'
    );
    if ($unhashed > 0) {
      throw new RuntimeException("$unhashed API key(s) have no hash yet, so the plaintext column was kept.");
    }
    $db->runScript('ALTER TABLE apikeys DROP COLUMN apikeys_authkey');
  }

  // 5. Retention default. An admin's choice is never overwritten.
  $db->upsert('settings', ['setting_key' => 'log_retention_days', 'setting_value' => '90'], ['setting_key'], false);
};
