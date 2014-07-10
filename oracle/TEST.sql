/*
Navicat Oracle Data Transfer
Oracle Client Version : 11.2.0.3.0

Source Server         : oracle:localhost
Source Server Version : 100200
Source Host           : 192.168.1.113:1521
Source Schema         : ZENWAY

Target Server Type    : ORACLE
Target Server Version : 100200
File Encoding         : 65001

Date: 2014-07-10 16:07:24
*/


-- ----------------------------
-- Table structure for TEST
-- ----------------------------
DROP TABLE "ZENWAY"."TEST";
CREATE TABLE "ZENWAY"."TEST" (
"ID" NUMBER NOT NULL ,
"SS" VARCHAR2(100 BYTE) NULL ,
"DD" DATE NULL ,
"TE" CLOB NULL 
)
LOGGING
NOCOMPRESS
NOCACHE

;

-- ----------------------------
-- Indexes structure for table TEST
-- ----------------------------

-- ----------------------------
-- Checks structure for table TEST
-- ----------------------------
ALTER TABLE "ZENWAY"."TEST" ADD CHECK ("ID" IS NOT NULL);

-- ----------------------------
-- Primary Key structure for table TEST
-- ----------------------------
ALTER TABLE "ZENWAY"."TEST" ADD PRIMARY KEY ("ID");
