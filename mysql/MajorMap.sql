DROP TABLE IF EXISTS MajorMap;
CREATE TABLE MajorMap (
	MajorID TINYINT(4) NOT NULL AUTO_INCREMENT,
	PRIMARY KEY (MajorID),
	Major VARCHAR(64) NOT NULL DEFAULT ''
);
