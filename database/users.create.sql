CREATE TABLE lyvuser
(
	userid int NOT NULL,
	createdate datetime NOT NULL,
	createuser int DEFAULT 1,
	modifydate datetime NOT NULL,
	modifyuser int DEFAULT 1,
	firstname varchar(255) NOT NULL,
	surname varchar(255) NOT NULL,
	email varchar(512) NOT NULL,
	password_hash varchar(512) NOT NULL,
	PRIMARY KEY (userid),
	UNIQUE(email)
)