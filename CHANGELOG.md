# Changelog

## Unreleased

Database version 0.3.2. Software version 1.2.0 (Wilma) (`lib/version.php`).

### Added

- PostgreSQL support alongside MySQL and MariaDB. The installer asks which engine to use. Both engines use the same code, through the `Db` class (`lib/db.php`).
- Uptime and status are computed from incidents when they are read, so the status page, its badges, and the API always agree. Only major and minor outages count against uptime.
- Incident and maintenance writes run in one transaction, and their audit entries follow the audit policy. Under the default strict policy, a write that cannot be audited is rolled back.
- Audit policy setting (`audit_policy`): strict (the default) or soft. Only superusers can change it.
- HTML in incident updates is cleaned on write and again on display. Plain text is escaped.
- `Authorization: Bearer` header for the status and incident APIs.
- Audit log retention: the `log_retention_days` setting (default 90, `0` keeps everything) and the `prune-log.php` command-line script.
- Migration 0.3.1 stores API keys as SHA-256 hashes, and migration 0.3.2 removes two unused columns.
- Each incident is listed by its start time. Open incidents appear before resolved ones, and open incidents stay listed however old they are.
- The welcome message reappears when its text changes. "Don't show for 24 hours" is keyed to that text.

### Changed

- API key lookups use the stored hash. Keys are no longer stored in plain text.
- The `authkey` URL parameter is deprecated. It still works in this release, and each use writes a warning to the audit log. Use the `Authorization: Bearer` header.
- Time zone handling: stored times are UTC, marked with `incident_update_utc`. Existing rows stay site-local.
- Admin pages escape the values they display, including values inside form fields.
- The admin page fragments (`admin/page*.php` and the style guides) answer 404 when requested directly.
- `templates/_api.php` and `templates/_version.php` moved to `lib/api.php` and `lib/version.php`. Logic files no longer live in `templates/`.
- Scripts that ran inline are in `assets/js/script.js` (public and shared) and `assets/js/admin.js` (admin). Inline `onclick`, `onchange`, and `onkeyup` handlers were removed.
- The Google tag loads from `www.googletagmanager.com`.
- The audit log no longer records the first eight characters of a new API key. It records a fingerprint instead.

### Removed

- `services.service_status_short`. Status is derived, not stored.
- `incident.incident_describes_ids`. The incident API builds the same comma-separated field from `incident_service`.
- The 96-character limit on services per incident. It existed only to fit the removed CSV column.
- The empty `admin/pes.php` file.

### Fixed

- API endpoints that could not run because of parse errors.
- SQL injection through the `service_id` parameter of the service API.
- Unchecked service IDs in the incident API's POST.
- Uptime could fall below 0% or go outside the 0-100 range. The status page could also crash on a `min()` call over an empty list.
- Resolving one of two open incidents no longer clears a service that the other incident still affects.
- The maintenance banner was chosen at random between windows. It now follows the active maintenance window.
- Admin pages escape stored text. Before, a setting containing a quote could break out of a form field.
- The "Details" modal for each service group showed the first group.
- A misspelled `data-bs-dismiss` attribute stopped the close button of the API key deletion dialog from working.
- The "change own password" form checked a field it never sent, so a mismatch got the wrong message.
- The search box matched only the first service in each group.

### Upgrade notes

- Back up the database before upgrading. Migrations 0.3.1 and 0.3.2 cannot be reversed.
- Run `php upgrade.php` from the command line. It is safe to run again.
- Update clients to send the API key in the `Authorization` header before the next release removes the URL parameter.
- Configure Apache (`CGIPassAuthorization On`) or nginx (`fastcgi_param HTTP_AUTHORIZATION`) so PHP receives the header. See the README.
- Schedule `prune-log.php` from cron, or set `log_retention_days` to `0`.
- If no superuser exists, the upgrade prints the SQL to create one.
- If any API key is stored twice, migration 0.3.1 stops. Delete one of the duplicate entries and run the upgrade again.

## 0.0.1c ("Burtnett")

- Updated the installer to show successful installation message. Previous messaging indicating successful installation was possibly ambiguous.
- Update the table reference in the PES query to use the `pes` table instead of the `post_event_summary` table. Previous attempts to add a PES were unsuccessful as the table name was incorrect. Instead, a SQL error message was thrown and the PES was not saved.

## 0.0.1b ("Grove Church")

- Updated the installer to drop foreign-key constraints before attempting to drop tables. Previous behavior was the installer failing on the table drop stage of installation due to a foreign key constraint. Instead, all foreign keys are dropped before the installer attempts to drop any tables.
- Updated the README to reflect an email-format for the root user. Previous documentation indicated that the root user login was just `root`, where it should have been `root@localhost`.

## 0.0.1a ("Hopewell")

- Initial commit

## Updating Version Numbers

On a new version release, the database schema is updated (under `settings`), the installer form will report that it is installing a new version, the installer runner will insert a new version number into the database, and a new release will be published in the repository. 
