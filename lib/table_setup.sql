DROP DATABASE IF EXISTS tvplan;
CREATE DATABASE tvplan;
USE tvplan;

DROP TABLE IF EXISTS tp_users;
CREATE TABLE tp_users (
	id INT NOT NULL AUTO_INCREMENT, 
    username varchar(30),
    password varchar(32),
    email varchar(32),
    admin tinyint(1) NOT NULL default '0',
    login timestamp DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY(id)
 );

INSERT INTO tp_users VALUES (
	NULL,
    'admin',
    MD5('somepass'),
    'admin@someemail.com',
    1,
    NOW()
);


DROP TABLE IF EXISTS tp_user_gavin_SHOWINDEX;
CREATE TABLE tp_user_admin_SHOWINDEX (
	id INT NOT NULL AUTO_INCREMENT, 
    full_name varchar(255),
    short_name varchar(255),
	PRIMARY KEY(id)
);

DROP TABLE IF EXISTS tp_user_gavin_NEWSINDEX;
CREATE TABLE tp_user_admin_NEWSINDEX (
	id INT NOT NULL AUTO_INCREMENT, 
    date datetime,
    series varchar(255),
    title varchar(255),
    text text,
	PRIMARY KEY(id)
);
