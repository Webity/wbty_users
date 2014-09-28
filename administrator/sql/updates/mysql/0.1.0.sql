# Add admin flag to organizations table;
ALTER TABLE  `#__wbty_users_organizations` ADD  `admin_org` TINYINT NOT NULL DEFAULT  '0';
