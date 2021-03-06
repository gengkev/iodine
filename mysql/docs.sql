DROP TABLE IF EXISTS docs;
CREATE TABLE docs (
	docid MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
	PRIMARY KEY(docid),
	name VARCHAR(128) NOT NULL DEFAULT '',
	path MEDIUMTEXT NOT NULL DEFAULT '',
	visible TINYINT(1) UNSIGNED DEFAULT 0,
	type VARCHAR(30)
);
