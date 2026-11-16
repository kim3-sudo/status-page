-- Status Page schema for PostgreSQL. Used by install/run.php on FRESH installs
-- only: it drops and recreates every table. Upgrades of existing installs go
-- through install/migrations/ instead. Keep this file in step with
-- install/schema/mysql.sql.
--
-- Timestamps are stored in UTC for rows where incident_update_utc is true.
-- Rows without that flag are site-local (see lib/time.php).

DROP TABLE IF EXISTS incident_service, incident_update, incident, services, servicegroups,
  service_status, incident_status, pes, settings, apikeys, users, log CASCADE;

CREATE TABLE log (
  log_id SERIAL PRIMARY KEY,
  log_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  log_entry VARCHAR(140),
  log_user_id INTEGER NOT NULL,
  log_type VARCHAR(4) DEFAULT 'INFO'
);

CREATE TABLE users (
  user_id SERIAL PRIMARY KEY,
  user_first_name VARCHAR(64) DEFAULT NULL,
  user_last_name VARCHAR(64) DEFAULT NULL,
  user_email VARCHAR(100) DEFAULT NULL,
  user_password VARCHAR(255) DEFAULT NULL,
  user_issuperuser BOOLEAN DEFAULT FALSE,
  user_totpenabled BOOLEAN DEFAULT FALSE,
  user_totpsecret VARCHAR(255) DEFAULT NULL
);

CREATE TABLE settings (
  setting_key VARCHAR(32) NOT NULL PRIMARY KEY,
  setting_value VARCHAR(1337) DEFAULT NULL
);

CREATE TABLE pes (
  pes_id SERIAL PRIMARY KEY,
  pes_issue_summary VARCHAR(5000) DEFAULT NULL,
  pes_issue_service_impact VARCHAR(5000) DEFAULT NULL,
  pes_date DATE DEFAULT CURRENT_DATE,
  pes_title VARCHAR(255) DEFAULT NULL
);

CREATE TABLE incident_status (
  incident_status_code VARCHAR(3) NOT NULL PRIMARY KEY,
  incident_status_description VARCHAR(64) DEFAULT NULL
);

CREATE TABLE service_status (
  service_status_code VARCHAR(3) NOT NULL PRIMARY KEY,
  service_status_description VARCHAR(64) DEFAULT NULL
);

CREATE TABLE servicegroups (
  servicegroup_id SERIAL PRIMARY KEY,
  servicegroup_name VARCHAR(64) DEFAULT NULL
);

CREATE TABLE services (
  service_id SERIAL PRIMARY KEY,
  service_name VARCHAR(64) DEFAULT NULL,
  servicegroup_id INTEGER DEFAULT NULL REFERENCES servicegroups (servicegroup_id),
  service_description VARCHAR(144) DEFAULT NULL,
  service_link VARCHAR(120) DEFAULT NULL
);
CREATE INDEX services_servicegroup_id ON services (servicegroup_id);

-- Which services an incident affects is stored in incident_service. Service status
-- is derived from incidents when it is read, so services has no status column.
CREATE TABLE incident (
  incident_id SERIAL PRIMARY KEY,
  incident_date DATE DEFAULT CURRENT_DATE,
  incident_description VARCHAR(255) DEFAULT NULL,
  incident_status_short VARCHAR(3) DEFAULT NULL REFERENCES incident_status (incident_status_code)
);
CREATE INDEX incident_incident_status_short ON incident (incident_status_short);

CREATE TABLE incident_update (
  incident_update_id SERIAL PRIMARY KEY,
  incident_update_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  incident_update_status_short VARCHAR(3) DEFAULT NULL REFERENCES incident_status (incident_status_code),
  incident_update_description VARCHAR(2000) DEFAULT NULL,
  incident_update_incident_id INTEGER DEFAULT NULL REFERENCES incident (incident_id),
  incident_update_is_planned_maint VARCHAR(2) DEFAULT NULL,
  incident_update_utc BOOLEAN NOT NULL DEFAULT FALSE
);
CREATE INDEX incident_update_status_short ON incident_update (incident_update_status_short);
CREATE INDEX incident_update_incident_id ON incident_update (incident_update_incident_id);
CREATE INDEX incident_update_incident_time ON incident_update (incident_update_incident_id, incident_update_timestamp);

-- Which services an incident affects, and how severely. One row per pair.
CREATE TABLE incident_service (
  incident_id INTEGER NOT NULL REFERENCES incident (incident_id),
  service_id INTEGER NOT NULL REFERENCES services (service_id),
  severity VARCHAR(3) NOT NULL DEFAULT 'MIN' REFERENCES service_status (service_status_code),
  PRIMARY KEY (incident_id, service_id)
);
CREATE INDEX incident_service_service_id ON incident_service (service_id);

CREATE TABLE apikeys (
  apikeys_id SERIAL PRIMARY KEY,
  apikeys_user_id INTEGER DEFAULT NULL REFERENCES users (user_id),
  apikeys_key_hash VARCHAR(64) DEFAULT NULL,
  apikeys_nickname VARCHAR(128) DEFAULT NULL,
  apikeys_is_personal BOOLEAN DEFAULT NULL
);
CREATE UNIQUE INDEX apikeys_key_hash_unique ON apikeys (apikeys_key_hash);

INSERT INTO incident_status (incident_status_code, incident_status_description) VALUES
  ('IDE', 'Identified'), ('INV', 'Investigating'), ('MON', 'Monitoring'),
  ('PLA', 'Planned Maintenance'), ('RES', 'Resolved');

INSERT INTO service_status (service_status_code, service_status_description) VALUES
  ('DEG', 'Degraded'), ('MAJ', 'Major Outage'), ('MIN', 'Minor Outage'),
  ('OPE', 'Operational'), ('PLA', 'Planned Maintenance');
