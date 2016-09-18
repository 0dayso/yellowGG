# Host: 127.0.0.1  (Version: 5.6.17)
# Date: 2015-02-02 16:49:09
# Generator: MySQL-Front 5.3  (Build 4.13)

/*!40101 SET NAMES utf8 */;

#
# Source for table "cj_accusation_req_aid_1"
#

DROP TABLE IF EXISTS `cj_accusation_req_aid_1`;
CREATE TABLE `cj_accusation_req_aid_1` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `accusation_id` int(10) NOT NULL DEFAULT '0',
  `offender_uid` int(10) NOT NULL DEFAULT '0' COMMENT '被举报人初见号',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COMMENT='举报业务管理员请求任务单';

#
# Data for table "cj_accusation_req_aid_1"
#

INSERT INTO `cj_accusation_req_aid_1` VALUES (11,1,23,1,'2015-01-31 18:24:08'),(12,2,23,1,'2015-01-31 18:24:08');

#
# Source for table "cj_accusation_req_aid_2"
#

DROP TABLE IF EXISTS `cj_accusation_req_aid_2`;
CREATE TABLE `cj_accusation_req_aid_2` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `accusation_id` int(10) NOT NULL DEFAULT '0',
  `offender_uid` int(10) NOT NULL DEFAULT '0' COMMENT '被举报人初见号',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='举报业务管理员请求任务单';

#
# Data for table "cj_accusation_req_aid_2"
#


#
# Source for table "cj_accusation_req_allocated"
#

DROP TABLE IF EXISTS `cj_accusation_req_allocated`;
CREATE TABLE `cj_accusation_req_allocated` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `accusation_id` int(10) NOT NULL DEFAULT '0',
  `offender_uid` int(10) NOT NULL DEFAULT '0' COMMENT '被举报人初见号',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COMMENT='举报业务已分配审核请求';

#
# Data for table "cj_accusation_req_allocated"
#

INSERT INTO `cj_accusation_req_allocated` VALUES (11,1,23,1,'2015-01-31 18:24:08'),(12,2,23,1,'2015-01-31 18:24:08');

#
# Source for table "cj_accusation_req_processed"
#

DROP TABLE IF EXISTS `cj_accusation_req_processed`;
CREATE TABLE `cj_accusation_req_processed` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `accusation_id` int(10) NOT NULL DEFAULT '0',
  `offender_uid` int(10) NOT NULL DEFAULT '0' COMMENT '被举报人初见号',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '处理时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COMMENT='举报业务已处理审核请求';

#
# Data for table "cj_accusation_req_processed"
#

INSERT INTO `cj_accusation_req_processed` VALUES (1,3,26,1,'2015-01-31 18:24:34'),(2,4,26,1,'2015-01-31 18:24:34'),(3,5,26,1,'2015-01-31 18:24:34'),(4,6,26,1,'2015-01-31 18:24:35'),(5,7,26,1,'2015-01-31 18:24:35'),(6,8,26,1,'2015-01-31 18:24:35');

#
# Source for table "cj_accusation_req_unallocated"
#

