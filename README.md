# Status Page

A simple status page client running a modern software dependency stack.

This software is *very* sloppily written in some places. Function over form. Large amounts of it could be cleaned up and better documented. For one, moving most of it to object-oriented code from procedural style would be a good start. It's on the ol' to-do list of things that will probably never happen, but I'm open to PRs - just see the [Contributing guidelines](https://github.com/kim3-sudo/status-page/blob/main/CONTRIBUTING.md) before submitting your PR so it shows up correctly.

I'm not gonna push a KoFi or sponsorship on this project. That's not its purpose. It's to be useful. If you want to be helpful, contribute, don't donate. If you have gobs of money, give it to the [IRC](https://www.rescue.org/) instead.

## Accessibility

This software aims to be accessible to the WCAG 2.1 level A guidelines for accessibility. It also fulfills some of the level AA guidelines but is not fully AA compliant.

Requests to improve the accessibility of the software should be submitted as a GitHub issue.

## License

The code in this repository is licensed under the [MIT License](https://github.com/kim3-sudo/status-page/blob/main/LICENSE) by Sejin Kim.

## Prerequisites

- Operating System
  + Rocky Linux (>=9) OR
  + CentOS Linux (>=7) OR
  + RHEL (>=7) OR
  + Unofficial support: Ubuntu Server (>=20.04 LTS)
  + Unofficial support: Debian Server (>=12)
  + Unofficial support: Fedora Server (>=38)
- Web Server
  + Apache (=2) OR
  + Nginx (1.20-1.21)
- PHP Processors
  + PHP (>=8.1) AND
  + the `pdo` extension, plus `pdo_mysql` for MySQL/MariaDB or `pdo_pgsql` for PostgreSQL
- Composer
- SQL Database (pick one)
  + MariaDB (>=10.5) OR
  + MySQL (>=8) OR
  + PostgreSQL (>=14), with the database created using UTF8 encoding

Windows, macOS, and other types of Linux (including Ubuntu Server) are not officially supported, but you may have success with these operating systems. Future official support for these operating systems may be added in the future. Unofficial support indicates that the software may work, but it has not been officially validated.

NOTE: Version >0.1.0 (Depolo) requires PHP version 8.1. Previous versions only required PHP version 8.0.

The test suite was run on PHP 8.4 and 8.5, against MariaDB 13 and PostgreSQL 16. PHP 8.1 to 8.3 and older database releases have not been tested with this version.

## Installation

1. Install the prerequisites.
2. Clone the repository into your web directory, probably `/var/www/html`.
```bash
git clone https://github.com/kim3-sudo/status-page.git
```
3. Use Composer to install the external package dependencies, pinned by `composer.lock`.
```bash
composer install
```
4. In MySQL, MariaDB, or PostgreSQL, create a database and a user with at least `CREATE`, `DROP`, `INSERT`, `DELETE`, `SELECT`, and `UPDATE` privileges. For PostgreSQL, the database must use UTF8 encoding. Remember, scoping your permissions is better for security!
5. In a web browser, navigate to your web directory, into the `install` directory - for example, https://your-host.tld/install.
6. Choose the database engine (MySQL/MariaDB or PostgreSQL), provide the connection details on the installation page, then press *Install Now*.
7. The installer will create the database schema and generate a config file. Place the config file into a file called `config.php` inside of the `templates` directory. An example file is provided under `templates/config.php.template`, but you don't have to use this if you copy the file over.
8. Clean up by removing the entire `install` directory.
9. :tada:

## Upgrading

Some database upgrades cannot be undone. Version 0.3.1 removes the plaintext API keys, and version 0.3.2 drops two columns that are no longer used. Once `php upgrade.php` has applied them, an older release cannot run against the database. Back up first and keep the backup until you have confirmed the new version works.

To upgrade an existing install in place:

1. Back up your database first. Migrations are written to be safe to re-run, but a restore point is still the only way back from 0.3.1 or 0.3.2.
2. Pull the new version into your web directory.
```bash
git pull
```
3. Update Composer dependencies.
```bash
composer install
```
4. Run the upgrade script from the command line on the server. This applies any pending database migrations and updates the recorded software/database version — it is safe to re-run if it's interrupted partway through.
```bash
php upgrade.php
```
   If the same API key is stored twice, the 0.3.1 migration stops and changes nothing. Delete one of the two entries in the admin portal (Service API Keys or API Keys). The remaining entry keeps the key working. Then run `php upgrade.php` again.
5. If the script reports that no superuser exists, it prints the SQL to grant one. Only a superuser can change the audit policy.
6. Schedule the audit log cleanup (see [Audit log retention](#audit-log-retention)).

## Configuration

By in large, configuration is handled in the software itself, rather than in local configuration files. When you installed the software, you should have set a **root password**. This password is different from the root of the server that the software is running on. Use this, along with the `root@localhost` **username** to log into the **admin portal**. Then, on the left-side, go to **System Settings**. Here, you can set individual system settings by entering the parameter, then clicking on **Submit** to save the setting.

## API

The status API (`api/service`) and the incident API (`api/incident`) authenticate with an API key. Create a key in the admin portal under **Service API Keys**. The key is shown once, and only a hash of it is stored, so a lost key cannot be recovered. Create a new one instead.

Send the key in the `Authorization` header:

```bash
curl -H "Authorization: Bearer YOUR_API_KEY" https://your-host.tld/api/service/
```

Earlier releases took the key from an `authkey` URL parameter. That still works in this release, and each use writes a warning to the audit log. It is deprecated because URLs are written to web server logs. Move your clients to the header before the next release removes it. The incident API's POST form also still accepts an `authkey` field.

If Apache passes PHP through CGI or FastCGI, it does not forward the `Authorization` header by default. Add `CGIPassAuthorization On` to the virtual host. With nginx and PHP-FPM, add `fastcgi_param HTTP_AUTHORIZATION $http_authorization;` to the PHP location block.

The incident API's list response still includes `incident_describes_ids`, a comma-separated list of affected service IDs. It is now built from the `incident_service` table. Service status (`service_status_short`) is no longer stored; it is computed when the API is called.

## Audit log retention

Every action is recorded in the `log` table. Entries older than the `log_retention_days` setting are removed by a command-line script. The default is 90 days. Set the value to `0` to keep everything. The setting lives in the `settings` table; it is not yet on the System Settings page.

Run the cleanup from cron, for example once a day:

```
15 3 * * *  php /var/www/html/prune-log.php
```

The script works only from the command line and refuses web requests.

## Software Versions

When new software versions are released, they will contain a new software version number and code name. In general, here's how the numbering scheme works.

```
0.0.1a (`Hopewell`)
│ │ ││       └─── Code name
│ │ │└─────────── Build number
│ │ └──────────── Revision number
│ └────────────── Minor version number
└──────────────── Major version number
```

- Major version updates are generally not going to be compatible with previous versions, and they may require full reinstalls.
- Minor version updates are generally going to include database updates, and they may require additional tables or constraints to be added to the database.
- Revision updates are generally compatible with previous versions and should be drop-in replacements. They replace major bugs and introduce new features.
- Build updates are generally compatible with previous versions and should be drop-in replacements. They replace minor bugs.
- The code name changes with each new version number.
