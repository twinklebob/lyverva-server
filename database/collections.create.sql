CREATE TABLE collection
(
	collectionid int NOT NULL,
	collectionguid varchar(36) NOT NULL,
	createdate datetime NOT NULL,
	createuser int DEFAULT 1,
	modifydate datetime NOT NULL,
	modifyuser int DEFAULT 1,
	libraryid int NOT NULL,
	collectionname varchar(255) NOT NULL,
	collectiontype int NOT NULL,
	location varchar(255),
	PRIMARY KEY (collectionid),
	UNIQUE(collectionguid)
)