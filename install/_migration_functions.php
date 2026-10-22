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
