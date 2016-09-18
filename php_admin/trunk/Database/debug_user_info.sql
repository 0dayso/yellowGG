CREATE TABLE IF NOT EXISTS `cj_user_info_modify`(
  `id`  int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='修改用户信息';

CREATE TABLE IF NOT EXISTS `cj_user_info_modify_log`(
  `id`  int(10) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL DEFAULT 0,
  `info_type` tinyint(2) NOT NULL DEFAULT 0 COMMENT '用户信息类型',
  `before_modify` varchar(200) NOT NULL COMMENT '管理员修改前',
  `after_modify` varchar(200) NOT NULL COMMENT '修改后',
  `dtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '修改时间',
  PRIMARY KEY (`id`)
)ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 COMMENT='修改用户信息log';
/**************************************************************************************/

TRUNCATE `cj_user_info_modify`;
INSERT INTO `cj_user_info_modify` (`uid`) values('26');
INSERT INTO `cj_user_info_modify` (`uid`) values('26');
INSERT INTO `cj_user_info_modify` (`uid`) values('29');
INSERT INTO `cj_user_info_modify` (`uid`) values('26');
INSERT INTO `cj_user_info_modify` (`uid`) values('29');
INSERT INTO `cj_user_info_modify` (`uid`) values('26');
INSERT INTO `cj_user_info_modify` (`uid`) values('26');
INSERT INTO `cj_user_info_modify` (`uid`) values('29');


UPDATE `cj_user` SET 
`nickname`='亚历山大刚刚好',
`job`='阿迪达斯',
`tags`='奶爸 俏皮 可爱的的的耳朵额的耳朵额的',
`signature`='大家都想知道你现在的心情',
`movie`='毁天灭地-启示录',
`weekend`='宅到吓死你',
`cooking`='各种地道湘菜',
`travel`='到处都----没去过',
`restaurant`='各种湘菜馆',
`sport`='晚上俯卧白天撑'
WHERE `uid`=26;

UPDATE `cj_user` SET 
`nickname`='CLON',
`job`='打杂专业户',
`tags`='奶爸 俏皮 可爱的的的耳朵额的耳朵额的',
`signature`='大家都想知道你现在的心情',
`movie`='人类之子',
`weekend`='dota时间到了',
`cooking`='各种湘菜',
`travel`='呼伦贝尔大草原',
`restaurant`='还是各种湘菜馆',
`sport`='电子竞技'
WHERE `uid`=29;

/**************************************************************************************/


TRUNCATE `cj_user_info_modify_log`;

