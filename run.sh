#!/usr/bin/env bash

dbname=bunq.db

sqlite3 $dbname " 
DROP TABLE IF EXISTS users; DROP TABLE IF EXISTS messages; DROP TABLE IF EXISTS message_recipients;
"

sqlite3 $dbname " 
CREATE TABLE IF NOT EXISTS users (
    user_id integer PRIMARY KEY,
    user_name VARCHAR(256)
);"

sqlite3 $dbname "
CREATE TABLE messages (
	message_id INTEGER PRIMARY KEY,
	time_stamp DATETIME,
	message VARCHAR(4000),
	read_status BOOLEAN,
	sender_id integer NOT NULL,
		FOREIGN KEY (sender_id) REFERENCES users(user_id)
);"

sqlite3 $dbname "
CREATE TABLE message_recipients (
	message_id integer NOT NULL,
	recipient_id integer NOT NULL,
		FOREIGN KEY (message_id) REFERENCES messages(message_id),
		FOREIGN KEY (recipient_id) REFERENCES users(user_id)
);"

sqlite3 $dbname "
insert into users (user_name) values (\"nk\");
insert into users (user_name) values (\"rk\");
insert into messages (time_stamp, message, read_status, sender_id) values (datetime('now'), \"hello from nk\", 0, 1);
insert into messages (time_stamp, message, read_status, sender_id) values (datetime('now'), \"hello from rk\", 0, 2);
insert into message_recipients (message_id, recipient_id) values (1, 2);
insert into message_recipients (message_id, recipient_id) values (2, 1);
"
