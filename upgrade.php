<?php
/*
    Status Page
    Copyright (C) 2024 Sejin Kim

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

// In-place upgrade script for existing installs (git pull, then run this
// from the server: `php upgrade.php`). CLI-only — this touches the database
// schema/settings, so it does not get a web-accessible counterpart the way
// install/ does.
//
// How it works: DATABASE_VERSION in templates/_version.php is the version
// this codebase expects. Each file in install/migrations/ is named after
// the database_version it upgrades TO and returns a callable that applies
// that step. On each run, every migration newer than the currently
// installed database_version (read from the `settings` table) and up to
// DATABASE_VERSION is applied in order, and database_version is updated
// after each one succeeds — so an interrupted upgrade can simply be
// re-run and picks up where it left off. Migrations must be idempotent
// (see install/migrations/0.2.0.php for the pattern) since a migration
// can be re-applied if the script dies after the schema change but before
// the version bookkeeping is written.

if (php_sapi_name() !== 'cli') {
  http_response_code(403);
  die('This script must be run from the command line: php upgrade.php');
}

require_once __DIR__ . '/templates/config.php';
require_once __DIR__ . '/templates/_db_helpers.php';
require_once __DIR__ . '/templates/_version.php';
require_once __DIR__ . '/install/_migration_functions.php';

function upgradeLog(string $message): void {
  echo $message . PHP_EOL;
}

function setSetting(mysqli $link, string $key, string $value): void {
  $stmt = $link->prepare(
    'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) '
    . 'ON DUPLICATE KEY UPDATE setting_value = ?'
  );
  $stmt->bind_param('sss', $key, $value, $value);
  $stmt->execute();
  $stmt->close();
}

// Earliest database_version this migration system supports. Installs from
// before database_version was tracked at all fall back to this baseline.
const BASELINE_DATABASE_VERSION = '0.1.0';

$currentVersion = getSetting($link, 'database_version');
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
      $migrate($link);
    } catch (\Throwable $e) {
      upgradeLog("Migration $version FAILED: " . $e->getMessage());
      writeToLog($link, "Upgrade migration $version failed: " . $e->getMessage(), -1, 'FERR');
      exit(1);
    }

    setSetting($link, 'database_version', $version);
    writeToLog($link, "Applied database migration to version $version", -1);
    upgradeLog("Migration $version applied. database_version is now $version.");
  }
}

// Software version can move independently of the database version — a
// release with no schema changes still needs SOFTWARE_VERSION recorded.
$currentSoftwareVersion = getSetting($link, 'software_version');
if ($currentSoftwareVersion !== SOFTWARE_VERSION) {
  setSetting($link, 'software_version', SOFTWARE_VERSION);
  writeToLog($link, "Updated software_version from '$currentSoftwareVersion' to '" . SOFTWARE_VERSION . "'", -1);
  upgradeLog("software_version updated: $currentSoftwareVersion -> " . SOFTWARE_VERSION);
}

upgradeLog('Upgrade complete.');
exit(0);
