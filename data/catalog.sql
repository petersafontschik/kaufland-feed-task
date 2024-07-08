CREATE TABLE catalog(
 	entity_id  INTEGER NOT NULL PRIMARY KEY,
 	CategoryName  TEXT NOT NULL,
 	sku  TEXT NOT NULL,
 	name  TEXT NOT NULL,
 	shortdesc  TEXT NOT NULL,
 	price  REAL NOT NULL,
 	link  TEXT NOT NULL,
 	image  TEXT NOT NULL,
 	Brand  TEXT NOT NULL,
 	Rating  INTEGER NOT NULL,
 	CaffeineType  TEXT NOT NULL,
 	Count  INTEGER NOT NULL,
 	Flavored  TEXT NOT NULL,
 	Seasonal  TEXT NOT NULL,
 	Instock  TEXT NOT NULL,
 	Facebook  INTEGER NOT NULL,
 	IsKCup  INTEGER NOT NULL 
);
.mode csv
.import catalog.csv catalog
