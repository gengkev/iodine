/*
** Defines groups and maps them to names.
*/
DROP TABLE IF EXISTS groups;
CREATE TABLE groups(
	gid MEDIUMINT UNSIGNED UNIQUE NOT NULL DEFAULT NULL AUTO_INCREMENT,
	PRIMARY KEY(gid),
	name VARCHAR(128) NOT NULL DEFAULT '',
	description VARCHAR(255) NOT NULL DEFAULT ''
);
