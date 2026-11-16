<?php
/*
  Status Page
  Copyright 2026 Sejin Kim

  Licensed under the MIT License. See LICENSE.
*/

// Fresh-install worker. Reached by POST from install/index.php. It connects with the
// supplied credentials, creates the schema for the chosen engine
// (install/schema/<driver>.sql), seeds settings, and creates the root administrator.
// Upgrades of existing installs use upgrade.php instead.

if (($_POST['installconfirm'] ?? '') !== 'true') {
  die('Installation continue not confirmed. Exiting now.');
}

require_once __DIR__ . '/../lib/db.php';
require_once __DIR__ . '/../lib/html.php';
require_once __DIR__ . '/../lib/db_helpers.php';
require_once __DIR__ . '/../lib/version.php';

// Settings that exist from the start but have no value until an admin sets one.
const INSTALL_EMPTY_SETTINGS = [
  'about_this_site', 'enable_sso', 'entity_id', 'ga_measurement_id', 'name_id_format',
  'pes_description', 'saml_email_attribute', 'service_provider_base_url', 'slo_service',
  'sso_service', 'x509cert', 'welcome_message', 'meta_description',
];

/**
 * The config.php contents for the copy-paste block. Values go through var_export,
 * so a password containing quotes or backslashes still produces valid PHP.
 */
function installConfigFile(string $driver, int $port, string $host, string $name, string $user, string $pass): string
{
    return "<?php\n"
        . 'define(\'DB_DRIVER\', ' . var_export($driver, true) . ");\n"
        . "define('DB_PORT', $port);\n"
        . 'define(\'DB_SERVER\', ' . var_export($host, true) . ");\n"
        . 'define(\'DB_USERNAME\', ' . var_export($user, true) . ");\n"
        . 'define(\'DB_PASSWORD\', ' . var_export($pass, true) . ");\n"
        . 'define(\'DB_NAME\', ' . var_export($name, true) . ");\n"
        . "?>\n";
}

$driver = $_POST['dbdriver'] ?? '';
if (!in_array($driver, ['mysql', 'pgsql'], true)) {
  die('Unsupported database engine. Choose MySQL or MariaDB, or PostgreSQL.');
}
$port = (int)($_POST['dbport'] ?? 0);
if ($port <= 0) {
  $port = $driver === 'mysql' ? 3306 : 5432;
}
$host = (string)($_POST['dbhost'] ?? '');
$name = (string)($_POST['dbschema'] ?? '');
$user = (string)($_POST['dbuser'] ?? '');
$pass = (string)($_POST['dbpass'] ?? '');

$timezone = (string)($_POST['timezone'] ?? '');
if (!in_array($timezone, timezone_identifiers_list(), true)) {
  die('Choose a valid timezone.');
}

try {
  $db = new Db($driver, $host, $port, $name, $user, $pass);
} catch (Throwable $e) {
  // The driver's message can echo connection details, so it goes to the log only.
  error_log('status-page install: database connection failed: ' . $e->getMessage());
  die('Unable to connect to the database. Check the engine, host, port, schema name and credentials.');
}

echo '<p>Copy the following lines of code between the begin and end tags (but not including the tags) into a new file called config.php, and place this file inside of the <code>templates</code> directory in your web server root. (For example, <code>/var/www/html/templates/config.php</code>.)</p>';
echo '======== BEGIN CONFIG FILE ========<br>';
echo '<pre>' . e(installConfigFile($driver, $port, $host, $name, $user, $pass)) . '</pre>';
echo '======== END CONFIG FILE ========<br>';
echo '======== BEGIN INSTALLATION LOG ========<br>';

echo 'Creating database schema<br>';
try {
  $db->runScript((string)file_get_contents(__DIR__ . "/schema/$driver.sql"));
} catch (Throwable $e) {
  error_log('status-page install: schema failed: ' . $e->getMessage());
  die('Unable to create the database schema. See the server log for details.');
}
SiteSettings::clear();
echo 'Schema created<br>';
writeToLog($db, 'Schema created for ' . $driver, -1);

// Settings from the installer form, plus the defaults.
$settings = [
  'software_version' => SOFTWARE_VERSION,
  'database_version' => DATABASE_VERSION,
  'footer_org' => $_POST['organization'] ?? '',
  'org_link' => $_POST['organizationwebpage'] ?? '',
  'feedback_link' => $_POST['feedbackwebpage'] ?? '',
  'privacy_policy_link' => $_POST['privacypolicywebpage'] ?? '',
  'header_image_location' => $_POST['headerimageurl'] ?? '',
  'get_help_destination' => $_POST['gethelpdestination'] ?? '',
  'timezone' => $timezone,
  'plannedfuturedays' => '7',
  'incident_to_show_timerange' => '90',
  'audit_policy' => 'strict',
  'log_retention_days' => '90',
];
foreach (INSTALL_EMPTY_SETTINGS as $key) {
  $settings[$key] = null;
}
foreach ($settings as $key => $value) {
  $db->upsert('settings', ['setting_key' => $key, 'setting_value' => $value], ['setting_key']);
}
SiteSettings::clear();
echo 'Created settings<br>';
writeToLog($db, 'Created settings', -1);

writeToLog($db, 'Hashing root user password', -1);
$hash = password_hash((string)($_POST['rootpass'] ?? ''), PASSWORD_DEFAULT);
// Booleans are bound as 1/0: PDO turns false into '', which PostgreSQL rejects.
$db->insert('users', [
  'user_first_name' => 'Root',
  'user_last_name' => 'User',
  'user_email' => 'root@localhost',
  'user_password' => $hash,
  'user_issuperuser' => 1,
  'user_totpenabled' => 0,
], 'user_id');
unset($hash);
echo 'Created root user<br>';
writeToLog($db, 'Created root user as superuser', -1);

writeToLog($db, 'Closing database connection now!', -1);
echo '======== END INSTALLATION LOG ========<br><br>';
?>
Installation has finished successfully!<br>
Here are some next steps to continue configuration.<br>
<ul>
  <li>You should delete the install directory to prevent these settings from being overwritten.<li>
  <li>Log into the admin portal by clicking at the link in the footer, then log in using the username and password you just configured.</li>
  <li>Configure your site settings by clicking on System Settings in the admin portal, like your feedback webpage or link, privacy policy, header image URL, Google Analytics measurement ID, and welcome message.</li>
  <li>Add some service groups.</li>
  <li>After you've added some service groups, add some services. (Services need to be a member of a service group.)</li>
</ul>
