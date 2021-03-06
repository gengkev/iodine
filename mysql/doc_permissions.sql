DROP TABLE IF EXISTS doc_permissions;
CREATE TABLE doc_permissions (
	docid MEDIUMINT(8) UNSIGNED NOT NULL,
	gid MEDIUMINT(8) UNSIGNED NOT NULL,
	PRIMARY KEY(docid,gid),
	view BOOLEAN NOT NULL DEFAULT 1,
	edit BOOLEAN NOT NULL DEFAULT 0
);
