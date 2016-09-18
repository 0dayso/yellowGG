
CREATE TABLE IF NOT EXISTS `cj_report` (
  `id` int(4) NOT NULL COMMENT '自增ID',
  `uid` int(4) NOT NULL COMMENT '举报人UID',
  `offender_uid` int(4) NOT NULL COMMENT '被举报人UID',
  `type` int(4) NOT NULL COMMENT '举报类型',
  `remark` varchar(225) NOT NULL COMMENT '备注',
  `status` int(4) NOT NULL COMMENT '操作状态',
  `dtime` int(4) NOT NULL COMMENT '举报时间',
  `atime` int(4) NOT NULL COMMENT '受理时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/**************************************************************************************/
DROP TABLE IF EXISTS `cj_accusation_req_unallocated`;
DROP TABLE IF EXISTS `cj_accusation_req_allocated`;
DROP TABLE IF EXISTS `cj_accusation_req_processed`;

CREATE TABLE IF NOT EXISTS `cj_accusation_req_unallocated`(
  `id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `accusation_id` int(10) NOT NULL DEFAULT 0 UNIQUE
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='举报业务未分配请求';

CREATE TABLE IF NOT EXISTS `cj_accusation_req_aid_1`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `accusation_id` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='举报业务管理员请求任务单';

CREATE TABLE IF NOT EXISTS `cj_accusation_req_aid_2`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `accusation_id` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='举报业务管理员请求任务单';

CREATE TABLE IF NOT EXISTS `cj_accusation_req_allocated`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `accusation_id` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='举报业务已分配审核请求';

CREATE TABLE IF NOT EXISTS `cj_accusation_req_processed`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `accusation_id` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '处理时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='举报业务已处理审核请求';

/**************************************************************************************/

CREATE TABLE IF NOT EXISTS `cj_accusation_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NOT NULL COMMENT '管理员',
  `uid` int(10) NOT NULL COMMENT '举报人',
  `offender_uid` int(10) NOT NULL COMMENT '被举报人',
  `type` int(4) NOT NULL COMMENT '举报类型',
  `remark` varchar(225) NOT NULL COMMENT '备注',
  `status` int(4) NOT NULL COMMENT '操作状态',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '举报时间',
  `atime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '受理时间',
  `log` varchar(225) NOT NULL COMMENT '日志',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='举报业务受理日志' AUTO_INCREMENT=1 ;

/*insert into online database*/
/**************************************************************************************/
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`status`,`dtime`,`atime`,`dblocking_time`) 
VALUES (50005,50006,1,'',0,1420605026,0,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`status`,`dtime`,`atime`,`dblocking_time`) 
VALUES (50005,50006,1,'',0,1420605126,0,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`status`,`dtime`,`atime`,`dblocking_time`) 
VALUES (50007,50008,2,'',0,1420605226,0,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`status`,`dtime`,`atime`,`dblocking_time`) 
VALUES (50007,50008,2,'',0,1420605027,0,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`status`,`dtime`,`atime`,`dblocking_time`) 
VALUES (50007,50008,2,'',0,1420605028,0,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`status`,`dtime`,`atime`,`dblocking_time`) 
VALUES (50007,50008,2,'',0,1420605029,0,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`status`,`dtime`,`atime`,`dblocking_time`) 
VALUES (50007,50008,2,'',0,1420605030,0,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`status`,`dtime`,`atime`,`dblocking_time`) 
VALUES (50007,50008,2,'',0,1420605031,0,0);

/**************************************************************************************/

/**************************************************************************************/
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`reason`,`status`,`dtime`,`atime`) 
VALUES (16,23,1,'',0,0,1420605026,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`reason`,`status`,`dtime`,`atime`) 
VALUES (16,23,1,'',0,0,1420605126,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`reason`,`status`,`dtime`,`atime`) 
VALUES (25,26,2,'',0,0,1420605226,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`reason`,`status`,`dtime`,`atime`) 
VALUES (25,26,2,'',0,0,1420605027,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`reason`,`status`,`dtime`,`atime`) 
VALUES (25,26,2,'',0,0,1420605028,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`reason`,`status`,`dtime`,`atime`) 
VALUES (25,26,2,'',0,0,1420605029,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`reason`,`status`,`dtime`,`atime`) 
VALUES (25,26,2,'',0,0,1420605030,0);
INSERT INTO `cj_report`(`uid`,`offender_uid`,`type`,`remark`,`reason`,`status`,`dtime`,`atime`) 
VALUES (25,26,2,'',0,0,1420605031,0);

/**************************************************************************************/
/*
*举报业务审核操作
*清空举报业务cj_report及cj_accusation_log中操作存留数据
*同时清空所有请求认证的数据,然后插入新的数据
*同时清空确认提交的数据
*/ 
UPDATE `cj_report` SET 
`status`=0,
`atime`=0,
`dblocking_time`=0
WHERE id in(1,2,3,4,5,6,7,8);

/**************************************************************************************/

/*清空相关数据表*/
TRUNCATE `cj_accusation_req_aid_1`;
TRUNCATE `cj_accusation_req_aid_2`;
TRUNCATE `cj_accusation_req_allocated`;
TRUNCATE `cj_accusation_req_processed`;
TRUNCATE `cj_accusation_req_unallocated`;
/*未分配任务*/
INSERT INTO `cj_accusation_req_unallocated`(`accusation_id`) VALUES (1,23);
INSERT INTO `cj_accusation_req_unallocated`(`accusation_id`) VALUES (2,23);
INSERT INTO `cj_accusation_req_unallocated`(`accusation_id`) VALUES (3,26);
INSERT INTO `cj_accusation_req_unallocated`(`accusation_id`) VALUES (4,26);
INSERT INTO `cj_accusation_req_unallocated`(`accusation_id`) VALUES (5,26);
INSERT INTO `cj_accusation_req_unallocated`(`accusation_id`) VALUES (6,26);
INSERT INTO `cj_accusation_req_unallocated`(`accusation_id`) VALUES (7,26);
INSERT INTO `cj_accusation_req_unallocated`(`accusation_id`) VALUES (8,26);

/**************************************************************************************/


TRUNCATE `cj_accusation_log`;