# Changelog

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
