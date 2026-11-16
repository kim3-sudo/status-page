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

// Backfills the `settings` rows the SAML/SSO integration expects
// (admin/pagesystemsettings.php and saml/saml_settings.php read all of
// these via getSetting()). A fresh install picks these up automatically
// from install/run.php; this migration is only for installs that existed
// before SAML was added.
//
// Insert-only: a key that already exists keeps its value, including one an admin
// configured by hand, so this is safe to re-run.

return function (Db $db): void {
  foreach (['enable_sso', 'entity_id', 'name_id_format', 'saml_email_attribute',
            'service_provider_base_url', 'slo_service', 'sso_service', 'x509cert'] as $key) {
    $db->upsert('settings', ['setting_key' => $key], ['setting_key'], false);
  }
};
