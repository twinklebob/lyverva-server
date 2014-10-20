CREATE TABLE libraryuser
(
	libraryuserid int NOT NULL,
	createdate datetime NOT NULL,
	createuser int DEFAULT 1,
	modifydate datetime NOT NULL,
	modifyuser int DEFAULT 1,
	libraryid int NOT NULL,
	userid int NOT NULL,
	usertype int NOT NULL,
	PRIMARY KEY (libuserid)
)

CREATE TABLE usertype
(
	usertypeid int NOT NULL,
	description int NOT NULL,
	PRIMARY KEY (usertypeid)
)
