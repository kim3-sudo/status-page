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
  + php-mysqlnd (>=8)
- Composer
- SQL Database
  + MariaDB (>=10.5) OR
  + MySQL (>=8)

Windows, macOS, and other types of Linux (including Ubuntu Server) are not officially supported, but you may have success with these operating systems. Future official support for these operating systems may be added in the future. Unofficial support indicates that the software may work, but it has not been officially validated.

NOTE: Version >0.1.0 (Depolo) requires PHP version 8.1. Previous versions only required PHP version 8.0.

## Installation

1. Install the prerequisites.
2. Clone the repository into your web directory, probably `/var/www/html`.
```bash
git clone https://github.com/kim3-sudo/status-page.git
```
3. Use Composer to install all of the external package dependencies.
```bash
composer update
```
4. In MySQL or MariaDB, create a database and a user with at least `CREATE`, `DROP`, `INSERT`, `DELETE`, `SELECT`, and `UPDATE` privileges. Remember, scoping your permissions is better for security!
5. In a web browser, navigate to your web directory, into the `install` directory - for example, https://your-host.tld/install.
6. Provide the necessary information on the installation page, then press *Install Now*.
7. The installer will create the database schema and generate a config file. Place the config file into a file called `config.php` inside of the `templates` directory. An example file is provided under `templates/config.php.template`, but you don't have to use this if you copy the file over.
8. Clean up by removing the entire `install` directory.
9. :tada:

## Configuration

By in large, configuration is handled in the software itself, rather than in local configuration files. When you installed the software, you should have set a **root password**. This password is different from the root of the server that the software is running on. Use this, along with the `root@localhost` **username** to log into the **admin portal**. Then, on the left-side, go to **System Settings**. Here, you can set individual system settings by entering the parameter, then clicking on **Submit** to save the setting.

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
