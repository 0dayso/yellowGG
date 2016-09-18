/**************************************************************************************/
DROP TABLE IF EXISTS `cj_certificate_video_req_unallocated`;
DROP TABLE IF EXISTS `cj_certificate_video_req_allocated`;
DROP TABLE IF EXISTS `cj_certificate_video_req_processed`;
DROP TABLE IF EXISTS `cj_certificate_video_req_aid_1`;
DROP TABLE IF EXISTS `cj_certificate_video_req_aid_2`;

CREATE TABLE IF NOT EXISTS `cj_certificate_video_req_unallocated`(
  `id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `certificate_video_id` int(10) NOT NULL DEFAULT 0 UNIQUE
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='视频认证未分配审核请求';

CREATE TABLE IF NOT EXISTS `cj_certificate_video_req_aid_1`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_video_id` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='视频认证管理员请求任务单';

CREATE TABLE IF NOT EXISTS `cj_certificate_video_req_aid_2`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_video_id` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='视频认证管理员请求任务单';

CREATE TABLE IF NOT EXISTS `cj_certificate_video_req_allocated`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_video_id` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='视频认证已分配审核请求';

CREATE TABLE IF NOT EXISTS `cj_certificate_video_req_processed`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_video_id` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '审核时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='视频认证已处理审核请求';



/**************************************************************************************/
/*
*视频认证审核操作
*清空视频认证cj_certificate_video及cj_certificate_video_log中操作存留数据
*同时清空所有请求认证的数据,然后插入新的数据
*同时清空确认提交的数据
*/ 
UPDATE `cj_certificate_video` SET
`status`=0,
`pass_time`='0000-00-00 00:00:00' 
WHERE id in(1,2,3,4,5,6,7);

/**************************************************************************************/


/*清空相关数据表*/
TRUNCATE `cj_certificate_video_req_aid_1`;
TRUNCATE `cj_certificate_video_req_aid_2`;
TRUNCATE `cj_certificate_video_req_allocated`;
TRUNCATE `cj_certificate_video_req_processed`;
TRUNCATE `cj_certificate_video_req_unallocated`;
/*未分配任务*/
INSERT INTO `cj_certificate_video_req_unallocated`(`certificate_video_id`) VALUES (1);
INSERT INTO `cj_certificate_video_req_unallocated`(`certificate_video_id`) VALUES (2);
INSERT INTO `cj_certificate_video_req_unallocated`(`certificate_video_id`) VALUES (3);
INSERT INTO `cj_certificate_video_req_unallocated`(`certificate_video_id`) VALUES (4);
INSERT INTO `cj_certificate_video_req_unallocated`(`certificate_video_id`) VALUES (5);
INSERT INTO `cj_certificate_video_req_unallocated`(`certificate_video_id`) VALUES (6);
INSERT INTO `cj_certificate_video_req_unallocated`(`certificate_video_id`) VALUES (7);


/**************************************************************************************/


TRUNCATE `cj_certificate_video_log`;