DROP TABLE IF EXISTS `cj_accusation_req_unallocated`;
CREATE TABLE `cj_accusation_req_unallocated` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `accusation_id` int(10) NOT NULL DEFAULT '0',
  `offender_uid` int(10) NOT NULL DEFAULT '0' COMMENT '被举报人初见号',
  PRIMARY KEY (`id`),
  UNIQUE KEY `accusation_id` (`accusation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='举报业务未分配请求';

#
# Data for table "cj_accusation_req_unallocated"
#


#
# Source for table "cj_accusation_request"
#

DROP TABLE IF EXISTS `cj_accusation_request`;
CREATE TABLE `cj_accusation_request` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `accusation_id` int(4) NOT NULL DEFAULT '0' COMMENT 'cj_report.id',
  `operation` tinyint(4) NOT NULL DEFAULT '0' COMMENT '操作类型：0为未分配，1为已分配，2为已处理，3为取消举报',
  `aid` int(4) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `allocate_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '审核结果数字表示:0为未审核，1为通过认证，2为车辆照不清晰，3为证件照不清晰，4为照片不符，5为驾驶证与行驶证姓名不符',
  `certificate_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  `result` varchar(60) NOT NULL DEFAULT '' COMMENT '审核结果文字表述',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '审核时管理员备注',
  `uid` int(4) NOT NULL DEFAULT '0' COMMENT '用户初见号',
  `offender_uid` int(4) NOT NULL DEFAULT '0' COMMENT '被举报人初见号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

#
# Data for table "cj_accusation_request"
#

INSERT INTO `cj_accusation_request` VALUES (1,1,0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00','','',16,23),(2,2,0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00','','',16,23),(3,3,0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00','','',25,26),(4,4,0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00','','',25,26),(5,5,0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00','','',25,26),(6,6,0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00','','',25,26),(7,7,0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00','','',25,26),(8,8,0,0,'0000-00-00 00:00:00',0,'0000-00-00 00:00:00','','',25,26);

#
# Source for table "cj_action"
#

DROP TABLE IF EXISTS `cj_action`;
CREATE TABLE `cj_action` (
  `action_id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`action_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4;

#
# Data for table "cj_action"
#

INSERT INTO `cj_action` VALUES (1,'视频审核'),(2,'车辆审核'),(3,'删除视频认证记录'),(4,'删除车辆认证记录'),(5,'添加用户'),(6,'删除用户头像'),(7,'任务大厅车辆认证'),(8,'任务大厅视频认证'),(9,'任务大厅分配车辆认证任务'),(10,'任务大厅分配视频认证任务'),(11,'任务大厅拉取车辆认证任务'),(12,'任务大厅拉取视频认证任务'),(13,'管理员退出所在组'),(14,'删除管理员'),(15,'添加管理员进组'),(16,'创建管理员'),(17,'移除管理员组权限'),(18,'添加管理员组权限'),(19,'创建管理员组'),(20,'删除管理员组'),(21,'任务大厅分配举报任务'),(22,'车辆认证任务大厅确认已完成任务'),(23,'视频认真任务大厅确认已完成任务'),(24,'重置车辆认证测试数据'),(25,'重置视频认证测试数据'),(26,'重置举报及封禁测试数据'),(27,'举报任务大厅确认已完成任务'),(28,'文字审查确认全部通过'),(29,'文字审查单个确认'),(30,'重置文字审查测试数据'),(31,'普通管理员举报审核'),(32,'任务大厅举报审核'),(33,'任务大厅分配意见反馈任务'),(34,'重置用户反馈测试数据'),(35,'取消视频认证'),(36,'取消车辆认证'),(37,'修改用户资料'),(38,'解除封禁');

#
# Source for table "cj_action_admin_group"
#

DROP TABLE IF EXISTS `cj_action_admin_group`;
CREATE TABLE `cj_action_admin_group` (
  `action_id` tinyint(1) DEFAULT NULL,
  `admin_group_id` smallint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

#
# Data for table "cj_action_admin_group"
#

INSERT INTO `cj_action_admin_group` VALUES (2,1),(1,3),(2,4),(2,5),(3,1),(4,1),(5,1),(1,6),(1,1),(8,1),(7,1),(6,1),(2,7),(9,1),(10,1),(11,1),(12,1),(18,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(2,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(36,1),(35,1),(37,1),(38,1);

#
# Source for table "cj_admin"
#

DROP TABLE IF EXISTS `cj_admin`;
CREATE TABLE `cj_admin` (
  `aid` smallint(6) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL COMMENT '登录账号',
  `pwd` char(32) DEFAULT NULL COMMENT '登录密码',
  `remark` varchar(255) DEFAULT '' COMMENT '备注信息',
  `find_code` char(5) DEFAULT NULL COMMENT '找回账号验证码',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '开通时间',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

#
# Data for table "cj_admin"
#

INSERT INTO `cj_admin` VALUES (1,'dylenfu','dylenfu@126.com','96e79218965eb72c92a549dd5a330112','密码为111111',NULL,'2014-12-01 10:52:27'),(2,'test','test@kingnet.com','96e79218965eb72c92a549dd5a330112','','0187','2014-12-26 08:29:08');

#
# Source for table "cj_admin_group"
#

DROP TABLE IF EXISTS `cj_admin_group`;
CREATE TABLE `cj_admin_group` (
  `admin_group_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`admin_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

#
# Data for table "cj_admin_group"
#

INSERT INTO `cj_admin_group` VALUES (1,'超级管理员'),(3,'测试-视频审核'),(4,'运营-车辆审核'),(5,'测试-车辆审核'),(6,'运营-视频审核'),(7,'测试');

#
# Source for table "cj_admin_group_admin"
#

DROP TABLE IF EXISTS `cj_admin_group_admin`;
CREATE TABLE `cj_admin_group_admin` (
  `aid` smallint(6) DEFAULT NULL,
  `admin_group_id` smallint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

#
# Data for table "cj_admin_group_admin"
#

INSERT INTO `cj_admin_group_admin` VALUES (1,1),(1,3),(1,4),(1,5),(2,4),(2,6);

#
# Source for table "cj_certificate_car_req_aid_1"
#

DROP TABLE IF EXISTS `cj_certificate_car_req_aid_1`;
CREATE TABLE `cj_certificate_car_req_aid_1` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_car_id` int(10) NOT NULL DEFAULT '0',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='车辆审核管理员请求任务单';

#
# Data for table "cj_certificate_car_req_aid_1"
#


#
# Source for table "cj_certificate_car_req_aid_2"
#

DROP TABLE IF EXISTS `cj_certificate_car_req_aid_2`;
CREATE TABLE `cj_certificate_car_req_aid_2` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_car_id` int(10) NOT NULL DEFAULT '0',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='车辆审核管理员请求任务单';

#
# Data for table "cj_certificate_car_req_aid_2"
#


#
# Source for table "cj_certificate_car_req_allocated"
#

DROP TABLE IF EXISTS `cj_certificate_car_req_allocated`;
CREATE TABLE `cj_certificate_car_req_allocated` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_car_id` int(10) NOT NULL DEFAULT '0',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='车辆审核已分配审核请求';

#
# Data for table "cj_certificate_car_req_allocated"
#


#
# Source for table "cj_certificate_car_req_processed"
#

DROP TABLE IF EXISTS `cj_certificate_car_req_processed`;
CREATE TABLE `cj_certificate_car_req_processed` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_car_id` int(10) NOT NULL DEFAULT '0',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='车辆审核已处理审核请求';

#
# Data for table "cj_certificate_car_req_processed"
#


#
# Source for table "cj_certificate_car_req_unallocated"
#

DROP TABLE IF EXISTS `cj_certificate_car_req_unallocated`;
CREATE TABLE `cj_certificate_car_req_unallocated` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_car_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificate_car_id` (`certificate_car_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COMMENT='车辆审核未分配审核请求';

#
# Data for table "cj_certificate_car_req_unallocated"
#

INSERT INTO `cj_certificate_car_req_unallocated` VALUES (1,1),(2,2),(3,3),(4,4),(5,5),(6,6);

#
# Source for table "cj_certificate_car_request"
#

DROP TABLE IF EXISTS `cj_certificate_car_request`;
CREATE TABLE `cj_certificate_car_request` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `certificate_car_id` int(4) NOT NULL DEFAULT '0' COMMENT 'cj_certificate_car.id',
  `operation` tinyint(4) NOT NULL DEFAULT '0' COMMENT '操作类型：0为未分配，1为已分配，2为已处理，3为取消认证',
  `aid` int(4) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `allocate_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '审核结果数字表示:0为未审核，1为通过认证，2为车辆照不清晰，3为证件照不清晰，4为照片不符，5为驾驶证与行驶证姓名不符',
  `certificate_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  `result` varchar(60) NOT NULL DEFAULT '' COMMENT '审核结果文字表述',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '审核时管理员备注',
  `uid` int(4) NOT NULL DEFAULT '0' COMMENT '用户初见号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

#
# Data for table "cj_certificate_car_request"
#


#
# Source for table "cj_certificate_video_req_aid_1"
#

DROP TABLE IF EXISTS `cj_certificate_video_req_aid_1`;
CREATE TABLE `cj_certificate_video_req_aid_1` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_video_id` int(10) NOT NULL DEFAULT '0',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='视频认证管理员请求任务单';

#
# Data for table "cj_certificate_video_req_aid_1"
#


#
# Source for table "cj_certificate_video_req_aid_2"
#

DROP TABLE IF EXISTS `cj_certificate_video_req_aid_2`;
CREATE TABLE `cj_certificate_video_req_aid_2` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_video_id` int(10) NOT NULL DEFAULT '0',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='视频认证管理员请求任务单';

#
# Data for table "cj_certificate_video_req_aid_2"
#


#
# Source for table "cj_certificate_video_req_allocated"
#

DROP TABLE IF EXISTS `cj_certificate_video_req_allocated`;
CREATE TABLE `cj_certificate_video_req_allocated` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_video_id` int(10) NOT NULL DEFAULT '0',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='视频认证已分配审核请求';

#
# Data for table "cj_certificate_video_req_allocated"
#


#
# Source for table "cj_certificate_video_req_processed"
#

DROP TABLE IF EXISTS `cj_certificate_video_req_processed`;
CREATE TABLE `cj_certificate_video_req_processed` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_video_id` int(10) NOT NULL DEFAULT '0',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  `result` varchar(30) DEFAULT NULL COMMENT '审核结果',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='视频认证已处理审核请求';

#
# Data for table "cj_certificate_video_req_processed"
#


#
# Source for table "cj_certificate_video_req_unallocated"
#

DROP TABLE IF EXISTS `cj_certificate_video_req_unallocated`;
CREATE TABLE `cj_certificate_video_req_unallocated` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_video_id` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `certificate_video_id` (`certificate_video_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COMMENT='视频认证未分配审核请求';

#
# Data for table "cj_certificate_video_req_unallocated"
#

INSERT INTO `cj_certificate_video_req_unallocated` VALUES (1,1),(2,2),(3,3),(4,4),(5,5),(6,6),(7,7);

#
# Source for table "cj_certificate_video_request"
#

DROP TABLE IF EXISTS `cj_certificate_video_request`;
CREATE TABLE `cj_certificate_video_request` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `certificate_video_id` int(4) NOT NULL DEFAULT '0' COMMENT 'cj_certificate_video.id',
  `operation` tinyint(3) NOT NULL DEFAULT '0' COMMENT '操作类型：0为未分配，1为已分配，2为已处理，3为取消认证',
  `aid` int(4) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `allocate_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '审核结果数字表示:0为未审核，1为通过视频认证，2为视频与认证照不相符，3为认证照不清晰，4为视频非本人，5为认证照非本人',
  `certificate_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
  `result` varchar(60) NOT NULL DEFAULT '' COMMENT '审核结果文字表述',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '审核时管理员备注',
  `uid` int(4) NOT NULL DEFAULT '0' COMMENT '用户初见号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

#
# Data for table "cj_certificate_video_request"
#


#
# Source for table "cj_feedback_data"
#

DROP TABLE IF EXISTS `cj_feedback_data`;
CREATE TABLE `cj_feedback_data` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT '0',
  `data` text COLLATE utf8mb4_bin NOT NULL COMMENT 'im的product表在任务分配时,用户反馈信息集合',
  `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开始时间',
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '结束时间',
  `product_id_arr` varchar(50) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'im的product.id集合',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈数据备份';

#
# Data for table "cj_feedback_data"
#

INSERT INTO `cj_feedback_data` VALUES (1,16,'[{\"id\":\"11\",\"sender\":\"16\",\"recver\":\"50\",\"smsid\":\"0\",\"text\":\"\\u4f60\\u597d\\u554a\\uff0c\\u540e\\u53f0\\u7ba1\\u7406\\u5458\",\"texttype\":\"1\",\"chattype\":\"2\",\"time\":\"2015-01-17 12:36:26\"},{\"id\":\"14\",\"sender\":\"16\",\"recver\":\"50\",\"smsid\":\"0\",\"text\":\"\\u6211\\u7684\\u5934\\u50cf\\u600e\\u4e48\\u88ab\\u5220\\u5149\\u4e86\",\"texttype\":\"1\",\"chattype\":\"2\",\"time\":\"2015-01-18 08:36:26\"},{\"id\":\"15\",\"sender\":\"16\",\"recver\":\"50\",\"smsid\":\"0\",\"text\":\"\\u6709\\u6ca1\\u6709\\u529f\\u5fb7\\u5fc3\\u554a\",\"texttype\":\"1\",\"chattype\":\"2\",\"time\":\"2015-01-18 11:36:23\"}]','2015-01-17 12:36:26','2015-01-18 11:36:23','[\"11\",\"14\",\"15\"]'),(2,23,'[{\"id\":\"12\",\"sender\":\"23\",\"recver\":\"50\",\"smsid\":\"0\",\"text\":\"\\u95ee\\u4f60\\u4e2a\\u95ee\\u9898\",\"texttype\":\"1\",\"chattype\":\"2\",\"time\":\"2015-01-17 12:36:29\"},{\"id\":\"13\",\"sender\":\"23\",\"recver\":\"50\",\"smsid\":\"0\",\"text\":\"\\u6211\\u7684\\u8ba4\\u8bc1\\u6709\\u95ee\\u9898\",\"texttype\":\"1\",\"chattype\":\"2\",\"time\":\"2015-01-17 15:30:01\"}]','2015-01-17 12:36:29','2015-01-17 15:30:01','[\"12\",\"13\"]');

#
# Source for table "cj_feedback_req_aid_1"
#

DROP TABLE IF EXISTS `cj_feedback_req_aid_1`;
CREATE TABLE `cj_feedback_req_aid_1` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `feedback_data_id` int(10) NOT NULL DEFAULT '0',
  `dtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  `text` text COLLATE utf8mb4_bin NOT NULL COMMENT '回复信息',
  `remark` varchar(100) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '备注信息',
  `atime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '处理时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `feedback_data_id` (`feedback_data_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈客服任务';

#
# Data for table "cj_feedback_req_aid_1"
#

INSERT INTO `cj_feedback_req_aid_1` VALUES (1,1,'2015-01-26 14:18:51','好的','测试7','2015-01-26 15:03:59'),(2,2,'2015-01-26 14:18:51','','','0000-00-00 00:00:00');

#
# Source for table "cj_feedback_req_aid_2"
#

DROP TABLE IF EXISTS `cj_feedback_req_aid_2`;
CREATE TABLE `cj_feedback_req_aid_2` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `feedback_data_id` int(10) NOT NULL DEFAULT '0',
  `dtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  `text` text COLLATE utf8mb4_bin NOT NULL COMMENT '回复信息',
  `remark` varchar(100) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '备注信息',
  `atime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '处理时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `feedback_data_id` (`feedback_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈客服任务';

#
# Data for table "cj_feedback_req_aid_2"
#


#
# Source for table "cj_feedback_req_allocated"
#

DROP TABLE IF EXISTS `cj_feedback_req_allocated`;
CREATE TABLE `cj_feedback_req_allocated` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `feedback_data_id` int(10) NOT NULL DEFAULT '0',
  `aid` smallint(6) NOT NULL DEFAULT '0',
  `dtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `feedback_data_id` (`feedback_data_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈已分配任务';

#
# Data for table "cj_feedback_req_allocated"
#

INSERT INTO `cj_feedback_req_allocated` VALUES (1,1,1,'2015-01-26 14:18:51'),(2,2,1,'2015-01-26 14:18:51');

#
# Source for table "cj_feedback_req_processed"
#

DROP TABLE IF EXISTS `cj_feedback_req_processed`;
CREATE TABLE `cj_feedback_req_processed` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` smallint(6) NOT NULL DEFAULT '0',
  `feedback_data_id` int(10) NOT NULL DEFAULT '0',
  `dtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  `text` text COLLATE utf8mb4_bin NOT NULL COMMENT '回复信息',
  `atime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '处理时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `feedback_data_id` (`feedback_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈已处理任务';

#
# Data for table "cj_feedback_req_processed"
#


#
# Source for table "cj_sensitive_words"
#

DROP TABLE IF EXISTS `cj_sensitive_words`;
CREATE TABLE `cj_sensitive_words` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) NOT NULL COMMENT '敏感词类型',
  `word` varchar(100) NOT NULL COMMENT '敏感词',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COMMENT='敏感词管理';

#
# Data for table "cj_sensitive_words"
#

INSERT INTO `cj_sensitive_words` VALUES (1,1,'成人'),(2,1,'AV'),(3,1,'自拍'),(4,1,'坐台'),(5,1,'肉欲'),(6,1,'肉体'),(7,1,'卵子'),(8,1,'黄片'),(9,1,'毛片'),(10,1,'叫春'),(11,1,'J8'),(12,1,'小姐'),(13,1,'交配'),(14,1,'兼职'),(15,1,'性事'),(16,1,'偷窥'),(17,1,'SM'),(18,1,'屄'),(19,1,'催情'),(20,1,'肛交'),(21,1,'龟头'),(22,1,'鸡'),(23,1,'妓女'),(24,1,'奸'),(25,1,'茎'),(26,1,'精液'),(27,1,'精子'),(28,1,'口交'),(29,1,'卖淫'),(30,1,'嫖娼'),(31,1,'肉棒'),(32,1,'乳交'),(33,1,'三陪'),(34,1,'色情'),(35,1,'射精'),(36,1,'性交'),(37,1,'穴'),(38,1,'颜射'),(39,1,'阴唇'),(40,1,'阴道'),(41,1,'阴茎'),(42,1,'做爱'),(43,1,'阳痿'),(44,1,'早泄'),(45,1,'插'),(46,1,'ML'),(47,1,'3P'),(48,1,'群P'),(49,1,'双飞'),(50,1,'色情'),(51,1,'推油'),(52,1,'群交'),(53,1,'水磨'),(54,1,'会所'),(55,1,'桑拿'),(56,1,'发廊'),(57,1,'站街'),(58,1,'性'),(59,2,'taobao'),(60,2,'淘宝'),(61,2,'陌陌'),(62,2,'银行'),(63,2,'钱'),(64,2,'支付宝'),(65,2,'微信'),(66,2,'广告'),(67,2,'办证'),(68,2,'出售'),(69,2,'卖'),(70,3,'白莲教'),(71,3,'东正教'),(72,3,'大法'),(73,3,'法轮'),(74,3,'自焚'),(75,3,'藏独'),(76,3,'台独'),(77,3,'西藏'),(78,3,'台湾'),(79,3,'新疆'),(80,3,'大纪元'),(81,3,'突尼斯'),(82,3,'k粉'),(83,3,'溜冰'),(84,3,'毒'),(85,3,'赌'),(86,3,'大麻'),(87,3,'迷幻药'),(88,3,'嗑药'),(89,3,'枪支'),(90,4,'爱滋'),(91,4,'淋病'),(92,4,'梅毒'),(93,4,'逼'),(94,4,'肛'),(95,4,'屁眼'),(96,4,'婊子'),(97,4,'操'),(98,4,'草'),(99,4,'荡'),(100,4,'骚'),(101,4,'干'),(102,4,'尻'),(103,1,'淫'),(104,1,'B'),(105,1,'b'),(106,5,'不用了'),(107,5,'卸载'),(108,5,'删'),(109,5,'评分');

#
# Source for table "cj_user_batch_add"
#

DROP TABLE IF EXISTS `cj_user_batch_add`;
CREATE TABLE `cj_user_batch_add` (
  `uid` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='批量添加机器人用户';

#
# Data for table "cj_user_batch_add"
#

INSERT INTO `cj_user_batch_add` VALUES (62),(63),(64),(65),(66),(67),(68),(69),(70),(71),(72),(73),(74),(75);
