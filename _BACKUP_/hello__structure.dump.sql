----
-- phpLiteAdmin database dump (https://www.phpliteadmin.org/)
-- phpLiteAdmin version: 1.9.9-dev
-- Exported: 5:18pm on September 7, 2019 (CEST)
-- database file: ./hello.sqlite
----
BEGIN TRANSACTION;

----
-- Table structure for g_blog
----
CREATE TABLE 'g_blog' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'locale' TEXT, 'permalink' TEXT,'creation_date' DATETIME, 'last_edit_date' DATETIME, 'title' TEXT, 'post' TEXT);
COMMIT;
