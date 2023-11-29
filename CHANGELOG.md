# Changelog

## 0.0.1b ("Grove Church")

- Updated the installer to drop foreign-key constraints before attempting to drop tables. Previous behavior was the installer failing on the table drop stage of installation due to a foreign key constraint. Instead, all foreign keys are dropped before the installer attempts to drop any tables.
- Updated the README to reflect an email-format for the root user. Previous documentation indicated that the root user login was just `root`, where it should have been `root@localhost`.

## 0.0.1a ("Hopewell")

- Initial commit
