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
  echo 'Creating database schema<br>';
  echo 'Dropping foreign key constraints before dropping tables<br>';
  if ($link->query("ALTER TABLE `incident` DROP FOREIGN KEY IF EXISTS `incident_ibfk_1`")) {
    echo 'Constraint <code>incident_ibfk_1</code> dropped if exists<br>';
  } else {
    echo 'Unable to drop <code>incident_ibfk_1</code> or insufficient permissions</br>';
  }
  if ($link->query("ALTER TABLE `incident_update` DROP FOREIGN KEY IF EXISTS `incident_update_ibfk_1`")) {
    echo 'Constraint <code>incident_update_ibfk_1</code> dropped if exists<br>';
  } else {
    echo 'Unable to drop <code>incident_update_ibfk_1</code> or insufficient permissions</br>';
  }
  if ($link->query("ALTER TABLE `incident_update` DROP FOREIGN KEY IF EXISTS `incident_update_ibfk_2`")) {
    echo 'Constraint <code>incident_update_ibfk_2</code> dropped if exists<br>';
  } else {
    echo 'Unable to drop <code>incident_update_ibfk_2</code> or insufficient permissions</br>';
  }
  if ($link->query("ALTER TABLE `services` DROP FOREIGN KEY IF EXISTS `services_ibfk_1`")) {
    echo 'Constraint <code>services_ibfk_1</code> dropped if exists<br>';
  } else {
    echo 'Unable to drop <code>services_ibfk_1</code> or insufficient permissions</br>';
  }
  if ($link->query("ALTER TABLE `services` DROP FOREIGN KEY IF EXISTS `services_ibfk_2`")) {
    echo 'Constraint <code>services_ibfk_2</code> dropped if exists<br>';
  } else {
    echo 'Unable to drop <code>services_ibfk_2</code> or insufficient permissions</br>';
  }
  if ($link->query("DROP TABLE IF EXISTS users")) {
    echo 'Table <code>users</code> dropped if exists<br>';
  } else {
    die('Unable to drop <code>users</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `users` ( `user_id` int(8) NOT NULL AUTO_INCREMENT, `user_first_name` varchar(64) DEFAULT NULL, `user_last_name` varchar(64) DEFAULT NULL, `user_email` varchar(100) DEFAULT NULL, `user_password` varchar(255) DEFAULT NULL, PRIMARY KEY (`user_id`)) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>users</code> created<br>';
  } else {
    die('Unable to create <code>users</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS settings")) {
    echo 'Table <code>settings</code> dropped if exists<br>';
  } else {
    die('Unable to drop <code>settings</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `settings` ( `setting_key` varchar(32) NOT NULL, `setting_value` varchar(1337) DEFAULT NULL, PRIMARY KEY (`setting_key`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>users</code> created<br>';
  } else {
    die('Unable to create <code>users</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS pes")) {
    echo 'Table <code>pes</code> dropped if exists<br>';
  } else {
    die('Unable to drop <code>pes</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `pes` ( `pes_id` int(11) NOT NULL AUTO_INCREMENT, `pes_issue_summary` varchar(5000) DEFAULT NULL, `pes_issue_service_impact` varchar(5000) DEFAULT NULL, `pes_date` date DEFAULT curdate(), `pes_title` varchar(255) DEFAULT NULL, PRIMARY KEY (`pes_id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>pes</code> created<br>';
  } else {
    die('Unable to create <code>pes</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS incident_status")) {
    echo 'Table <code>incident_status</code> dropped if exists<br>';
  } else {
    die('Unable to drop <code>incident_status</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `incident_status` ( `incident_status_code` varchar(3) NOT NULL, `incident_status_description` varchar(64) DEFAULT NULL, PRIMARY KEY (`incident_status_code`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>incident_status</code> created<br>';
  } else {
    die('Unable to create <code>incident_status</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS service_status")) {
    echo 'Table <code>service_status</code> dropped if exists<br>';
  } else {
    die('Unable to drop <code>service_status</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `service_status` ( `service_status_code` varchar(3) NOT NULL, `service_status_description` varchar(64) DEFAULT NULL, PRIMARY KEY (`service_status_code`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>service_status</code> created<br>';
  } else {
    die('Unable to create <code>service_status</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS servicegroups")) {
    echo 'Table <code>servicegroups</code> dropped if exists<br>';
  } else {
    die('Unable to drop <code>servicegroups</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `servicegroups` ( `servicegroup_id` int(8) NOT NULL AUTO_INCREMENT, `servicegroup_name` varchar(64) DEFAULT NULL, PRIMARY KEY (`servicegroup_id`)) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>servicegroups</code> created<br>';
  } else {
    die('Unable to create <code>servicegroups</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS services")) {
    echo 'Table <code>services</code> dropped if exists<br>';
  } else {
    die('Unable to drop <code>services</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `services` ( `service_id` int(8) NOT NULL AUTO_INCREMENT, `service_name` varchar(64) DEFAULT NULL, `servicegroup_id` int(8) DEFAULT NULL, `service_description` varchar(144) DEFAULT NULL, `service_status_short` varchar(3) DEFAULT NULL, PRIMARY KEY (`service_id`), KEY `servicegroup_id` (`servicegroup_id`), KEY `service_status_short` (`service_status_short`), CONSTRAINT `services_ibfk_1` FOREIGN KEY (`servicegroup_id`) REFERENCES `servicegroups` (`servicegroup_id`), CONSTRAINT `services_ibfk_2` FOREIGN KEY (`service_status_short`) REFERENCES `service_status` (`service_status_code`)) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>services</code> created<br>';
  } else {
    die('Unable to create <code>services</code>');
  }
  if ($link->query("DROP TABLE IF EXISTS incident")) {
    echo 'Table <code>incident</code> dropped if exists<br>';
  } else {
    die('Unable to drop <code>incident</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `incident` (`incident_id` int(8) NOT NULL AUTO_INCREMENT, `incident_date` date DEFAULT curdate(), `incident_description` varchar(255) DEFAULT NULL, `incident_status_short` varchar(3) DEFAULT NULL, `incident_describes_ids` varchar(96) DEFAULT NULL, PRIMARY KEY (`incident_id`), KEY `incident_status_short` (`incident_status_short`), CONSTRAINT `incident_ibfk_1` FOREIGN KEY (`incident_status_short`) REFERENCES `incident_status` (`incident_status_code`)) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>incident</code> created<br>';
  } else {
    die('Unable to create <code>incident</code> or insufficient permissions<br>');
  }
  if ($link->query("DROP TABLE IF EXISTS incident_update")) {
    echo 'Table <code>incident_update</code> dropped if exists<br>';
  } else {
    die('Unable to drop <code>incident_update</code> or insufficient permissions<br>');
  }
  if ($link->query("CREATE TABLE `incident_update` ( `incident_update_id` int(8) NOT NULL AUTO_INCREMENT, `incident_update_timestamp` datetime DEFAULT current_timestamp(), `incident_update_status_short` varchar(3) DEFAULT NULL, `incident_update_description` varchar(2000) DEFAULT NULL, `incident_update_incident_id` int(8) DEFAULT NULL, PRIMARY KEY (`incident_update_id`), KEY `incident_update_status_short` (`incident_update_status_short`), KEY `incident_update_incident_id` (`incident_update_incident_id`), CONSTRAINT `incident_update_ibfk_1` FOREIGN KEY (`incident_update_status_short`) REFERENCES `incident_status` (`incident_status_code`), CONSTRAINT `incident_update_ibfk_2` FOREIGN KEY (`incident_update_incident_id`) REFERENCES `incident` (`incident_id`)) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci")) {
    echo 'Table <code>incident_update</code> created<br>';
  } else {
    die('Unable to create <code>incident_update</code> or insufficient permissions<br>');
  }
  if ($link->query("INSERT INTO incident_status VALUES ('IDE', 'Identified'), ('INV', 'Investigating'), ('MON', 'Monitoring'), ('PLA', 'Planned Maintenance'), ('RES', 'Resolved')")) {
    echo 'Inserted initial incident status codes<br>';
  } else {
    die('Unable to insert initial incident status codes<br>');
  }
  if ($link->query("INSERT INTO service_status VALUES ('DEG', 'Degraded'), ('MAJ', 'Major Outage'), ('MIN', 'Minor Outage'), ('OPE', 'Operational'),  ('PLA', 'Planned Maintenance')")) {
    echo 'Inserted initial service status codes<br>';
  } else {
    die('Unable to insert initial service status codes<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('software_version', '0.0.1c (`Burtnett`)')")) {
    echo 'Created and assigned <code>software_version</code> key<br>';
  } else {
    die('Unable to create and assign <code>software_version</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('database_version', '0.0.1')")) {
    echo 'Created and assigned <code>database_version</code> key<br>';
  } else {
    die('Unable to create and assign <code>database_version</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('footer_org', '" . mysqli_real_escape_string($link, $_POST['organization']) . "')")) {
    echo 'Created and assigned <code>footer_org</code> key<br>';
  } else {
    die('Unable to create and assign <code>footer_org</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('org_link', '" . mysqli_real_escape_string($link, $_POST['organizationwebpage']) . "')")) {
    echo 'Created and assigned <code>org_link</code> key<br>';
  } else {
    die('Unable to create and assign <code>org_link</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('feedback_link', '" . mysqli_real_escape_string($link, $_POST['feedbackwebpage']) . "')")) {
    echo 'Created and assigned <code>feedback_link</code> key<br>';
  } else {
    die('Unable to create and assign <code>feedback_link</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('privacy_policy_link', '" . mysqli_real_escape_string($link, $_POST['privacypolicywebpage']) . "')")) {
    echo 'Created and assigned <code>privacy_policy_link</code> key<br>';
  } else {
    die('Unable to create and assign <code>privacy_policy_link</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('header_image_location', '" . mysqli_real_escape_string($link, $_POST['headerimageurl']) . "')")) {
    echo 'Created and assigned <code>header_image_location</code> key<br>';
  } else {
    die('Unable to create and assign <code>header_image_location</code> key<br>');
  }
  if ($link->query("INSERT INTO settings VALUES ('get_help_destination', '" . mysqli_real_escape_string($link, $_POST['gethelpdestination']) . "')")) {
    echo 'Created and assigned <code>get_help_destination</code> key<br>';
  } else {
    die('Unable to create and assign <code>get_help_destination</code> key<br>');
  }
  if ($link->query("INSERT INTO settings (setting_key) VALUES ('about_this_site'), ('enable_sso'), ('entity_id'), ('ga_measurement_id'), ('name_id_format'), ('pes_description'), ('service_provider_base_url'), ('slo_service'), ('sso_service'), ('x509cert')")) {
    echo 'Created and assigned <code>get_help_destination</code> key<br>';
  } else {
    die('Unable to create and assign <code>get_help_destination</code> key<br>');
  }
  $mempass = password_hash($_POST['rootpass'], PASSWORD_DEFAULT);
  if ($link->query("INSERT INTO users (user_first_name, user_last_name, user_email, user_password) VALUES ('Root', 'User', 'root@localhost', '" . $mempass . "')")) {
    echo 'Created root user<br>';
  } else {
    echo 'Failed to create root user<br>';
  }
  unset($mempass);
  $link->close();
  echo '======== END INSTALLATION LOG ========<br><br>';
  echo 'Installation has finished successfully!<br>You should delete the install directory to prevent these settings from being overwritten.';
} else {
  die('Unable to connect to MySQL or MariaDB server');
}
?>
