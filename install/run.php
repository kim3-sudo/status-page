<?php
if ($_POST['installconfirm'] != 'true') {
  die('Installation continue not confirmed. Exiting now.');
}

define('DB_SERVER', $_POST['dbhost']);
define('DB_USERNAME', $_POST['dbuser']);
define('DB_PASSWORD', $_POST['dbpass']);
define('DB_NAME', $_POST['dbschema']);

if ($link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME)) {
  echo '<p>Copy the following lines of code between the begin and end tags (but not including the tags) into a new file called config.php, and place this file inside of the <code>templates</code> directory in your web server root. (For example, <code>/var/www/html/templates/config.php</code>.)</p>';
?>
======== BEGIN CONFIG FILE ========
<pre>
&lt;?php
define('DB_SERVER', '<?=$_POST['dbhost']?>');
define('DB_USERNAME', '<?=$_POST['dbuser']?>');
define('DB_PASSWORD', '<?=$_POST['dbpass']?>');
define('DB_NAME', '<?=$_POST['dbschema']?>');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
?&gt;
</pre>
======== END CONFIG FILE ========<br>
======== BEGIN INSTALLATION LOG ========<br>
<?php
  function writeToLog($link, $entry, $uid, $type = 'INFO') {
    if ($link->query("INSERT INTO log (log_entry, log_user_id, log_type) VALUES ('" . mysqli_real_escape_string($link, substr($entry, 0, 139)) . "', '" . mysqli_real_escape_string($link, $uid) . "', '" . mysqli_real_escape_string($link, $type) . "')")) {
    } else {
      die('Unable to write to log! Auditability violated.');
    }
  }
  echo 'Creating database schema<br>';
  if ($link->query("DROP TABLE IF EXISTS log")) {
    echo 'Table <code>log</code> dropped if exists<br>';
  } else {
    die('Unable to drop <code>log</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE log (log_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY, log_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP, log_entry VARCHAR(140), log_user_id INT NOT NULL, log_type VARCHAR(4) DEFAULT 'INFO') ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Created log table<br>';
    writeToLog($link, 'New log table was initialized', -1);
  } else {
    die('Failed to create log table<br>');
  }
  echo 'Dropping foreign key constraints before dropping tables<br>';
  if ($link->query("ALTER TABLE `incident` DROP FOREIGN KEY IF EXISTS `incident_ibfk_1`")) {
    echo 'Constraint <code>incident_ibfk_1</code> dropped if exists<br>';
    writeToLog($link, 'Constraint incident_ibfk_1 dropped if exists', -1);
  } else {
    writeToLog($link, 'Unable to drop incident_ibfk_1 or insufficient permissions', -1, 'WARN');
    echo 'Unable to drop <code>incident_ibfk_1</code> or insufficient permissions</br>';
  }
  if ($link->query("ALTER TABLE `incident_update` DROP FOREIGN KEY IF EXISTS `incident_update_ibfk_1`")) {
    echo 'Constraint <code>incident_update_ibfk_1</code> dropped if exists<br>';
    writeToLog($link, 'Constraint incident_update_ibfk_1 dropped if exists', -1);
  } else {
    echo 'Unable to drop <code>incident_update_ibfk_1</code> or insufficient permissions</br>';
    writeToLog($link, 'Unable to drop incident_update_ibfk_1 or insufficient permissions', -1, 'WARN');
  }
  if ($link->query("ALTER TABLE `incident_update` DROP FOREIGN KEY IF EXISTS `incident_update_ibfk_2`")) {
    echo 'Constraint <code>incident_update_ibfk_2</code> dropped if exists<br>';
    writeToLog($link, 'Constraint incident_update_ibfk_2 dropped if exists', -1);
  } else {
    echo 'Unable to drop <code>incident_update_ibfk_2</code> or insufficient permissions</br>';
    writeToLog($link, 'Unable to drop incident_update_ibfk_2 or insufficient permissions', -1, 'WARN');
  }
  if ($link->query("ALTER TABLE `services` DROP FOREIGN KEY IF EXISTS `services_ibfk_1`")) {
    echo 'Constraint <code>services_ibfk_1</code> dropped if exists<br>';
    writeToLog($link, 'Constraint services_ibfk_1 dropped if exists', -1);
  } else {
    echo 'Unable to drop <code>services_ibfk_1</code> or insufficient permissions</br>';
    writeToLog($link, 'Unable to drop services_ibfk_1 or insufficient permissions', -1, 'WARN');
  }
  if ($link->query("ALTER TABLE `services` DROP FOREIGN KEY IF EXISTS `services_ibfk_2`")) {
    echo 'Constraint <code>services_ibfk_2</code> dropped if exists<br>';
    writeToLog($link, 'Constraint services_ibfk_2 dropped if exists', -1);
  } else {
    echo 'Unable to drop <code>services_ibfk_2</code> or insufficient permissions</br>';
    writeToLog($link, 'Unable to drop services_ibfk_2 or insufficient permissions', -1, 'WARN');
  }
  if ($link->query("DROP TABLE IF EXISTS users")) {
    echo 'Table <code>users</code> dropped if exists<br>';
    writeToLog($link, 'Table users dropped if exists', -1);
  } else {
    writeToLog($link, 'Unable to drop users or insufficient permissions', -1, 'WARN');
    die('Unable to drop <code>users</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `users` ( `user_id` int(8) NOT NULL AUTO_INCREMENT, `user_first_name` varchar(64) DEFAULT NULL, `user_last_name` varchar(64) DEFAULT NULL, `user_email` varchar(100) DEFAULT NULL, `user_password` varchar(255) DEFAULT NULL, `user_issuperuser` BOOL DEFAULT 0, `user_totpenabled` BOOL DEFAULT 0, user_totpsecret VARCHAR(255), PRIMARY KEY (`user_id`)) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>users</code> created<br>';
    writeToLog($link, 'Table users created', -1);
  } else {
    writeToLog($link, 'Unable to create users', -1, 'WARN');
    die('Unable to create <code>users</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS settings")) {
    echo 'Table <code>settings</code> dropped if exists<br>';
    writeToLog($link, 'Table settings dropped if exists', -1);
  } else {
    writeToLog($link, 'Unable to drop settings or insufficient permissions', -1, 'WARN');
    die('Unable to drop <code>settings</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `settings` ( `setting_key` varchar(32) NOT NULL, `setting_value` varchar(1337) DEFAULT NULL, PRIMARY KEY (`setting_key`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>settings</code> created<br>';
    writeToLog($link, 'Table settings created', -1);
  } else {
    writeToLog($link, 'Unable to create settings', -1, 'WARN');
    die('Unable to create <code>settings</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS pes")) {
    echo 'Table <code>pes</code> dropped if exists<br>';
    writeToLog($link, 'Table pes dropped if exists', -1);
  } else {
    writeToLog($link, 'Unable to drop pes or insufficient permissions', -1, 'WARN');
    die('Unable to drop <code>pes</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `pes` ( `pes_id` int(11) NOT NULL AUTO_INCREMENT, `pes_issue_summary` varchar(5000) DEFAULT NULL, `pes_issue_service_impact` varchar(5000) DEFAULT NULL, `pes_date` date DEFAULT curdate(), `pes_title` varchar(255) DEFAULT NULL, PRIMARY KEY (`pes_id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>pes</code> created<br>';
    writeToLog($link, 'Table pes created', -1);
  } else {
    writeToLog($link, 'Unable to create pes', -1, 'WARN');
    die('Unable to create <code>pes</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS incident_status")) {
    echo 'Table <code>incident_status</code> dropped if exists<br>';
    writeToLog($link, 'Table incident_status dropped if exists', -1);
  } else {
    writeToLog($link, 'Unable to drop incident_status or insufficient permissions', -1);
    die('Unable to drop <code>incident_status</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `incident_status` ( `incident_status_code` varchar(3) NOT NULL, `incident_status_description` varchar(64) DEFAULT NULL, PRIMARY KEY (`incident_status_code`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>incident_status</code> created<br>';
    writeToLog($link, 'Table incident_status created', -1);
  } else {
    writeToLog($link, 'Unable to create incident_status', -1, 'WARN');
    die('Unable to create <code>incident_status</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS service_status")) {
    echo 'Table <code>service_status</code> dropped if exists<br>';
    writeToLog($link, 'Table service_status dropped if exists', -1);
  } else {
    writeToLog($link, 'Unable to drop service_status or insufficient permissions', -1, 'WARN');
    die('Unable to drop <code>service_status</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `service_status` ( `service_status_code` varchar(3) NOT NULL, `service_status_description` varchar(64) DEFAULT NULL, PRIMARY KEY (`service_status_code`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>service_status</code> created<br>';
    writeToLog($link, 'Table service_status created', -1);
  } else {
    writeToLog($link, 'Unable to create service_status', -1, 'WARN');
    die('Unable to create <code>service_status</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS servicegroups")) {
    echo 'Table <code>servicegroups</code> dropped if exists<br>';
    writeToLog($link, 'Table servicegroups dropped if exists', -1);
  } else {
    writeToLog($link, 'Unable to drop servicegroups or insufficient permissions', -1, 'WARN');
    die('Unable to drop <code>servicegroups</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `servicegroups` ( `servicegroup_id` int(8) NOT NULL AUTO_INCREMENT, `servicegroup_name` varchar(64) DEFAULT NULL, PRIMARY KEY (`servicegroup_id`)) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>servicegroups</code> created<br>';
    writeToLog($link, 'Table servicegroups created', -1);
  } else {
    writeToLog($link, 'Unable to create servicegroups', -1, 'WARN');
    die('Unable to create <code>servicegroups</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS services")) {
    echo 'Table <code>services</code> dropped if exists<br>';
    writeToLog($link, 'Table services dropped if exists', -1);
  } else {
    writeToLog($link, 'Unable to drop services or insufficient permissions', -1, 'WARN');
    die('Unable to drop <code>services</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `services` ( `service_id` int(8) NOT NULL AUTO_INCREMENT, `service_name` varchar(64) DEFAULT NULL, `servicegroup_id` int(8) DEFAULT NULL, `service_description` varchar(144) DEFAULT NULL, `service_link` varchar(120) DEFAULT NULL, `service_status_short` varchar(3) DEFAULT NULL, PRIMARY KEY (`service_id`), KEY `servicegroup_id` (`servicegroup_id`), KEY `service_status_short` (`service_status_short`), CONSTRAINT `services_ibfk_1` FOREIGN KEY (`servicegroup_id`) REFERENCES `servicegroups` (`servicegroup_id`), CONSTRAINT `services_ibfk_2` FOREIGN KEY (`service_status_short`) REFERENCES `service_status` (`service_status_code`)) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>services</code> created<br>';
    writeToLog($link, 'Table services created', -1);
  } else {
    writeToLog($link, 'Unable to create services', -1, 'WARN');
    die('Unable to create <code>services</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS incident")) {
    echo 'Table <code>incident</code> dropped if exists<br>';
    writeToLog($link, 'Table incident dropped if exists', -1);
  } else {
    writeToLog($link, 'Unable to drop incident or insufficient permissions', -1, 'WARN');
    die('Unable to drop <code>incident</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `incident` (`incident_id` int(8) NOT NULL AUTO_INCREMENT, `incident_date` date DEFAULT curdate(), `incident_description` varchar(255) DEFAULT NULL, `incident_status_short` varchar(3) DEFAULT NULL, `incident_describes_ids` varchar(96) DEFAULT NULL, PRIMARY KEY (`incident_id`), KEY `incident_status_short` (`incident_status_short`), CONSTRAINT `incident_ibfk_1` FOREIGN KEY (`incident_status_short`) REFERENCES `incident_status` (`incident_status_code`)) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>incident</code> created<br>';
    writeToLog($link, 'Table incident created', -1);
  } else {
    writeToLog($link, 'Unable to create incident or insufficient permissions', -1, 'WARN');
    die('Unable to create <code>incident</code> or insufficient permissions<br>');
  }
  if ($link->query("DROP TABLE IF EXISTS incident_update")) {
    echo 'Table <code>incident_update</code> dropped if exists<br>';
    writeToLog($link, 'Table incident_update dropped if exists', -1);
  } else {
    writeToLog($link, 'Unable to drop incident_update or insufficient permissions', -1, 'WARN');
    die('Unable to drop <code>incident_update</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `incident_update` ( `incident_update_id` int(8) NOT NULL AUTO_INCREMENT, `incident_update_timestamp` datetime DEFAULT current_timestamp(), `incident_update_status_short` varchar(3) DEFAULT NULL, `incident_update_description` varchar(2000) DEFAULT NULL, `incident_update_incident_id` int(8) DEFAULT NULL, incident_update_is_planned_maint VARCHAR(2) DEFAULT NULL, PRIMARY KEY (`incident_update_id`), KEY `incident_update_status_short` (`incident_update_status_short`), KEY `incident_update_incident_id` (`incident_update_incident_id`), CONSTRAINT `incident_update_ibfk_1` FOREIGN KEY (`incident_update_status_short`) REFERENCES `incident_status` (`incident_status_code`), CONSTRAINT `incident_update_ibfk_2` FOREIGN KEY (`incident_update_incident_id`) REFERENCES `incident` (`incident_id`)) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>incident_update</code> created<br>';
    writeToLog($link, 'Table incident_update created', -1);
  } else {
    writeToLog($link, 'Unable to create incident_update or insufficient permissions', -1, 'WARN');
    die('Unable to create <code>incident_update</code> or insufficient permissions<br>');
  }
  if ($link->query("INSERT INTO incident_status VALUES ('IDE', 'Identified'), ('INV', 'Investigating'), ('MON', 'Monitoring'), ('PLA', 'Planned Maintenance'), ('RES', 'Resolved')")) {
    echo 'Inserted initial incident status codes<br>';
    writeToLog($link, 'Inserted initial incident status codes', -1);
  } else {
    writeToLog($link, 'Unable to insert initial incident status codes', -1, 'WARN');
    die('Unable to insert initial incident status codes<br>');
  }
  if ($link->query("INSERT INTO service_status VALUES ('DEG', 'Degraded'), ('MAJ', 'Major Outage'), ('MIN', 'Minor Outage'), ('OPE', 'Operational'),  ('PLA', 'Planned Maintenance')")) {
    echo 'Inserted initial service status codes<br>';
    writeToLog($link, 'Inserted initial service status codes', -1);
  } else {
    writeToLog($link, 'Unable to insert initial service status codes', -1, 'WARN');
    die('Unable to insert initial service status codes<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('software_version', '0.1.6 (`Earlywine`)')")) {
    echo 'Created and assigned <code>software_version</code> key<br>';
    writeToLog($link, 'Created an assigned software_version key', -1);
  } else {
    writeToLog($link, 'Unable to create and assign software_version key', -1, 'WARN');
    die('Unable to create and assign <code>software_version</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('database_version', '0.0.9')")) {
    echo 'Created and assigned <code>database_version</code> key<br>';
    writeToLog($link, 'Created and assigned database_version key', -1);
  } else {
    writeToLog($link, 'Unable to create and assign database_version key', -1, 'WARN');
    die('Unable to create and assign <code>database_version</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('footer_org', '" . mysqli_real_escape_string($link, $_POST['organization']) . "')")) {
    echo 'Created and assigned <code>footer_org</code> key<br>';
    writeToLog($link, 'Created and assigned footer_org key', -1);
    writeToLog($link, $_POST['organization'], -1);
  } else {
    writeToLog($link, 'Unable to create and assign footer_org key', -1, 'WARN');
    die('Unable to create and assign <code>footer_org</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('org_link', '" . mysqli_real_escape_string($link, $_POST['organizationwebpage']) . "')")) {
    echo 'Created and assigned <code>org_link</code> key<br>';
    writeToLog($link, 'Created and assigned org_link key', -1);
    writeToLog($link, $_POST['organizationwebpage'], -1);
  } else {
    writeToLog($link, 'Unable to create and assign org_link key', -1, 'WARN');
    die('Unable to create and assign <code>org_link</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('feedback_link', '" . mysqli_real_escape_string($link, $_POST['feedbackwebpage']) . "')")) {
    echo 'Created and assigned <code>feedback_link</code> key<br>';
    writeToLog($link, 'Created and assigned feedback_link key', -1);
    writeToLog($link, $_POST['feedbackwebpage'], -1);
  } else {
    writeToLog($link, 'Unable to create and assign feedback_link key', -1, 'WARN');
    die('Unable to create and assign <code>feedback_link</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('privacy_policy_link', '" . mysqli_real_escape_string($link, $_POST['privacypolicywebpage']) . "')")) {
    echo 'Created and assigned <code>privacy_policy_link</code> key<br>';
    writeToLog($link, 'Created and assigned privacy_policy_link key', -1);
    writeToLog($link, $_POST['privacypolicywebpage'], -1);
  } else {
    writeToLog($link, 'Unable to create and assign privacy_policy_link key', -1, 'WARN');
    die('Unable to create and assign <code>privacy_policy_link</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('header_image_location', '" . mysqli_real_escape_string($link, $_POST['headerimageurl']) . "')")) {
    echo 'Created and assigned <code>header_image_location</code> key<br>';
    writeToLog($link, 'Created and assigned header_image_location key', -1);
    writeToLog($link, $_POST['headerimageurl'], -1);
  } else {
    writeToLog($link, 'Unable to create and assign header_image_location key', -1, 'WARN');
    die('Unable to create and assign <code>header_image_location</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('get_help_destination', '" . mysqli_real_escape_string($link, $_POST['gethelpdestination']) . "')")) {
    echo 'Created and assigned <code>get_help_destination</code> key<br>';
    writeToLog($link, 'Created and assigned get_help_destination key', -1);
    writeToLog($link, $_POST['gethelpdestination'], -1);
  } else {
    writeToLog($link, 'Unable to create and assign get_help_destination key', -1, 'WARN');
    die('Unable to create and assign <code>get_help_destination</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('timezone', '" . mysqli_real_escape_string($link, $_POST['timezone']) . "')")) {
    echo 'Created and assigned <code>timezone</code> key<br>';
    writeToLog($link, 'Created and assigned timezone key', -1);
    writeToLog($link, $_POST['timezone'], -1);
  } else {
    writeToLog($link, 'Unable to create and assign timezone key', -1, 'WARN');
    die('Unable to create and assign <code>timezone</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('plannedfuturedays', '7')") {
    echo 'Created and assigned <code>plannedfuturedays</code> key</br>';
    writeToLog($link, 'Created and assigned plannedfuturedays key', -1);
  } else {
    writeToLog($link, 'Unable to create and assign plannedfuturedays key', -1, 'WARN');
    die('Unable to create and assign <code>plannedfuturedays</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('incident_to_show_timerange', '90')") {
    echo 'Created and assigned <code>incident_to_show_timerange</code> key</br>';
    writeToLog($link, 'Created and assigned incident_to_show_timerange key', -1);
  } else {
    writeToLog($link, 'Unable to create and assign incident_to_show_timerange key', -1, 'WARN');
    die('Unable to create and assign <code>incident_to_show_timerange</code> key</br>');
  }
  if ($link->query("INSERT INTO settings (setting_key) VALUES ('about_this_site'), ('enable_sso'), ('entity_id'), ('ga_measurement_id'), ('name_id_format'), ('pes_description'), ('service_provider_base_url'), ('slo_service'), ('sso_service'), ('x509cert'), ('welcome_message'), ('meta_description')")) {
    echo 'Created additional setting keys<br>';
    writeToLog($link, 'Created additional setting keys', -1);
  } else {
    writeToLog($link, 'Unable to create additional setting keys', -1, 'WARN');
    die('Unable to create additional setting keys<br>');
  }
  writeToLog($link, 'Hashing root user password', -1);
  $mempass = password_hash($_POST['rootpass'], PASSWORD_DEFAULT);
  if ($link->query("INSERT INTO users (user_first_name, user_last_name, user_email, user_password) VALUES ('Root', 'User', 'root@localhost', '" . $mempass . "')")) {
    echo 'Created root user<br>';
    writeToLog($link, 'Created root user', -1);
  } else {
    echo 'Failed to create root user<br>';
    writeToLog($link, 'Failed to create root user', -1, 'WARN');
  }
  unset($mempass);
  writeToLog($link, 'Closing database connection now!', -1);
  $link->close();
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
<?php
} else {
  die('Unable to connect to MySQL or MariaDB server');
}
?>
