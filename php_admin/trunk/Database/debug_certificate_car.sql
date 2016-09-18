/*
*车辆认证审核操作
*清空车辆认证cj_certificate_car及cj_certificate_car_log中操作存留数据
*同时清空所有请求认证的数据,然后插入新的数据
*同时清空确认提交的数据
*/ 
UPDATE `cj_certificate_car` SET
`status`=0,
`car_name`='',
`car_license`='',
`car_brand_id`=0,
`car_brand_name`='',
`car_model_id`=0,
`pass_time`='0000-00-00 00:00:00' 
WHERE uid in(16,23,25,26,28,30);
UPDATE `cj_certificate_car_req_unallocated` SET 
`num`=1
WHERE uid in(16,23,25,26,28,30);
TRUNCATE `cj_certificate_car_log`;
TRUNCATE `cj_certificate_car_req_aid_1`;
TRUNCATE `cj_certificate_car_req_aid_2`;
TRUNCATE `cj_certificate_car_req_allocated`;
TRUNCATE `cj_certificate_car_req_processed`;
TRUNCATE `cj_certificate_car_req_unallocated`;
/*未分配任务*/
INSERT INTO `cj_certificate_car_req_unallocated`(`uid`, `num`) VALUES (16,1);
INSERT INTO `cj_certificate_car_req_unallocated`(`uid`, `num`) VALUES (23,1);
INSERT INTO `cj_certificate_car_req_unallocated`(`uid`, `num`) VALUES (25,1);
INSERT INTO `cj_certificate_car_req_unallocated`(`uid`, `num`) VALUES (26,1);
INSERT INTO `cj_certificate_car_req_unallocated`(`uid`, `num`) VALUES (28,1);
INSERT INTO `cj_certificate_car_req_unallocated`(`uid`, `num`) VALUES (30,1);
/*分配给dylenfu的任务*/
/*INSERT INTO `cj_certificate_car_req_aid_1`(`uid`, `num`, `aid`) VALUES (16,1,1);
INSERT INTO `cj_certificate_car_req_aid_1`(`uid`, `num`, `aid`) VALUES (23,1,1);
INSERT INTO `cj_certificate_car_req_aid_1`(`uid`, `num`, `aid`) VALUES (25,1,1);
INSERT INTO `cj_certificate_car_req_aid_1`(`uid`, `num`, `aid`) VALUES (26,1,1);
INSERT INTO `cj_certificate_car_req_aid_1`(`uid`, `num`, `aid`) VALUES (28,1,1);
INSERT INTO `cj_certificate_car_req_aid_1`(`uid`, `num`, `aid`) VALUES (30,1,1);*/
/*已分配任务*/
/*INSERT INTO `cj_certificate_car_req_allocated`(`uid`, `num`, `aid`) VALUES (16,1,1);
INSERT INTO `cj_certificate_car_req_allocated`(`uid`, `num`, `aid`) VALUES (23,1,1);
INSERT INTO `cj_certificate_car_req_allocated`(`uid`, `num`, `aid`) VALUES (25,1,1);
INSERT INTO `cj_certificate_car_req_allocated`(`uid`, `num`, `aid`) VALUES (26,1,1);
*/

DROP TABLE IF EXISTS `cj_certificate_car_req_unallocated`;
DROP TABLE IF EXISTS `cj_certificate_car_req_allocated`;
DROP TABLE IF EXISTS `cj_certificate_car_req_processed`;
DROP TABLE IF EXISTS `cj_certificate_car_req_aid_1`;
DROP TABLE IF EXISTS `cj_certificate_car_req_aid_2`;
/*
* 车辆认证
-------------------------------------------------------------------------------------------
*/
CREATE TABLE IF NOT EXISTS `cj_certificate_car_req_unallocated`(
  `id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `certificate_car_id` int(10) NOT NULL DEFAULT 0 unique
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='车辆审核未分配审核请求';

CREATE TABLE IF NOT EXISTS `cj_certificate_car_req_aid_1`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_car_id` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='车辆审核管理员请求任务单';

CREATE TABLE IF NOT EXISTS `cj_certificate_car_req_aid_2`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_car_id` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='车辆审核管理员请求任务单';

CREATE TABLE IF NOT EXISTS `cj_certificate_car_req_allocated`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_car_id` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='车辆审核已分配审核请求';

CREATE TABLE IF NOT EXISTS `cj_certificate_car_req_processed`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_car_id` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '审核时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='车辆审核已处理审核请求';
/******************************************************************************************************/
/*
*车辆认证审核操作
*清空车辆认证cj_certificate_car及cj_certificate_car_log中操作存留数据
*同时清空所有请求认证的数据,然后插入新的数据
*同时清空确认提交的数据
*/ 
UPDATE `cj_certificate_car` SET
`status`=0,
`car_name`='',
`car_license`='',
`car_brand_id`=0,
`car_brand_name`='',
`car_model_id`=0,
`pass_time`='0000-00-00 00:00:00' 
WHERE uid in(16,23,25,26,28,30);


/******************************************************************************************************/


TRUNCATE `cj_certificate_car_req_aid_1`;
TRUNCATE `cj_certificate_car_req_aid_2`;
TRUNCATE `cj_certificate_car_req_unallocated`;
TRUNCATE `cj_certificate_car_req_allocated`;
TRUNCATE `cj_certificate_car_req_processed`;

/*未分配任务*/

INSERT INTO `cj_certificate_car_req_unallocated`(`certificate_car_id`) VALUES (2);
INSERT INTO `cj_certificate_car_req_unallocated`(`certificate_car_id`) VALUES (3);
INSERT INTO `cj_certificate_car_req_unallocated`(`certificate_car_id`) VALUES (4);
INSERT INTO `cj_certificate_car_req_unallocated`(`certificate_car_id`) VALUES (5);
INSERT INTO `cj_certificate_car_req_unallocated`(`certificate_car_id`) VALUES (6);


/******************************************************************************************************/


TRUNCATE `cj_certificate_car_log`;



TRUNCATE `cj_certificate_car_req_aid_1`;
TRUNCATE `cj_certificate_car_req_aid_2`;
TRUNCATE `cj_certificate_car_req_unallocated`;
TRUNCATE `cj_certificate_car_req_allocated`;
TRUNCATE `cj_certificate_car_req_processed`;
INSERT INTO `cj_certificate_car_req_unallocated`(`certificate_car_id`) VALUES (1),(2),(3),(4),(5),(6);


TRUNCATE cj_certificate_car_req_aid_1;
                         TRUNCATE cj_certificate_car_req_aid_2;
                         TRUNCATE cj_certificate_car_req_unallocated;
                         TRUNCATE cj_certificate_car_req_allocated;
                         TRUNCATE cj_certificate_car_req_processed;