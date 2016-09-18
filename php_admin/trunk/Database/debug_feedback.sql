/*
 * 创建im服务器上意见反馈用户发送信息的表结构
**/
CREATE TABLE IF NOT EXISTS `product` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sender` bigint(20) NOT NULL DEFAULT '0' COMMENT 'sender',
  `recver` bigint(20) NOT NULL DEFAULT '0' COMMENT 'recv',
  `smsid` bigint(20) NOT NULL DEFAULT '0' COMMENT 'smsid',
  `text` varchar(5000) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'text',
  `texttype` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'text type TEXT = 1, IMAGE = 2',
  `chattype` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'chat type NORMAL = 1, SYSTEM = 2, READSTATE = 3',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='log' AUTO_INCREMENT=1 ;

/*
 * 重置cj_im_server的用户反馈信息
**/
TRUNCATE `product`;

INSERT INTO `product`(`sender`, `recver`, `smsid`, `text`, `texttype`, `chattype`, `time`) VALUES (16,50,0,'你好啊，后台管理员',1,2,'2015-01-17 12:36:26');
INSERT INTO `product`(`sender`, `recver`, `smsid`, `text`, `texttype`, `chattype`, `time`) VALUES (23,50,0,'问你个问题',1,2,'2015-01-17 12:36:29');
INSERT INTO `product`(`sender`, `recver`, `smsid`, `text`, `texttype`, `chattype`, `time`) VALUES (23,50,0,'我的认证有问题',1,2,'2015-01-17 15:30:01');
INSERT INTO `product`(`sender`, `recver`, `smsid`, `text`, `texttype`, `chattype`, `time`) VALUES (16,50,0,'我的头像怎么被删光了',1,2,'2015-01-18 08:36:26');
INSERT INTO `product`(`sender`, `recver`, `smsid`, `text`, `texttype`, `chattype`, `time`) VALUES (16,50,0,'有没有功德心啊',1,2,'2015-01-18 11:36:23');

--------------------------------------------------------------------------------

/*
 * 创建用户反馈后台相关表
 */
CREATE TABLE IF NOT EXISTS `cj_feedback_data` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `data` text NOT NULL DEFAULT '' COMMENT 'im的product表在任务分配时,用户反馈信息集合',
  `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开始时间',
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '结束时间',
  `product_id_arr` varchar(50) NOT NULL DEFAULT '' COMMENT 'im的product.id集合',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈数据备份' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `cj_feedback_req_allocated` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `feedback_data_id` int(10) NOT NULL DEFAULT 0,
  `aid` smallint(6) NOT NULL DEFAULT 0,
  `dtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `feedback_data_id` (`feedback_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈已分配任务' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `cj_feedback_req_aid_1` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `feedback_data_id` int(10) NOT NULL DEFAULT 0,
  `dtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  `text` text NOT NULL DEFAULT '' COMMENT '回复信息',
  `atime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '处理时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `feedback_data_id` (`feedback_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈客服任务' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `cj_feedback_req_aid_2` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `feedback_data_id` int(10) NOT NULL DEFAULT 0,
  `dtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  `text` text NOT NULL DEFAULT '' COMMENT '回复信息',
  `atime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '处理时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `feedback_data_id` (`feedback_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈客服任务' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `cj_feedback_req_processed` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` smallint(6) NOT NULL DEFAULT 0,
  `feedback_data_id` int(10) NOT NULL DEFAULT 0,
  `dtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  `text` text NOT NULL DEFAULT '' COMMENT '回复信息',
  `atime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '处理时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `feedback_data_id` (`feedback_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈已处理任务' AUTO_INCREMENT=1 ;

--------------------------------------------------------------------------------

/*
 * 创建用户反馈log
 */
CREATE TABLE IF NOT EXISTS `cj_feedback_allocate_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `feedback_data_id` int(10) NOT NULL DEFAULT 0,
  `aid` smallint(6) NOT NULL DEFAULT 0 COMMENT '分配给',
  `allocate_admin_id` smallint(6) NOT NULL DEFAULT 0 COMMENT '分配人',
  `dtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈任务分配记录' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `cj_feedback_process_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` smallint(6) NOT NULL DEFAULT 0,
  `feedback_data_id` int(10) NOT NULL DEFAULT 0,
  `dtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '分配时间',
  `text` text NOT NULL DEFAULT '' COMMENT '回复信息',
  `atime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '处理时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户反馈处理记录' AUTO_INCREMENT=1 ;

