CREATE TABLE news(
	id INT UNSIGNED NOT NULL DEFAULT NULL AUTO_INCREMENT UNIQUE, /*Unique autoid*/
	PRIMARY KEY(id),				
	title VARCHAR(255) NOT NULL,			/*Story title*/
	text TEXT NOT NULL,			/*Story text*/
	authorID MEDIUMINT UNSIGNED,			/*Student/teacher ID*/
	revised TIMESTAMP NOT NULL DEFAULT 'CURRENT_TIMESTAMP',	/*Date revised*/
	posted TIMESTAMP NOT NULL DEFAULT 'CURRENT_TIMESTAMP',	/*Date posted*/
	KEY(posted)
);
