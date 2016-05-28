-- *********************************************
-- * Standard SQL generation                   
-- *--------------------------------------------
-- * DB-MAIN version: 9.3.0              
-- * Generator date: Feb 12 2016              
-- * Generation date: Sat May 28 09:25:04 2016 
-- * LUN file: /home/tknapp/Entwicklung/tinc-web-management/tinc-web-management.lun 
-- * Schema: SCHEMA/SQL 
-- ********************************************* 


-- Database Section
-- ________________ 

drop database if exists `tinc2`;
create database `tinc2`;
use `tinc2`;


-- DBSpace Section
-- _______________
ALTER SCHEMA `tinc2` DEFAULT CHARACTER SET utf8  DEFAULT COLLATE utf8_general_ci ;

-- Tables Section
-- _____________ 

create table Network (
     name varchar(16) not null,
     subnet varchar(15) not null,
     subnetWeight numeric(2) not null,
     createdAt timestamp not null default current_timestamp,
     deleted boolean not null default false,
     seq bigint unsigned not null, -- Sequence for Sync
     constraint ID_Network_ID primary key (name));

create table Node (
     id int unsigned not null,
     name varchar(16) not null,
     address varchar(256),
     port smallint unsigned default 655, -- 0 to 65535
     inviteCode varchar(256) not null,
     active boolean not null default false,
     createdAt timestamp not null default current_timestamp,
     deleted boolean not null default false,
     seq bigint unsigned not null, -- Sequence for Sync
     networkName varchar(16) not null,
     constraint ID_Node_ID primary key (id),
     unique key (name, networkName));

create table _Sequence (
     name varchar(16) not null,
     value bigint unsigned not null,
     constraint ID_Sequence_ID primary key (name));


-- Constraints Section
-- ___________________ 

alter table Node add constraint REF_Node_Netwo_FK
     foreign key (networkName)
     references Network (name);


-- Index Section
-- _____________ 

create unique index ID_Network_IND
     on Network (name);

create unique index ID_Node_IND
     on Node (id);

create index REF_Node_Netwo_IND
     on Node (networkName);

create unique index ID_Sequence_IND
     on _Sequence (name);

-- Function Section
-- ________________

DELIMITER $$
CREATE FUNCTION `getNextSeq`(sSeqName VARCHAR (16)) RETURNS bigint unsigned
BEGIN
    DECLARE nLast_val BIGINT UNSIGNED;
	SET nLast_val = (SELECT `value` 
					 FROM _Sequence 
					 WHERE `name` = sSeqName);

    IF nLast_val IS NULL THEN
        SET nLast_val = 1;
        INSERT INTO _Sequence(`name`, `value`) 
		VALUES (sSeqName, nLast_Val);
    ELSE
        SET nLast_val = nLast_val + 1;
		UPDATE _Sequence SET `value` = nLast_val
        WHERE `name` = sSeqName;
    END IF;

RETURN nLast_val;
END
$$


-- Trigger Section
-- _______________

DELIMITER $$
CREATE TRIGGER `Network_BINS` BEFORE INSERT ON `Network` FOR EACH ROW
BEGIN
    SET NEW.seq = getNextSeq("sync");
END
$$

DELIMITER $$
CREATE TRIGGER `Network_BUPD` BEFORE UPDATE ON `Network` FOR EACH ROW
BEGIN
    SET NEW.seq = getNextSeq("sync");
END
$$

DELIMITER $$
CREATE TRIGGER `Node_BINS` BEFORE INSERT ON `Node` FOR EACH ROW
BEGIN
    SET NEW.seq = getNextSeq("sync");
END
$$

DELIMITER $$
CREATE TRIGGER `Node_BUPD` BEFORE UPDATE ON `Node` FOR EACH ROW
BEGIN
    SET NEW.seq = getNextSeq("sync");
END
$$
