<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Permission is hereby granted, free of charge, to any person obtaining a copy of
  this software and associated documentation files (the “Software”), to deal in
  the Software without restriction, including without limitation the rights to
  use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
  of the Software, and to permit persons to whom the Software is furnished to do
  so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all
  copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
  SOFTWARE.
*/

// In-place upgrade script for existing installs (git pull, then run this
// from the server: `php upgrade.php`). CLI-only — this touches the database
// schema/settings, so it does not get a web-accessible counterpart the way
// install/ does.
//
// How it works: DATABASE_VERSION in lib/version.php is the version
// this codebase expects. Each file in install/migrations/ is named after
// the database_version it upgrades TO and returns a callable that applies
// that step. On each run, every migration newer than the currently
// installed database_version (read from the `settings` table) and up to
// DATABASE_VERSION is applied in order, and database_version is updated
// after each one succeeds — so an interrupted upgrade can simply be
// re-run and picks up where it left off. Migrations must be idempotent
// (see install/migrations/0.3.0.php for the pattern) since a migration
// can be re-applied if the script dies after the schema change but before
// the version bookkeeping is written.
//
// Works on MySQL/MariaDB and PostgreSQL. The driver comes from templates/config.php.

if (php_sapi_name() !== 'cli') {
  http_response_code(403);
  die('This script must be run from the command line: php upgrade.php');
}

require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/db_helpers.php';
require_once __DIR__ . '/lib/version.php';
require_once __DIR__ . '/install/_migration_functions.php';

function upgradeLog(string $message): void {
  echo $message . PHP_EOL;
}

function setSetting(Db $db, string $key, string $value): void {
  $db->upsert('settings', ['setting_key' => $key, 'setting_value' => $value], ['setting_key']);
  SiteSettings::clear();
}

// Earliest database_version this migration system supports. Installs from
// before database_version was tracked at all fall back to this baseline.
const BASELINE_DATABASE_VERSION = '0.1.0';

$currentVersion = getSetting($db, 'database_version');
if ($currentVersion === '') {
  $currentVersion = BASELINE_DATABASE_VERSION;
  upgradeLog("No database_version setting found — assuming baseline $currentVersion.");
}

upgradeLog("Current database version: $currentVersion");
upgradeLog('Target database version:  ' . DATABASE_VERSION);

if (version_compare($currentVersion, DATABASE_VERSION, '>=')) {
  upgradeLog('Database is already up to date.');
} else {
  $availableFiles = [];
  foreach (glob(__DIR__ . '/install/migrations/*.php') as $file) {
    $availableFiles[basename($file, '.php')] = $file;
  }

  $versionsToApply = selectMigrationsToApply(array_keys($availableFiles), $currentVersion, DATABASE_VERSION);

  if (empty($versionsToApply)) {
    upgradeLog('No migration files found between the current and target version — check install/migrations/.');
    exit(1);
  }

  foreach ($versionsToApply as $version) {
    $file = $availableFiles[$version];
    upgradeLog("Applying migration $version...");
    $migrate = require $file;
    try {
      $migrate($db);
    } catch (\Throwable $e) {
      upgradeLog("Migration $version FAILED: " . $e->getMessage());
      writeToLog($db, "Upgrade migration $version failed: " . $e->getMessage(), -1, 'FERR');
      exit(1);
    }

    setSetting($db, 'database_version', $version);
    writeToLog($db, "Applied database migration to version $version", -1);
    upgradeLog("Migration $version applied. database_version is now $version.");
  }
}

// Software version can move independently of the database version — a
// release with no schema changes still needs SOFTWARE_VERSION recorded.
$currentSoftwareVersion = getSetting($db, 'software_version');
if ($currentSoftwareVersion !== SOFTWARE_VERSION) {
  setSetting($db, 'software_version', SOFTWARE_VERSION);
  writeToLog($db, "Updated software_version from '$currentSoftwareVersion' to '" . SOFTWARE_VERSION . "'", -1);
  upgradeLog("software_version updated: $currentSoftwareVersion -> " . SOFTWARE_VERSION);
}

// The audit policy can only be changed by a superuser. Installs made before
// 0.3.0 may have none, so say so rather than changing anyone's privileges.
if ((int)$db->value('SELECT COUNT(*) FROM users WHERE user_issuperuser') === 0) {
  upgradeLog('Note: no superuser account exists, so the audit policy cannot be changed from the admin portal.');
  upgradeLog('      To grant one, run this as the database user, replacing 1 with the user_id:');
  upgradeLog('      UPDATE users SET user_issuperuser = TRUE WHERE user_id = 1;');
}

upgradeLog('Upgrade complete.');
exit(0);
