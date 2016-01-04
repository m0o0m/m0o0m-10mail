CREATE TABLE `{prefix}address` (
  `address_id` varchar(32) NOT NULL,
  `address_email` varchar(100) NOT NULL,
  `address_timestamp` int(12) NOT NULL default '0',
  `address_ip` varchar(50) default NULL,
  `address_hostname` varchar(255) default NULL,
  `address_user_agent` text,
  `address_session_id` varchar(32) NOT NULL,
  KEY `address_id` (`address_id`)
);


CREATE TABLE `{prefix}mail` (
  `mail_id` int(12) NOT NULL default '0',
  `mail_address_id` varchar(32) NOT NULL,
  `mail_from` text,
  `mail_subject` text,
  `mail_excerpt` text,
  `mail_body` text,
  `mail_character_set` varchar(20) NULL,
  `mail_timestamp` int(12) NOT NULL default '0',
  `mail_read` int(1) NOT NULL default '0',
  KEY `mail_address_id` (`mail_address_id`),
  KEY `mail_id_mail_address_id` (`mail_id`,`mail_address_id`)
);


CREATE TABLE IF NOT EXISTS `{prefix}setting` (
  `setting_name` varchar(250) NOT NULL,
  `setting_value` text,
  KEY `setting_name` (`setting_name`)
);