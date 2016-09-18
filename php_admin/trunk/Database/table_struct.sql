--
-- 表的结构 `cj_action`
--

CREATE TABLE IF NOT EXISTS `cj_action` (
  `action_id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`action_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=3 ;

--
-- 转存表中的数据 `cj_action`
--

INSERT INTO `cj_action` (`action_id`, `name`) VALUES
(1, '视频审核'),
(2, '车辆审核');



CREATE TABLE IF NOT EXISTS `cj_action_admin_group` (
  `action_id` tinyint(1) DEFAULT NULL,
  `admin_group_id` smallint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `cj_action_admin_group`
--

INSERT INTO `cj_action_admin_group` (`action_id`, `admin_group_id`) VALUES
(1, 1),
(2, 1),
(1, 2),
(1, 3),
(2, 4),
(2, 5);

CREATE TABLE IF NOT EXISTS `cj_admin_group` (
  `admin_group_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`admin_group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=6 ;

--
-- 转存表中的数据 `cj_admin_group`
--

INSERT INTO `cj_admin_group` (`admin_group_id`, `name`) VALUES
(1, '超级管理员'),
(2, '运营-视频审核'),
(3, '测试-视频审核'),
(4, '运营-车辆审核'),
(5, '测试-车辆审核');

CREATE TABLE IF NOT EXISTS `cj_admin_group_admin` (
  `aid` smallint(6) DEFAULT NULL,
  `admin_group_id` smallint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `cj_admin_group_admin`
--

INSERT INTO `cj_admin_group_admin` (`aid`, `admin_group_id`) VALUES
(1, 1);
INSERT INTO `cj_admin_group_admin` (`aid`, `admin_group_id`) VALUES
(2, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE IF NOT EXISTS `cj_certificate_car_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` smallint(6) DEFAULT NULL,
  `uid` int(10) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `remark` varchar(190) DEFAULT NULL,
  `dtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=21 ;

--
-- 转存表中的数据 `cj_certificate_car_log`
--

DROP TABLE IF EXISTS `cj_certificate_video_log`;
CREATE TABLE IF NOT EXISTS `cj_certificate_video_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` smallint(6) NOT NULL  DEFAULT 0,
  `uid` int(10) NOT NULL  DEFAULT 0,
  `status` tinyint(1) NOT NULL  DEFAULT 0,
  `remark` varchar(190) NOT NULL  DEFAULT 0,
  `log` varchar(50) NOT NULL DEFAULT 0,
  `dtime` timestamp NOT NULL  DEFAULT '0000-00-00:00-00-00',  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `cj_certificate_video_log`;
CREATE TABLE IF NOT EXISTS `cj_certificate_video_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` smallint(6) NOT NULL  DEFAULT 0,
  `uid` int(10) NOT NULL  DEFAULT 0,
  `status` tinyint(1) NOT NULL  DEFAULT 0,
  `remark` varchar(190) NOT NULL  DEFAULT 0,
  `log` varchar(50) NOT NULL DEFAULT 0,
  `dtime` timestamp NOT NULL  DEFAULT '0000-00-00:00-00-00',  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

SELECT `p1`,`p2`,`p3`,`p4`,
        cj_certificate_video.dtime as sub_time,
        cj_certificate_video.uid as uid,
        cj_certificate_video_log.remark as remark,
        max(cj_certificate_video_log.dtime) as pass_time 
FROM `cj_certificate_video`
LEFT JOIN cj_certificate_video_log 
ON cj_certificate_video_log.uid=cj_certificate_video.uid
WHERE cj_certificate_video.status = 1
AND cj_certificate_video.uid > '0'
GROUP BY cj_certificate_video.uid
ORDER BY cj_certificate_video_log.dtime desc


SELECT `p1`,`p2`,`p3`,`p4`,
        cj_certificate_video.dtime as sub_time,
        cj_certificate_video.uid as uid,
        cj_certificate_video_log.remark as remark,
        cj_certificate_video_log.dtime as pass_time 
FROM `cj_certificate_video`
LEFT JOIN cj_certificate_video_log 
ON cj_certificate_video_log.uid=cj_certificate_video.uid
WHERE cj_certificate_video.status = 1
AND cj_certificate_video.uid > '0'
GROUP BY cj_certificate_video.uid
ORDER BY cj_certificate_video_log.dtime asc

alter table `cj_certificate_video` modify `p1` varchar(50) not null default '';
alter table `cj_certificate_video` modify `p2` varchar(50) not null default '';
alter table `cj_certificate_video` modify `p3` varchar(50) not null default '';
alter table `cj_certificate_video` modify `p4` varchar(50) not null default '';

alter table `cj_certificate_car` modify `p1` varchar(50) not null default '';
alter table `cj_certificate_car` modify `p2` varchar(50) not null default '';
alter table `cj_certificate_car` modify `p3` varchar(50) not null default '';
alter table `cj_certificate_car` modify `style` varchar(50) not null default '' comment '车型';
alter table `cj_certificate_car` modify `icon` varchar(50) not null default '' comment '车标';

alter table `cj_certificate_car_log` modify `remark` varchar(190) not null default '' comment '备注';
alter table `cj_certificate_car_log` modify `log` varchar(100) not null default '' comment '操作记录';

alter table `cj_certificate_video_log` modify `remark` varchar(190) not null default '' comment '备注';
alter table `cj_certificate_video_log` modify `log` varchar(100) not null default '' comment '操作记录';

DROP TABLE IF EXISTS `cj_car_license_series`;
CREATE TABLE IF NOT EXISTS `cj_car_license_series` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `style` varchar(30) NOT NULL  DEFAULT '' COMMENT '行驶证车型序列',
  `lib_id` int(10) NOT NULL DEFAULT 0 COMMENT '车型id',  
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_action` (
  `action_id` tinyint(1) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`action_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=3 ;

CREATE TABLE IF NOT EXISTS `cj_headimg_log_1`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_headimg_log_2`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_headimg_log_3`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_headimg_log_4`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_headimg_log_5`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_headimg_log_6`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_headimg_log_7`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_headimg_log_8`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_headimg_log_9`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_headimg_log_10`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_headimg_log_11`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_headimg_log_12`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

/*
* 车辆认证
-------------------------------------------------------------------------------------------
*/
CREATE TABLE IF NOT EXISTS `cj_certificate_car_req_unallocated`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `num` tinyint(3) NOT NULL DEFAULT 1 COMMENT '第几辆车',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='车辆审核未分配审核请求';

CREATE TABLE IF NOT EXISTS `cj_certificate_car_req_aid_1`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `num` tinyint(3) NOT NULL DEFAULT 1 COMMENT '第几辆车',
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='车辆审核管理员请求任务单';

CREATE TABLE IF NOT EXISTS `cj_certificate_car_req_aid_2`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `num` tinyint(3) NOT NULL DEFAULT 1 COMMENT '第几辆车',
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='车辆审核管理员请求任务单';

CREATE TABLE IF NOT EXISTS `cj_certificate_car_req_allocated`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `num` tinyint(3) NOT NULL DEFAULT 1 COMMENT '第几辆车',
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='车辆审核已分配审核请求';

CREATE TABLE IF NOT EXISTS `cj_certificate_car_req_processed`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `num` tinyint(3) NOT NULL DEFAULT 1 COMMENT '第几辆车',
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '审核时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='车辆审核已处理审核请求';

/*
* 视频认证
-------------------------------------------------------------------------------------------
*/
CREATE TABLE IF NOT EXISTS `cj_certificate_video_req_unallocated`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='视频认证未分配审核请求';

CREATE TABLE IF NOT EXISTS `cj_certificate_video_req_aid_1`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='视频认证管理员请求任务单';

CREATE TABLE IF NOT EXISTS `cj_certificate_video_req_aid_2`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='视频认证管理员请求任务单';

CREATE TABLE IF NOT EXISTS `cj_certificate_video_req_allocated`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '分配时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='视频认证已分配审核请求';

CREATE TABLE IF NOT EXISTS `cj_certificate_video_req_processed`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `aid` int(10) NOT NULL DEFAULT 0 COMMENT '管理员id',
  `dtime` datetime NOT NULL  DEFAULT '0000-00-00:00-00-00' COMMENT '审核时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='视频认证已处理审核请求';

/*******************************************不允许用uid和num做唯一标识**********************************************************/


/*
* 车辆认证
-------------------------------------------------------------------------------------------
*/
CREATE TABLE IF NOT EXISTS `cj_certificate_car_req_unallocated`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_car_id` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
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

/*
* 视频认证
-------------------------------------------------------------------------------------------
*/
CREATE TABLE IF NOT EXISTS `cj_certificate_video_req_unallocated`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `certificate_video_id` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
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

