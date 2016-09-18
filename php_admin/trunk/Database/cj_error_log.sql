CREATE TABLE IF NOT EXISTS `cj_error_log`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `path` varchar(80) NOT NULL DEFAULT '' COMMENT '用户头像路径',
  `dtime` int(10) NOT NULL DEFAULT 0 COMMENT '上传时间',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0用户上传，1用户删除，2管理员删除',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `cj_error_log`(
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `action` tinyint(4) NOT NULL DEFAULT 0 COMMENT '管理员操作id',
  `dtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '操作时间',
  `controller` varchar(30) NOT NULL DEFAULT '' COMMENT '控制器',
  `function` varchar(30) NOT NULL DEFAULT '' COMMENT '方法',
  `db_table` varchar(30) NOT NULL DEFAULT '' COMMENT '数据表',
  `cloud` varchar(30) NOT NULL DEFAULT '' COMMENT '云服务器',
  `log` varchar(100) NOT NULL DEFAULT '' COMMENT '日志', 
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;