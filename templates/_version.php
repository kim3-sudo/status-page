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

// Single source of truth for the app's version numbers. install/run.php,
// install/index.php, and upgrade.php all read these constants instead of
// each hardcoding their own literal — the SAML integration previously
// shipped without either version being bumped because nothing enforced
// that they come from one place.

if (!defined('SOFTWARE_VERSION')) {
  define('SOFTWARE_VERSION', '1.1.0 (McCament)');
}

// DATABASE_VERSION tracks the schema/settings shape, independently of
// SOFTWARE_VERSION — a release can ship with no database changes at all.
// Bumped here because the SAML integration added new expected `settings`
// rows (enable_sso, entity_id, ...) that install/run.php creates on a fresh
// install but that pre-existing installs upgrading in place won't have;
// see install/migrations/0.2.0.php.
if (!defined('DATABASE_VERSION')) {
  define('DATABASE_VERSION', '0.2.0');
}
