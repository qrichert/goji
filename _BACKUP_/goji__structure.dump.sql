----
-- phpLiteAdmin database dump (https://www.phpliteadmin.org/)
-- phpLiteAdmin version: 1.9.9-dev
-- Exported: 3:53pm on November 12, 2019 (CET)
-- database file: ./db/goji.sqlite3
----
BEGIN TRANSACTION;

----
-- Table structure for g_blog
----
CREATE TABLE 'g_blog' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'locale' TEXT, 'permalink' TEXT,'creation_date' DATETIME, 'last_edit_date' DATETIME, 'title' TEXT, 'post' TEXT);

----
-- Table structure for g_member
----
CREATE TABLE 'g_member' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'username' TEXT, 'password' TEXT, 'role' INTEGER, 'date_registered' DATETIME);

----
-- Table structure for g_member_tmp
----
CREATE TABLE 'g_member_tmp' ('id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'username' TEXT, 'password' TEXT, 'date_registered' DATETIME);
COMMIT;
