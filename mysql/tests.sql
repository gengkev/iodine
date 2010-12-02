DROP TABLE IF EXISTS tests;
CREATE TABLE tests(
	id int(11) UNIQUE NOT NULL auto_increment,
	PRIMARY KEY(`id`),
	time DATETIME NULL DEFAULT NULL,
	type VARCHAR(255) NOT NULL
);