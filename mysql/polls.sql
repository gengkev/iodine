DROP TABLE IF EXISTS polls;
CREATE TABLE polls (
		  pid MEDIUMINT(8) UNSIGNED DEFAULT NULL AUTO_INCREMENT,
		  PRIMARY KEY(pid),
		  name VARCHAR(128),
		  introduction TEXT,
		  visible TINYINT(1) UNSIGNED,
		  startdt DATETIME DEFAULT 0,
		  enddt DATETIME DEFAULT 0
);
