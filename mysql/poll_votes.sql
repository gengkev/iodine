DROP TABLE IF EXISTS poll_votes;
CREATE TABLE poll_votes (
		  uid MEDIUMINT(8) UNSIGNED DEFAULT 0,
		  pid MEDIUMINT(8) UNSIGNED DEFAULT 0,
		  qid MEDIUMINT(8) UNSIGNED DEFAULT 0,
		  answer MEDIUMINT(8) UNSIGNED DEFAULT 0
);
