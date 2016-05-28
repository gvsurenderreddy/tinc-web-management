-- *********************************************
-- * Standard SQL generation                   
-- *--------------------------------------------
-- * DB-MAIN version: 9.3.0              
-- * Generator date: Feb 12 2016              
-- * Generation date: Wed May 25 10:56:33 2016 
-- * LUN file: /home/tknapp/Entwicklung/tinc-web-management/tinc-web-management.lun 
-- * Schema: SCHEMA-DB/SQL 
-- ********************************************* 


-- Database Section
-- ________________ 

drop database if exists `tinc`;
create database `tinc`;
use `tinc`;


-- DBSpace Section
-- _______________
ALTER SCHEMA `tinc` DEFAULT CHARACTER SET utf8  DEFAULT COLLATE utf8_general_ci ;


-- Tables Section
-- _____________ 

create table _Delete (
     delSeq bigint unsigned not null, -- Sequence value of deleted entity
     tableName varchar(32) not null,
     seq bigint unsigned not null,
     constraint ID_Delete_ID primary key (delSeq));

create table Network (
     name varchar(16) not null,
     subnet varchar(15) not null,
     prefixLength TINYINT unsigned not null,
     created datetime not null,
     seq bigint unsigned not null,
     constraint ID_Network_ID primary key (name));

create table Node (
     name varchar(16) not null,
     networkName varchar(16) not null,
	 isStatic boolean not null,
     address varchar(256),
     port smallint unsigned,
     inviteCode varchar(256) not null,
     active boolean not null,
     created datetime not null,
     seq bigint unsigned not null,
     constraint ID_Node_ID primary key (name));

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

create unique index ID_Delete_IND
     on _Delete (delSeq);

create unique index ID_Network_IND
     on Network (name);

create unique index ID_Node_IND
     on Node (name, networkName);

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


-- Trigger Section
-- _______________
DELIMITER $$
CREATE TRIGGER `Node_BDEL` BEFORE DELETE ON `Node` FOR EACH ROW
BEGIN
    INSERT INTO _Deletes(`delSeq`,`tableName`,`seq`) 
    VALUES(OLD.seq, "Node", getNextSeq("sync"));
END
