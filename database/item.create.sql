CREATE TABLE collectionitem
(
	itemid int NOT NULL,
	itemguid varchar(36) NOT NULL,
	createdate datetime NOT NULL,
	createuser int DEFAULT 1,
	modifydate datetime NOT NULL,
	modifyuser int DEFAULT 1,
	collectionid int NOT NULL,
	itemreference varchar(255),
	itemname varchar(512) NOT NULL,
	attribution varchar(512),
	location varchar(512),
	PRIMARY KEY (itemid),
	UNIQUE(itemguid)
)

CREATE TABLE itemmeta
(
	metaid int NOT NULL,
	itemid int NOT NULL,
	createdate datetime NOT NULL,
	createuser int DEFAULT 1,
	modifydate datetime NOT NULL,
	modifyuser int DEFAULT 1,
	metakey varchar(50) NOT NULL,
	metavalue varchar(255),
	PRIMARY KEY (metaid)
)