
CREATE TABLE IF NOT EXISTS `#__wbty_users_components` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(255) NOT NULL,
  `asset_id` varchar(255) NOT NULL,
  `base_user_group` varchar(255) NOT NULL,
  `user_form` varchar(255) NOT NULL,
  `base_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__wbty_users_component_assets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `asset_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `base_user_group` varchar(255) NOT NULL,
  `component_id` int(11) NOT NULL,
  `base_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__wbty_users_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `field_type` varchar(20) NOT NULL,
  `list_view` int(11) NOT NULL,
  `registration_view` int(11) NOT NULL,
  `base_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__wbty_users_field_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `field_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=195 ;

CREATE TABLE IF NOT EXISTS `#__wbty_users_field_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `display` varchar(50) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

INSERT INTO `#__wbty_users_field_types` (`id`, `display`, `value`) VALUES
(1, 'Text field', 'J_TEXT'),
(2, 'Textarea field', 'J_TEXTAREA'),
(3, 'Checkbox', 'J_CHECKBOX'),
(4, 'List (Select list)', 'J_LIST'),
(5, 'Radio buttons (Multiple)', 'J_RADIO'),
(6, 'Editor (WYSIWYG)', 'J_EDITOR'),
(7, 'Password field', 'J_PASSWORD'),
(8, 'Select Integer list', 'J_INTEGER'),
(9, 'Calendar (Date Selector)', 'J_CALENDAR'),
(10, 'Hidden field', 'J_HIDDEN'),
(11, 'Languages (Select List)', 'J_LANGUAGE'),
(12, 'Media Manager', 'J_MEDIA'),
(13, 'SQL Field (Select list of rows)', 'J_SQL'),
(14, 'Users (Select List)', 'J_USER'),
(15, 'Timezones (Select List)', 'J_TIMEZONE'),
(16, 'Joomla Category', 'J_CATEGORY'),
(17, 'Foreign Key (Select from list)', 'FOREIGN_KEY'),
(18, 'Checkbox SQL', 'J_CHECKBOXSQL'),
(19, 'Modal SQL Field', 'J_MODALSQL'),
(20, 'File Upload', 'J_FILE'),
(21, 'Checkboxes', 'J_WBTYCHECKBOXES');

CREATE TABLE IF NOT EXISTS `#__wbty_users_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `display` varchar(50) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

INSERT INTO `#__wbty_users_filters` (`id`, `display`, `value`) VALUES
(1, 'Rules', 'rules'),
(2, 'Unset', 'unset'),
(3, 'Raw', 'raw'),
(4, 'Safe HTML', 'safehtml'),
(5, 'Integer Array', 'int_array'),
(6, 'Server UTC', 'server_utc'),
(7, 'User UTC', 'user_utc'),
(8, 'URL', 'url'),
(9, 'Telephone Number', 'tel');

CREATE TABLE `#__wbty_users_organizations` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `ordering` int(11) NOT NULL,
 `state` tinyint(1) NOT NULL DEFAULT '1',
 `checked_out` int(11) NOT NULL,
 `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `created_by` int(11) NOT NULL,
 `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `modified_by` int(11) NOT NULL,
 `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `name` varchar(255) NOT NULL,
 `group_id` int(11) NOT NULL,
 `base_id` int(11) NOT NULL,
 `admin_org` tinyint(4) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__wbty_users_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordering` int(11) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '1',
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL,
  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL,
  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
