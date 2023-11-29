--
-- Table structure for table `incident`
--

DROP TABLE IF EXISTS `incident`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incident` (
  `incident_id` int(8) NOT NULL AUTO_INCREMENT,
  `incident_date` date DEFAULT curdate(),
  `incident_description` varchar(255) DEFAULT NULL,
  `incident_status_short` varchar(3) DEFAULT NULL,
  `incident_describes_ids` varchar(96) DEFAULT NULL,
  PRIMARY KEY (`incident_id`),
  KEY `incident_status_short` (`incident_status_short`),
  CONSTRAINT `incident_ibfk_1` FOREIGN KEY (`incident_status_short`) REFERENCES `incident_status` (`incident_status_code`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `incident_status`
--

DROP TABLE IF EXISTS `incident_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incident_status` (
  `incident_status_code` varchar(3) NOT NULL,
  `incident_status_description` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`incident_status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `incident_update`
--

DROP TABLE IF EXISTS `incident_update`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incident_update` (
  `incident_update_id` int(8) NOT NULL AUTO_INCREMENT,
  `incident_update_timestamp` datetime DEFAULT current_timestamp(),
  `incident_update_status_short` varchar(3) DEFAULT NULL,
  `incident_update_description` varchar(2000) DEFAULT NULL,
  `incident_update_incident_id` int(8) DEFAULT NULL,
  PRIMARY KEY (`incident_update_id`),
  KEY `incident_update_status_short` (`incident_update_status_short`),
  KEY `incident_update_incident_id` (`incident_update_incident_id`),
  CONSTRAINT `incident_update_ibfk_1` FOREIGN KEY (`incident_update_status_short`) REFERENCES `incident_status` (`incident_status_code`),
  CONSTRAINT `incident_update_ibfk_2` FOREIGN KEY (`incident_update_incident_id`) REFERENCES `incident` (`incident_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pes`
--

DROP TABLE IF EXISTS `pes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pes` (
  `pes_id` int(11) NOT NULL AUTO_INCREMENT,
  `pes_issue_summary` varchar(5000) DEFAULT NULL,
  `pes_issue_service_impact` varchar(5000) DEFAULT NULL,
  `pes_date` date DEFAULT curdate(),
  `pes_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`pes_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_status`
--

DROP TABLE IF EXISTS `service_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_status` (
  `service_status_code` varchar(3) NOT NULL,
  `service_status_description` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`service_status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servicegroups`
--

DROP TABLE IF EXISTS `servicegroups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servicegroups` (
  `servicegroup_id` int(8) NOT NULL AUTO_INCREMENT,
  `servicegroup_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`servicegroup_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `services` (
  `service_id` int(8) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(64) DEFAULT NULL,
  `servicegroup_id` int(8) DEFAULT NULL,
  `service_description` varchar(144) DEFAULT NULL,
  `service_status_short` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`service_id`),
  KEY `servicegroup_id` (`servicegroup_id`),
  KEY `service_status_short` (`service_status_short`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`servicegroup_id`) REFERENCES `servicegroups` (`servicegroup_id`),
  CONSTRAINT `services_ibfk_2` FOREIGN KEY (`service_status_short`) REFERENCES `service_status` (`service_status_code`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `setting_key` varchar(32) NOT NULL,
  `setting_value` varchar(1337) DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(8) NOT NULL AUTO_INCREMENT,
  `user_first_name` varchar(64) DEFAULT NULL,
  `user_last_name` varchar(64) DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `user_password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
