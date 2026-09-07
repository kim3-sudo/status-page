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

// Single source of truth for the app's version numbers. install/run.php,
// install/index.php, and upgrade.php all read these constants instead of
// each hardcoding their own literal — the SAML integration previously
// shipped without either version being bumped because nothing enforced
// that they come from one place.

if (!defined('SOFTWARE_VERSION')) {
  define('SOFTWARE_VERSION', '1.2.0 (Wilma)');
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
