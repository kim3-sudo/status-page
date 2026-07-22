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

// Pure function only — no filesystem/DB access — so this is safe to require
// directly from tests. upgrade.php requires this and supplies the real
// filesystem listing and version numbers.

if (!function_exists('selectMigrationsToApply')) {
  /**
   * @param array<string> $availableVersions Version strings found in install/migrations/
   * @return array<string> The subset that should be applied, in ascending order
   */
  function selectMigrationsToApply(array $availableVersions, string $currentVersion, string $targetVersion): array {
    $selected = array_filter(
      $availableVersions,
      fn (string $v) => version_compare($v, $currentVersion, '>') && version_compare($v, $targetVersion, '<=')
    );
    usort($selected, 'version_compare');
    return array_values($selected);
  }
}
