CREATE DATABASE IF NOT EXISTS `exactspotter`;
USE `exactspotter`;

CREATE TABLE IF NOT EXISTS `agent_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_number` varchar(80) DEFAULT NULL,
  `agent_name` varchar(80) DEFAULT NULL,
  `exactspotter_id` varchar(80) DEFAULT NULL,
  `exactspotter_group_id` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_number` (`agent_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `call_out_exactspotter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_number` varchar(80) NOT NULL,
  `exactspotter_agent_id` int(10) unsigned DEFAULT NULL,
  `client_number` varchar(80) NOT NULL,
  `channel` varchar(100) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `uniqueid` varchar(80) NOT NULL,
  `duration` int(11) DEFAULT NULL,
  `billsec` int(11) DEFAULT NULL,
  `disposition` varchar(45) NOT NULL,
  `recordingfile` varchar(255) NOT NULL,
  `voice_opened` datetime DEFAULT NULL,
  `recording_sent` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uniqueid` (`uniqueid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

