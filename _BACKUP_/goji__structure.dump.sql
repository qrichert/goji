----
-- phpLiteAdmin database dump (https://www.phpliteadmin.org/)
-- phpLiteAdmin version: 1.9.9-dev
-- Exported: 12:25pm on October 1, 2019 (CEST)
-- database file: ./db/goji.sqlite
----
BEGIN TRANSACTION;

----
-- Table structure for g_blog
----
CREATE TABLE 'g_blog' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'locale' TEXT, 'permalink' TEXT,'creation_date' DATETIME, 'last_edit_date' DATETIME, 'title' TEXT, 'post' TEXT);

----
-- Table structure for g_user
----
CREATE TABLE 'g_user' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'username' TEXT, 'password' TEXT, 'date_registered' DATETIME);
COMMIT;
