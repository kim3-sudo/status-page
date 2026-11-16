-- Status Page schema for MySQL and MariaDB. Used by install/run.php on FRESH
-- installs only: it drops and recreates every table. Upgrades of existing
-- installs go through install/migrations/ instead. Keep this file in step with
-- install/schema/pgsql.sql.
--
-- Timestamps are stored in UTC for rows where incident_update_utc is true.
-- Rows without that flag are site-local (see lib/time.php).

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS incident_service, incident_update, incident, services, servicegroups,
  service_status, incident_status, pes, settings, apikeys, users, log;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE log (
  log_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  log_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  log_entry VARCHAR(140),
  log_user_id INT NOT NULL,
  log_type VARCHAR(4) DEFAULT 'INFO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  user_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  user_first_name VARCHAR(64) DEFAULT NULL,
  user_last_name VARCHAR(64) DEFAULT NULL,
  user_email VARCHAR(100) DEFAULT NULL,
  user_password VARCHAR(255) DEFAULT NULL,
  user_issuperuser BOOLEAN DEFAULT FALSE,
  user_totpenabled BOOLEAN DEFAULT FALSE,
  user_totpsecret VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  setting_key VARCHAR(32) NOT NULL PRIMARY KEY,
  setting_value VARCHAR(1337) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pes (
  pes_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  pes_issue_summary VARCHAR(5000) DEFAULT NULL,
  pes_issue_service_impact VARCHAR(5000) DEFAULT NULL,
  pes_date DATE DEFAULT curdate(),
  pes_title VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE incident_status (
  incident_status_code VARCHAR(3) NOT NULL PRIMARY KEY,
  incident_status_description VARCHAR(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE service_status (
  service_status_code VARCHAR(3) NOT NULL PRIMARY KEY,
  service_status_description VARCHAR(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE servicegroups (
  servicegroup_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  servicegroup_name VARCHAR(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
  service_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  service_name VARCHAR(64) DEFAULT NULL,
  servicegroup_id INT DEFAULT NULL,
  service_description VARCHAR(144) DEFAULT NULL,
  service_link VARCHAR(120) DEFAULT NULL,
  service_status_short VARCHAR(3) DEFAULT NULL,
  KEY servicegroup_id (servicegroup_id),
  KEY service_status_short (service_status_short),
  CONSTRAINT services_ibfk_1 FOREIGN KEY (servicegroup_id) REFERENCES servicegroups (servicegroup_id),
  CONSTRAINT services_ibfk_2 FOREIGN KEY (service_status_short) REFERENCES service_status (service_status_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- incident_describes_ids is the legacy comma-separated list. It is kept, and
-- incident_service is the source of truth from migration 0.3.0 onwards.
CREATE TABLE incident (
  incident_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  incident_date DATE DEFAULT curdate(),
  incident_description VARCHAR(255) DEFAULT NULL,
  incident_status_short VARCHAR(3) DEFAULT NULL,
  incident_describes_ids VARCHAR(96) DEFAULT NULL,
  KEY incident_status_short (incident_status_short),
  CONSTRAINT incident_ibfk_1 FOREIGN KEY (incident_status_short) REFERENCES incident_status (incident_status_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE incident_update (
  incident_update_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  incident_update_timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  incident_update_status_short VARCHAR(3) DEFAULT NULL,
  incident_update_description VARCHAR(2000) DEFAULT NULL,
  incident_update_incident_id INT DEFAULT NULL,
  incident_update_is_planned_maint VARCHAR(2) DEFAULT NULL,
  incident_update_utc BOOLEAN NOT NULL DEFAULT FALSE,
  KEY incident_update_status_short (incident_update_status_short),
  KEY incident_update_incident_id (incident_update_incident_id),
  CONSTRAINT incident_update_ibfk_1 FOREIGN KEY (incident_update_status_short) REFERENCES incident_status (incident_status_code),
  CONSTRAINT incident_update_ibfk_2 FOREIGN KEY (incident_update_incident_id) REFERENCES incident (incident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX incident_update_incident_time ON incident_update (incident_update_incident_id, incident_update_timestamp);

-- Which services an incident affects, and how severely. One row per pair.
CREATE TABLE incident_service (
  incident_id INT NOT NULL,
  service_id INT NOT NULL,
  severity VARCHAR(3) NOT NULL DEFAULT 'MIN',
  PRIMARY KEY (incident_id, service_id),
  KEY incident_service_service_id (service_id),
  CONSTRAINT incident_service_ibfk_1 FOREIGN KEY (incident_id) REFERENCES incident (incident_id),
  CONSTRAINT incident_service_ibfk_2 FOREIGN KEY (service_id) REFERENCES services (service_id),
  CONSTRAINT incident_service_ibfk_3 FOREIGN KEY (severity) REFERENCES service_status (service_status_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE apikeys (
  apikeys_id INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
  apikeys_user_id INT DEFAULT NULL,
  apikeys_key_hash VARCHAR(64) DEFAULT NULL,
  apikeys_nickname VARCHAR(128) DEFAULT NULL,
  apikeys_is_personal BOOLEAN DEFAULT NULL,
  UNIQUE KEY apikeys_key_hash_unique (apikeys_key_hash),
  CONSTRAINT users_ibfk_9 FOREIGN KEY (apikeys_user_id) REFERENCES users (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO incident_status (incident_status_code, incident_status_description) VALUES
  ('IDE', 'Identified'), ('INV', 'Investigating'), ('MON', 'Monitoring'),
  ('PLA', 'Planned Maintenance'), ('RES', 'Resolved');

INSERT INTO service_status (service_status_code, service_status_description) VALUES
  ('DEG', 'Degraded'), ('MAJ', 'Major Outage'), ('MIN', 'Minor Outage'),
  ('OPE', 'Operational'), ('PLA', 'Planned Maintenance');
