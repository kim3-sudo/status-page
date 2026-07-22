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

// Backfills the `settings` rows the SAML/SSO integration expects
// (admin/pagesystemsettings.php and saml/saml_settings.php read all of
// these via getSetting()). A fresh install picks these up automatically
// from install/run.php; this migration is only for installs that existed
// before SAML was added.
//
// INSERT IGNORE is used deliberately: `setting_key` is the primary key, so
// this is a no-op for any key that's already present — including one an
// admin may have already configured by hand — and safe to re-run.

return function (mysqli $link): void {
  $link->query(
    "INSERT IGNORE INTO settings (setting_key) VALUES "
    . "('enable_sso'), ('entity_id'), ('name_id_format'), "
    . "('saml_email_attribute'), ('service_provider_base_url'), "
    . "('slo_service'), ('sso_service'), ('x509cert')"
  );
};
