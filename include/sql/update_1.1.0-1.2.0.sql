ALTER TABLE `{prefix}mail` ADD INDEX `mail_id_mail_address_id` ( `mail_id` , `mail_address_id` ) ;
ALTER TABLE `{prefix}address` ADD `address_session_id` VARCHAR( 32 ) NOT NULL ;