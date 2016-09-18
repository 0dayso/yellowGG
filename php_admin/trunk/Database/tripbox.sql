-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 2014-12-24 03:13:30
-- 服务器版本： 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tripbox`
--

-- --------------------------------------------------------

--
-- 表的结构 `pa_access`
--

CREATE TABLE IF NOT EXISTS `pa_access` (
  `role_id` smallint(6) unsigned NOT NULL,
  `node_id` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) NOT NULL,
  `pid` smallint(6) DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  KEY `groupId` (`role_id`),
  KEY `nodeId` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='权限分配表';

--
-- 转存表中的数据 `pa_access`
--

INSERT INTO `pa_access` (`role_id`, `node_id`, `level`, `pid`, `module`) VALUES
(3, 14, 2, 1, ''),
(3, 13, 3, 4, ''),
(3, 12, 3, 4, ''),
(3, 11, 3, 4, ''),
(3, 10, 3, 4, ''),
(3, 4, 2, 1, ''),
(3, 9, 3, 3, ''),
(3, 8, 3, 3, ''),
(3, 7, 3, 3, ''),
(3, 3, 2, 1, ''),
(3, 6, 3, 2, ''),
(3, 5, 3, 2, ''),
(3, 2, 2, 1, ''),
(3, 1, 1, 0, ''),
(4, 7, 3, 3, ''),
(4, 3, 2, 1, ''),
(4, 6, 3, 2, ''),
(4, 5, 3, 2, ''),
(4, 2, 2, 1, ''),
(4, 1, 1, 0, ''),
(3, 14, 2, 1, ''),
(3, 13, 3, 4, ''),
(3, 12, 3, 4, ''),
(3, 11, 3, 4, ''),
(3, 10, 3, 4, ''),
(3, 4, 2, 1, ''),
(3, 9, 3, 3, ''),
(3, 8, 3, 3, ''),
(3, 7, 3, 3, ''),
(3, 3, 2, 1, ''),
(3, 6, 3, 2, ''),
(3, 5, 3, 2, ''),
(3, 2, 2, 1, ''),
(3, 1, 1, 0, ''),
(4, 7, 3, 3, ''),
(4, 3, 2, 1, ''),
(4, 6, 3, 2, ''),
(4, 5, 3, 2, ''),
(4, 2, 2, 1, ''),
(4, 1, 1, 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `pa_admin`
--

CREATE TABLE IF NOT EXISTS `pa_admin` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL COMMENT '登录账号',
  `pwd` char(32) DEFAULT NULL COMMENT '登录密码',
  `status` int(11) DEFAULT '1' COMMENT '账号状态',
  `remark` varchar(255) DEFAULT '' COMMENT '备注信息',
  `find_code` char(5) DEFAULT NULL COMMENT '找回账号验证码',
  `time` int(10) DEFAULT NULL COMMENT '开通时间',
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='网站后台管理员表' AUTO_INCREMENT=4 ;

--
-- 转存表中的数据 `pa_admin`
--

INSERT INTO `pa_admin` (`aid`, `nickname`, `email`, `pwd`, `status`, `remark`, `find_code`, `time`) VALUES
(1, '超级管理员', 'chvch@163.com', '862afceedf6a3a64ba10480a7fdf39c9', 1, '我是超级管理员 哈哈~~', '', 1393221130);

-- --------------------------------------------------------

--
-- 表的结构 `pa_category`
--

CREATE TABLE IF NOT EXISTS `pa_category` (
  `cid` int(5) NOT NULL AUTO_INCREMENT,
  `pid` int(5) DEFAULT NULL COMMENT 'parentCategory上级分类',
  `name` varchar(20) DEFAULT NULL COMMENT '分类名称',
  PRIMARY KEY (`cid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='新闻分类表' AUTO_INCREMENT=55 ;

--
-- 转存表中的数据 `pa_category`
--

INSERT INTO `pa_category` (`cid`, `pid`, `name`) VALUES
(24, 22, '理财资讯'),
(14, 13, '私募动态'),
(23, 22, '行业动态'),
(8, 6, '募资资讯'),
(2, 1, '行业新闻2'),
(9, 6, '上市资讯'),
(54, 0, '景点门票'),
(21, 18, '债券公告'),
(15, 13, '私募人物'),
(16, 13, '私募视点'),
(26, 22, '监管动态'),
(53, 0, '游轮'),
(17, 13, '私募研究'),
(10, 6, '大佬语录'),
(12, 6, '投资人生'),
(52, 0, '一日游'),
(20, 18, '债市研究'),
(51, 0, '跟团游'),
(25, 22, '观点评论'),
(11, 6, '投资人物'),
(28, 27, '行业动态'),
(30, 27, '行业研究'),
(50, 0, '自由行'),
(7, 6, '行业动态'),
(29, 27, '研究动态'),
(19, 18, '债券要闻'),
(31, 6, '收购并购');

-- --------------------------------------------------------

--
-- 表的结构 `pa_elm_area`
--

CREATE TABLE IF NOT EXISTS `pa_elm_area` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `cid` smallint(3) DEFAULT NULL COMMENT '所在分类',
  `title` varchar(200) DEFAULT NULL COMMENT '新闻标题',
  `alpha` varchar(20) DEFAULT NULL,
  `aid` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='新闻表' AUTO_INCREMENT=6 ;

--
-- 转存表中的数据 `pa_elm_area`
--

INSERT INTO `pa_elm_area` (`id`, `cid`, `title`, `alpha`, `aid`) VALUES
(1, 0, '上海', 'SH', 1),
(2, 0, '北京', 'BJ', 1),
(3, 0, '马尔代夫', 'MD', 1),
(4, 0, '尼泊尔', 'NBR', 1),
(5, 0, '迪拜', 'DB', 1);

-- --------------------------------------------------------

--
-- 表的结构 `pa_elm_supplier`
--

CREATE TABLE IF NOT EXISTS `pa_elm_supplier` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `tel` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- 转存表中的数据 `pa_elm_supplier`
--

INSERT INTO `pa_elm_supplier` (`id`, `title`, `tel`) VALUES
(1, '途牛', '13518282828'),
(3, '中航信', '95105105');

-- --------------------------------------------------------

--
-- 表的结构 `pa_elm_visa`
--

CREATE TABLE IF NOT EXISTS `pa_elm_visa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area_id` int(11) NOT NULL DEFAULT '0',
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=116 ;

--
-- 转存表中的数据 `pa_elm_visa`
--

INSERT INTO `pa_elm_visa` (`id`, `area_id`, `content`) VALUES
(112, 3, 'TESTaaa'),
(113, 4, 'QQQQ'),
(115, 2, '2222223333');

-- --------------------------------------------------------

--
-- 表的结构 `pa_news`
--

CREATE TABLE IF NOT EXISTS `pa_news` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `cid` smallint(3) DEFAULT NULL COMMENT '所在分类',
  `title` varchar(200) DEFAULT NULL COMMENT '新闻标题',
  `keywords` varchar(50) DEFAULT NULL COMMENT '文章关键字',
  `description` mediumtext COMMENT '文章描述',
  `status` tinyint(1) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL COMMENT '文章摘要',
  `published` int(10) DEFAULT NULL,
  `update_time` int(10) DEFAULT NULL,
  `content` text,
  `aid` smallint(3) DEFAULT NULL COMMENT '发布者UID',
  `travel_desc` text NOT NULL COMMENT '行程介绍',
  `fee_desc` text NOT NULL COMMENT '费用说明',
  `booking_notes` text NOT NULL COMMENT '预订须知',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='新闻表' AUTO_INCREMENT=16 ;

--
-- 转存表中的数据 `pa_news`
--

INSERT INTO `pa_news` (`id`, `cid`, `title`, `keywords`, `description`, `status`, `summary`, `published`, `update_time`, `content`, `aid`, `travel_desc`, `fee_desc`, `booking_notes`) VALUES
(3, 14, '银监会拟引入银行理财业务和机构准入制度', '银行理财', '银行理财业务的迅猛增长，倒逼监管的步步升级。', 1, '银行理财业务的迅猛增长，倒逼监管的步步升级。记者从业内获得的最新统计数据显示，截至2012年末，各银行共存续理财产品32152款，理财资金账面余额7.1万亿元，比2011年末增长约55%。年初以来，银监会已将理财业务列为今年的重点监管工作。消息人士透露，对理财产品加大监管主要表现在两方面：一是将派出机构对银行理财产品销售活动进行专项检查；另一方面，将“资金池”操作模式作为现场检查的重点，“要求商业…', 1363141499, 1363148135, '银行理财业务的迅猛增长，倒逼监管的步步升级。<p>记者从业内获得的最新统计数据显示，截至2012年末，各银行共存续理财产品32152款，理财资金账面余额7.1万亿元，比2011年末增长约55%。</p><p>年初以来，银监会已将理财业务列为今年的重点监管工作。消息人士透露，对理财产品加大监管主要表现在两方面：一是将派出机构对银行理财产品销售活动进行专项检查；另一方面，将“资金池”操作模式作为现场检查的重点，“要求商业银行在2-4月份首先对‘资金池’类理财产品进行自查整改。”</p><p>随着理财业务的过快发展，监管部门对于理财业务参与机构的风险管理能力、资产管理能力等方面表现出担忧，特别是城商行和农村合作<a href="http://licai.so/Jgzl/" target="_blank">金融机构</a>。消息人士称，因此，监管部门正在酝酿开展理财业务的机构准入和业务准入制度。</p><p><strong> 严禁银行理财输血地方融资平台</strong></p><p>银行理财业务自2005年发端，至今经历了七年发展期。但时至今日仍有部分银行对理财业务的发展缺乏明确的战略定位，并未真正树立起“代客理财”的理念。</p><p>银行每季度末为冲规模大量发行期限短、收益高的理财产品，表明部分银行仅将理财业务当作其自营业务的附属，当存款规模紧张时，就通过发行保本、高收益产品争揽存款；当贷款规模紧张时，就通过理财实现贷款规模表外化，把银行理财作为“高息揽储”和“变相放贷”的工具。</p><p>记者了解到，监管部门因此要求商业银行董事会及高管层要对理财业务进行清晰的战略定位，避免理财业务沦为其他业务的调节工具和手段。</p><p>此前，部分银行将理财业务视为“变相放贷”的工具，通过规避银信合作监管规定的方式来开展项目融资，如以银证、银保、银基合作的方式，投资于票据资产或其他非标准化债券类资产。</p><p>记者获得的数据显示，截至2012年末，项目融资类理财产品余额同比增长了53%，占全部理财产品投资余额的30%，超过2万亿元。</p><p>前述消息人士透露，为了控制去年以来迅猛增长的银证、银保、银基合作等通道类业务所蕴含的风险，监管部门要求商业银行开展此类业务全程确保合规，这包括，首先要界定好投资过程中的法律关系；其次要在尽职调查的基础上合理安排交易结构和投资条款；第三，要求产品说明书要按照“解包还原”的原则充分披露；第四，要对最终投资标的的资产进行实质性管理和控制；最后还要求目标客户必须满足合格投资者的相关要求。</p><p>对于理财产品销售过程中的不规范行为，监管部门将针对这一环节进行专项检查，并计划要求银行通过投资者教育的门户网站来公示预期收益率和实际收益率的对比情况。</p><p>理财资金投向方面也要严格把关。银监会强调商业银行应严格限制资金通过各类渠道进入地方政府融资平台、“两高一剩”企业、商业房地产开发项目等限制性行业和领域。“特别强调要防止地方政府融资平台绕道通过银行理财进行直接或间接融资。”消息人士称。</p><p>银监会公布的数据显示，截至2012年末，政府融资平台贷款为93035亿元。</p><p><strong>中小机构冒进 监管层酝酿准入制度</strong></p><p>去年以来，中小金融机构特别是城商行和农村合作金融机构大量参与理财市场更加激进。记者获悉，大型银行和股份制银行在理财业务的市场份额已从2011年的88%，下降至2012年的83%。</p><p>理财业务发展过快而参与机构良莠不齐，引发监管部门的担忧。同时，部分机构还存在风险管理能力不足、业务开展不够审慎的问题。</p><p>如根据银率网的统计数据显示，今年2月份共有22款理财产品未达到预期收益率，其中有15款均为南洋商业银行所发行的产品。</p><p>而且，部分中小银行由于缺乏自主的产品设计能力，在与券商、基金、资产管理公司合作时，缺乏对产品风险和收益的实际控制权，极易沦为合作方的资金募集通道，一旦出现风险只能被动接受。</p><p>消息人士透露，对于此类风险管控能力较低、资产管理能力和专业素质还不足的中小金融机构，银监会将对其能够从事多大规模的理财业务，进行严格把关和密切监测。制定一套开展理财业务的机构准入和业务准入制度也纳入监管部门的计划中。</p><p>值得注意的是，一些创新型理财产品，如股权类投资、股票基金类投资和另类投资等，监管部门考虑到其高风险和结构复杂性，其发行将会受到严控。“特别是中小银行金融机构发行此类理财产品时，将需要逐笔上报银监会，加强合规性审查。”</p><p>此外，监管部门还注意到，部分银行存在将理财产品持有的资产与其他理财产品持有的资产，或银行自营业务资产，通过非公允的市场价格进行交易的违规行为。更有银行将一些较高收益率的理财产品销售给特定关系人，涉嫌利益输送。</p><p>银行理财业务存在的问题引起多部委的注意。记者获悉，去年，中纪委和监察部国家预防腐败局办公室也曾就此问题与银监会进行过专门的探讨，对于银行理财产品设计和交易中可能存在的腐败问题，中纪委、监察部和银监会都将进一步密切关注。</p>', 1, '', '', ''),
(4, 4, 'ttttt', 'dasdf', 'asdf', 1, 'asdf', 1393222608, 1393222618, 'asdfasdf', 1, '', '', ''),
(5, 1, '', '', '', 0, '', 1394423238, 0, '', 1, '', '', ''),
(6, 1, '', '', '', 0, '', 1394423407, 0, '', 1, '', '', ''),
(7, 1, '', '', '', 0, '', 1394447527, 0, '', 1, '', '', ''),
(8, 1, '', '', '', 0, '', 1394448997, 0, '', 1, '', '', ''),
(9, 1, '', '', '', 0, '', 1394449011, 0, '', 1, '', '', ''),
(10, 1, '', '', '', 0, '', 1394449039, 0, '', 1, '', '', ''),
(11, 1, '', '', '', 0, '', 1394449287, 0, '', 1, '', '', ''),
(12, 1, '', '', '', 0, '', 1394449363, 0, '', 1, '', '', ''),
(13, 1, '', '', '', 0, '', 1394503190, 0, '', 1, '', '', ''),
(14, 1, '', '', '', 0, '', 1394503314, 0, '', 1, '', '', '');

-- --------------------------------------------------------

--
-- 表的结构 `pa_node`
--

CREATE TABLE IF NOT EXISTS `pa_node` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `sort` smallint(6) unsigned DEFAULT NULL,
  `pid` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `pid` (`pid`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='权限节点表' AUTO_INCREMENT=45 ;

--
-- 转存表中的数据 `pa_node`
--

INSERT INTO `pa_node` (`id`, `name`, `title`, `status`, `remark`, `sort`, `pid`, `level`) VALUES
(1, 'Admin', '后台管理', 1, '网站后台管理', 10, 0, 1),
(2, 'Product', '产品管理', 1, '', 1, 1, 2),
(3, 'Member', '会员管理', 1, '', 3, 1, 2),
(4, 'Webinfo', '系统管理', 1, '', 4, 1, 2),
(5, 'index', '产品列表', 1, '', 5, 2, 3),
(6, 'add', '添加线路', 1, '', 6, 2, 3),
(7, 'index', '会员首页', 1, '', 7, 3, 3),
(8, 'index', '管理员列表', 1, '', 8, 14, 3),
(9, 'addAdmin', '添加管理员', 1, '', 9, 14, 3),
(10, 'index', '系统设置首页', 1, '', 10, 4, 3),
(11, 'setEmailConfig', '设置系统邮件', 1, '', 12, 4, 3),
(12, 'testEmailConfig', '发送测试邮件', 1, '', 0, 4, 3),
(13, 'setSafeConfig', '系统安全设置', 1, '', 0, 4, 3),
(14, 'Access', '权限管理', 1, '权限管理，为系统后台管理员设置不同的权限', 0, 1, 2),
(15, 'nodeList', '查看节点', 1, '节点列表信息', 0, 14, 3),
(16, 'roleList', '角色列表查看', 1, '角色列表查看', 0, 14, 3),
(17, 'addRole', '添加角色', 1, '', 0, 14, 3),
(18, 'editRole', '编辑角色', 1, '', 0, 14, 3),
(19, 'opNodeStatus', '便捷开启禁用节点', 1, '', 0, 14, 3),
(20, 'opRoleStatus', '便捷开启禁用角色', 1, '', 0, 14, 3),
(21, 'editNode', '编辑节点', 1, '', 0, 14, 3),
(22, 'addNode', '添加节点', 1, '', 0, 14, 3),
(23, 'addAdmin', '添加管理员', 1, '', 0, 14, 3),
(24, 'editAdmin', '编辑管理员信息', 1, '', 0, 14, 3),
(25, 'changeRole', '权限分配', 1, '', 0, 14, 3),
(26, 'News', '产品管理', 1, '', 0, 1, 2),
(27, 'index', '产品列表', 1, '', 0, 26, 3),
(28, 'category', '分类管理', 1, '', 0, 26, 3),
(29, 'add', '新增产品', 1, '', 0, 26, 3),
(30, 'edit', '编辑产品', 1, '', 0, 26, 3),
(31, 'del', '删除产品', 0, '', 0, 26, 3),
(32, 'SysData', '数据库管理', 1, '包含数据库备份、还原、打包等', 0, 1, 2),
(33, 'index', '查看数据库表结构信息', 1, '', 0, 32, 3),
(34, 'backup', '备份数据库', 1, '', 0, 32, 3),
(35, 'restore', '查看已备份SQL文件', 1, '', 0, 32, 3),
(36, 'restoreData', '执行数据库还原操作', 1, '', 0, 32, 3),
(37, 'delSqlFiles', '删除SQL文件', 1, '', 0, 32, 3),
(38, 'sendSql', '邮件发送SQL文件', 1, '', 0, 32, 3),
(39, 'zipSql', '打包SQL文件', 1, '', 0, 32, 3),
(40, 'zipList', '查看已打包SQL文件', 1, '', 0, 32, 3),
(41, 'unzipSqlfile', '解压缩ZIP文件', 1, '', 0, 32, 3),
(42, 'delZipFiles', '删除zip压缩文件', 1, '', 0, 32, 3),
(43, 'downFile', '下载备份的SQL,ZIP文件', 1, '', 0, 32, 3),
(44, 'repair', '数据库优化修复', 1, '', 0, 32, 3);

-- --------------------------------------------------------

--
-- 表的结构 `pa_order`
--

CREATE TABLE IF NOT EXISTS `pa_order` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NOT NULL,
  `sn` varchar(16) NOT NULL,
  `product_id` int(10) NOT NULL COMMENT '产品线路ID',
  `product_part_id` int(10) NOT NULL COMMENT '旅游线路日期ID',
  `reseller_id` int(10) NOT NULL COMMENT '分销商ID',
  `order_status` tinyint(1) NOT NULL COMMENT '订单状态',
  `order_time` int(11) NOT NULL COMMENT '下单时间',
  `order_review_time` int(10) NOT NULL COMMENT '审核时间',
  `phone` varchar(20) NOT NULL COMMENT '用户电话',
  `nickname` varchar(20) NOT NULL,
  `contact_type` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sn` (`sn`),
  KEY `product_id` (`product_id`,`reseller_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=39 ;

--
-- 转存表中的数据 `pa_order`
--

INSERT INTO `pa_order` (`id`, `aid`, `sn`, `product_id`, `product_part_id`, `reseller_id`, `order_status`, `order_time`, `order_review_time`, `phone`, `nickname`, `contact_type`) VALUES
(26, 0, '1404031652913470', 49, 20140404, 0, 0, 1396516529, 0, '135858585885', 'cc', 1),
(27, 0, '1404031658116688', 49, 20140404, 0, 0, 1396516581, 0, '135858585885', 'cc', 1),
(28, 0, '1404031718452489', 49, 20140404, 0, 1, 1396517184, 0, '135858585885', 'cc', 1),
(32, 0, '1404040669921587', 49, 20140404, 0, 0, 0, 0, '1333333333', '八佰伴', 1),
(33, 0, '1404040681534601', 49, 20140404, 0, 0, 0, 0, '135858585885', '11111', 1),
(34, 0, '1404040684624960', 49, 20140404, 0, 0, 0, 0, '135858585885', '11111', 1),
(35, 0, '1404084816978281', 49, 20140404, 0, 0, 1396948169, 0, '18818181818', '王先生', 2),
(36, 0, '1404084819503639', 49, 20140404, 0, 0, 1396948195, 0, '18818181818', '王先生', 2),
(37, 0, '1404085211141531', 49, 20140404, 351, 0, 1396952111, 0, '135858585885', '李', 1),
(38, 0, '1404092400079495', 49, 20140404, 0, 3, 1397024000, 1397799804, '135885858585858', '王先生', 2);

-- --------------------------------------------------------

--
-- 表的结构 `pa_order_contact`
--

CREATE TABLE IF NOT EXISTS `pa_order_contact` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(10) NOT NULL,
  `contact_name` varchar(20) NOT NULL,
  `contact_phone` varchar(40) NOT NULL,
  `contact_email` varchar(40) NOT NULL,
  `contact_fax` varchar(40) NOT NULL,
  `contact_address` varchar(200) NOT NULL,
  `contact_cert_type` int(5) NOT NULL,
  `contact_cert_id` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- 转存表中的数据 `pa_order_contact`
--

INSERT INTO `pa_order_contact` (`id`, `order_id`, `contact_name`, `contact_phone`, `contact_email`, `contact_fax`, `contact_address`, `contact_cert_type`, `contact_cert_id`) VALUES
(7, 38, '王先生', '13353343434', 'sdaf@asdf.com', '324324234', '上海市1', 1, '123456'),
(8, 38, '王女士', '13333343434', 'asdfsadf@asdf.com', '234234234234', '北京', 0, ''),
(9, 38, 'cc', '13343434', '24324@4234.com', '234234', '1234234', 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `pa_order_pay`
--

CREATE TABLE IF NOT EXISTS `pa_order_pay` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(10) NOT NULL,
  `pay_time` int(10) NOT NULL,
  `pay_amount` decimal(8,2) NOT NULL,
  `pay_type` int(11) NOT NULL,
  `pay_account` varchar(200) NOT NULL,
  `pay_reason` varchar(200) NOT NULL,
  `pay_status` tinyint(4) NOT NULL,
  `pay_memo` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- 转存表中的数据 `pa_order_pay`
--

INSERT INTO `pa_order_pay` (`id`, `order_id`, `pay_time`, `pay_amount`, `pay_type`, `pay_account`, `pay_reason`, `pay_status`, `pay_memo`) VALUES
(12, 38, 1398061380, '111.00', 1, '133', '定金', 0, ''),
(13, 38, 1398320640, '222.00', 0, '', '', 0, ''),
(14, 38, 1398666300, '12.00', 0, '', '333', 1, ''),
(15, 38, 1398664800, '100.00', 1, '', '补款', 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `pa_order_process`
--

CREATE TABLE IF NOT EXISTS `pa_order_process` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(10) NOT NULL,
  `update_desc` varchar(200) NOT NULL,
  `update_aid` int(10) NOT NULL,
  `update_aname` varchar(200) NOT NULL,
  `update_time` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=31 ;

--
-- 转存表中的数据 `pa_order_process`
--

INSERT INTO `pa_order_process` (`id`, `order_id`, `update_desc`, `update_aid`, `update_aname`, `update_time`) VALUES
(1, 38, '水电费', 1, '', 1397460212),
(2, 38, '上门收取定金', 1, '', 1397460350),
(3, 38, '办理签证中', 1, '', 1397460362),
(4, 38, '', 1, '', 1397462877),
(5, 38, '签证办理完毕', 1, '超级管理员', 1397462911),
(6, 38, '收取余款', 1, '超级管理员', 1397463508),
(7, 38, '编辑联系人信息', 1, '超级管理员', 1398517253),
(8, 38, '添加支付信息', 1, '超级管理员', 1398517965),
(9, 38, '编辑支付信息', 1, '超级管理员', 1398649341),
(10, 38, '编辑支付信息', 1, '超级管理员', 1398649342),
(11, 38, '编辑支付信息', 1, '超级管理员', 1398649342),
(12, 38, '编辑支付信息', 1, '超级管理员', 1398649366),
(13, 38, '编辑支付信息', 1, '超级管理员', 1398649399),
(14, 38, '编辑支付信息', 1, '超级管理员', 1398650070),
(15, 38, '编辑支付信息', 1, '超级管理员', 1398651073),
(16, 38, '', 1, '超级管理员', 1398652236),
(17, 38, '编辑支付信息', 1, '超级管理员', 1398665838),
(18, 38, '编辑支付信息', 1, '超级管理员', 1398665931),
(19, 38, '编辑支付信息', 1, '超级管理员', 1398665955),
(20, 38, '编辑支付信息', 1, '超级管理员', 1398666228),
(21, 38, '编辑支付信息', 1, '超级管理员', 1398666260),
(22, 38, '编辑支付信息', 1, '超级管理员', 1398666297),
(23, 38, '添加支付信息', 1, '超级管理员', 1398666348),
(24, 38, '编辑联系人信息', 1, '超级管理员', 1398667818),
(25, 38, '编辑联系人信息', 1, '超级管理员', 1398667897),
(26, 38, '编辑联系人信息', 1, '超级管理员', 1398668094),
(27, 38, '编辑支付信息', 1, '超级管理员', 1398668345),
(28, 38, '删除联系人信息', 1, '超级管理员', 1398668386),
(29, 38, '添加支付信息', 1, '超级管理员', 1398668477),
(30, 38, '编辑支付信息', 1, '超级管理员', 1398670433);

-- --------------------------------------------------------

--
-- 表的结构 `pa_product`
--

CREATE TABLE IF NOT EXISTS `pa_product` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `cid` smallint(3) DEFAULT NULL COMMENT '所在分类',
  `title` varchar(200) DEFAULT NULL COMMENT '新闻标题',
  `photo` varchar(50) NOT NULL,
  `from_city` varchar(20) NOT NULL,
  `to_city` varchar(20) NOT NULL,
  `supplier` int(10) NOT NULL,
  `keywords` varchar(50) DEFAULT NULL COMMENT '文章关键字',
  `description` mediumtext COMMENT '文章描述',
  `status` tinyint(1) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL COMMENT '文章摘要',
  `publish_time` int(10) DEFAULT NULL,
  `update_time` int(10) DEFAULT NULL,
  `content` text,
  `aid` smallint(3) DEFAULT NULL COMMENT '发布者UID',
  `visa_desc` text NOT NULL COMMENT '签证说明',
  `travel_desc` text NOT NULL COMMENT '行程介绍',
  `fee_desc` text NOT NULL COMMENT '费用说明',
  `booking_notes` text NOT NULL COMMENT '预订须知',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='新闻表' AUTO_INCREMENT=54 ;

--
-- 转存表中的数据 `pa_product`
--

INSERT INTO `pa_product` (`id`, `cid`, `title`, `photo`, `from_city`, `to_city`, `supplier`, `keywords`, `description`, `status`, `summary`, `publish_time`, `update_time`, `content`, `aid`, `visa_desc`, `travel_desc`, `fee_desc`, `booking_notes`, `is_deleted`) VALUES
(47, 54, '北京到马来西亚10日游', '', '1', '3', 0, '阿道夫', '', 1, '', 0, 1395745408, '<p>三亚一地牛人专线，行程轻松不赶路，只玩精华景点，白天不推任何自费景点，全程无购物，让你自由徜徉在北纬18°的灿烂阳光下。\n白天无自费景点，无任何购物安排，享受一价全包的透明旅行。 \n带薪导游全程服务，每个景点全程陪同游览，绝不因为有了景点讲解就降低服务。</p>', 1, '<p><strong style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">浮潜</strong><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(1)醉酒者、患有耳、鼻疾病、癫痫症、精神病、结核病、糖尿病、肾脏病、心脏病、气喘、高（低）血压等疾病的游客不能从事潜水活动；低于10岁的儿童不能从事潜水活动。</span><span style="padding: 0px; margin: 0px; font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255); color: rgb(255, 0, 0);">以上疾病类型只是简要示例，如游客尚有其他疾病可能不适合参加旅游活动的，请主动向旅行社告知或咨询。</span><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(2)游客境外出游的，在自由活动期间，切勿参加非法或未经中国政府核实的当地旅游团 体提供的自费项目、行程，以免发生人身伤亡、财产损失、饮食中毒等意外事件。</span><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(3)注意气候状况，阴天、雨天或风较大的天气都不适合浮潜。</span><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(4)浮潜三宝（面镜、呼吸管及蛙鞋）皆不可少。</span><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(5)浮潜时需注意安全，要在指定区域浮潜，并且有教练员或者工作人员的陪同。</span><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(6)在整个活动中，务必要听从导游或者工作人员的指示。</span><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(7)当不自觉进入流区，无论顺流或逆流，请尽速离开，以免因逆流消耗体力或因顺流被带离岸边。为节省体力，以顺流斜角游离为宜。</span><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(8)掌握简易的镜面排水方法，当浮潜中面镜进水时，双手指头用力按住面镜上部镜缘，由鼻子喷气，水便会由面镜下部排出。请先于浅滩处练习。</span><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(9)掌握简易的呼吸管排水方法，当呼吸管进水时，请用力且快速吹气将水排出。另外有些设计较好的呼吸管有排水阀及逆止阀之设计，可有效降低海水进入呼吸管的量，建议最好选择设计较好的呼吸管。</span><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(10)浮潜时间建议以一小时为限，以免体力透支。尽量穿戴防水手表，以掌握时间。</span><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(11)万一发生体力不支、漂流或溺水之状况，请务必告诉自己必须冷静，唯有冷静才得以自救并求援。海水 浮力大，双腿若能以垂直踩脚踏车动作持续移动，可延长救助时间。</span><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(12)当同行伙伴发生紧急状况，请即刻就近求援，并评估自身是否具备救援能力，前往救援时，尽量携带浮具。若两人皆已在深水区域，请务必先行评估自身救援能力，切勿贸然救援。在本身无救援能力之情况下，请以向他人求救为先，并将可提供浮力之器具传予溺水者。</span><br style="padding: 0px; margin: 0px; color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"/><span style="color: rgb(102, 102, 102); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; background-color: rgb(255, 255, 255);">(13)浮潜属于高风险旅游项目，请旅游者根据自身情况谨慎选择参加。旅行社在此特别提醒，建议旅游者投保高风险意外险种，酒后禁止参加。浮潜前，仔细阅读景区提示，在景区指定区域内开展活动。</span></p>', '<h3 style="margin: 0px; font-size: 12px; zoom: 1; float: none; width: 778.4000244140625px; padding: 0px !important; background-image: none !important;"></h3><h3 style="white-space: normal; margin: 0px; font-size: 12px; zoom: 1; float: none; width: 778.4000244140625px; padding: 0px !important; background-image: none !important;"><p><strong style="padding: 0px; margin: 0px;">度假村介绍</strong></p></h3><p style="white-space: normal;">Velavaru 在马尔代夫语里意为&quot;海龟岛&quot;，这是一座充满原始风情的岛屿，它远离尘嚣，四周环绕着棕榈树，黄金海岸以及清澈而又透明的蓝绿色湖水。您可以想象，当轻柔的海风吹过身旁，色彩斑斓的鱼儿在珊瑚海里自由穿行，那是多美的画面啊！<br style="padding: 0px; margin: 0px;"/></p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a class="niuren_light" href="http://m.tuniucdn.com/filebroker/cdn/prd/26/ab/26ab4af64eae289207b2a6a65a2c8e93_w0_h600_c0_t0.jpeg" style="padding: 0px; margin: 0px; color: rgb(64, 64, 64); display: block; width: 320px; height: 240px; overflow: hidden;"><img class="" src="http://127.0.0.1/tripbox/Uploads/upload_image/20140326/14481395826866.jpeg" data-src="http://m.tuniucdn.com/filebroker/cdn/prd/26/ab/26ab4af64eae289207b2a6a65a2c8e93_w320_h240_c1_t0.jpeg" style="padding: 0px; margin: 0px; border: 0px; width: 320px; height: auto; display: inline;"/></a></p><p>出海</p></li><li><p><a class="niuren_light" href="http://m.tuniucdn.com/filebroker/cdn/prd/b4/36/b436058f4400d28e6cc467077bd9c3eb_w0_h600_c0_t0.jpeg" style="padding: 0px; margin: 0px; color: rgb(64, 64, 64); display: block; width: 320px; height: 240px; overflow: hidden;"><img class="" src="http://127.0.0.1/tripbox/Uploads/upload_image/20140326/87531395826867.jpeg" data-src="http://m.tuniucdn.com/filebroker/cdn/prd/b4/36/b436058f4400d28e6cc467077bd9c3eb_w320_h240_c1_t0.jpeg" style="padding: 0px; margin: 0px; border: 0px; width: 320px; height: auto; display: inline;"/></a></p><p>俯瞰</p></li></ul><h3 style="white-space: normal; margin: 0px; font-size: 12px; zoom: 1; float: none; width: 778.4000244140625px; padding: 0px !important; background-image: none !important;"><span style="padding: 0px 0px 0px 10px; margin: 0px; font-size: 14px; float: left;">&nbsp;</span><p><strong style="padding: 0px; margin: 0px;">房型介绍</strong></p></h3><p style="white-space: normal;"><strong style="padding: 0px; margin: 0px;">海滩别墅 Beachfront Villa</strong></p><p style="white-space: normal;">入住海滩别墅，全天候，全角度观赏印度洋全景！与爱侣在满天星光的环境下，品味一杯清爽可口的鸡尾酒，共度浪漫时刻。</p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a class="niuren_light" href="http://m.tuniucdn.com/filebroker/cdn/prd/b2/fe/b2fe9c30898aee906cb4c4fef00aa94e_w0_h600_c0_t0.jpeg" style="padding: 0px; margin: 0px; color: rgb(64, 64, 64); display: block; width: 320px; height: 240px; overflow: hidden;"><img class="" src="http://127.0.0.1/tripbox/Uploads/upload_image/20140326/30251395826869.jpeg" data-src="http://m.tuniucdn.com/filebroker/cdn/prd/b2/fe/b2fe9c30898aee906cb4c4fef00aa94e_w320_h240_c0_t0.jpeg" style="padding: 0px; margin: 0px; border: 0px; width: 320px; height: auto; display: inline;"/></a></p><p>室内</p></li><li><p><a class="niuren_light" href="http://m.tuniucdn.com/filebroker/cdn/prd/2f/4b/2f4bff7442579f7ebfad23daf90d9492_w0_h600_c0_t0.jpeg" style="padding: 0px; margin: 0px; color: rgb(64, 64, 64); display: block; width: 320px; height: 240px; overflow: hidden;"><img class="" src="http://127.0.0.1/tripbox/Uploads/upload_image/20140326/29391395826870.jpeg" data-src="http://m.tuniucdn.com/filebroker/cdn/prd/2f/4b/2f4bff7442579f7ebfad23daf90d9492_w320_h240_c0_t0.jpeg" style="padding: 0px; margin: 0px; border: 0px; width: 320px; height: auto; display: inline;"/></a></p><p>外景</p></li></ul><p style="white-space: normal;"><span style="padding: 0px; margin: 0px;">海中阁 InOcean pool Villas</span></p><p style="white-space: normal;">从11幢建在珊瑚礁旁的海中阁里，观看印度洋全景！无论是即将交换戒指的情侣，还是想回味海誓山盟的伴侣，这里都是最理想的场所。</p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a class="niuren_light" href="http://m.tuniucdn.com/filebroker/cdn/prd/e7/a7/e7a71ae66020eecdfda66824211851fa_w0_h600_c0_t0.jpeg" style="padding: 0px; margin: 0px; color: rgb(64, 64, 64); display: block; width: 320px; height: 240px; overflow: hidden;"><img class="" src="http://127.0.0.1/tripbox/Uploads/upload_image/20140326/74351395826872.jpeg" data-src="http://m.tuniucdn.com/filebroker/cdn/prd/e7/a7/e7a71ae66020eecdfda66824211851fa_w320_h240_c0_t0.jpeg" style="padding: 0px; margin: 0px; border: 0px; width: 320px; height: auto; display: inline;"/></a></p><p>室内</p></li><li><p><a class="niuren_light" href="http://m.tuniucdn.com/filebroker/cdn/prd/d1/25/d125d578b1b1c052b5dc84bf4d650c1b_w0_h600_c0_t0.jpeg" style="padding: 0px; margin: 0px; color: rgb(64, 64, 64); display: block; width: 320px; height: 240px; overflow: hidden;"><img class="" src="http://127.0.0.1/tripbox/Uploads/upload_image/20140326/4881395826873.jpeg" data-src="http://m.tuniucdn.com/filebroker/cdn/prd/d1/25/d125d578b1b1c052b5dc84bf4d650c1b_w320_h240_c0_t0.jpeg" style="padding: 0px; margin: 0px; border: 0px; width: 320px; height: auto; display: inline;"/></a></p><p>水屋</p></li></ul><h3 style="white-space: normal; margin: 0px; font-size: 12px; zoom: 1; float: none; width: 778.4000244140625px; padding: 0px !important; background-image: none !important;"><span style="padding: 0px 0px 0px 10px; margin: 0px; font-size: 14px; float: left;">&nbsp;</span><p><strong style="padding: 0px; margin: 0px;">餐饮美食</strong></p></h3><p style="white-space: normal;"><span class="_FF0000" style="padding: 0px; margin: 0px; color: rgb(255, 0, 0) !important;"><span class="_000000" style="padding: 0px; margin: 0px; color: rgb(0, 0, 0) !important;"><br style="padding: 0px; margin: 0px;"/></span></span></p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a class="niuren_light" href="http://m.tuniucdn.com/filebroker/cdn/prd/15/d5/15d569eb67f857aa213423e480576495_w0_h600_c0_t0.jpeg" style="padding: 0px; margin: 0px; color: rgb(64, 64, 64); display: block; width: 320px; height: 240px; overflow: hidden;"><img class="" src="http://127.0.0.1/tripbox/Uploads/upload_image/20140326/6421395826875.jpeg" data-src="http://m.tuniucdn.com/filebroker/cdn/prd/15/d5/15d569eb67f857aa213423e480576495_w320_h240_c0_t0.jpeg" style="padding: 0px; margin: 0px; border: 0px; width: 320px; height: auto; display: inline;"/></a></p><p>餐厅</p></li><li><p><a class="niuren_light" href="http://m.tuniucdn.com/filebroker/cdn/prd/db/1c/db1c2d9051a008b2fde78743d35a5158_w0_h600_c0_t0.jpeg" style="padding: 0px; margin: 0px; color: rgb(64, 64, 64); display: block; width: 320px; height: 240px; overflow: hidden;"><img class="" src="http://127.0.0.1/tripbox/Uploads/upload_image/20140326/54921395826878.jpeg" data-src="http://m.tuniucdn.com/filebroker/cdn/prd/db/1c/db1c2d9051a008b2fde78743d35a5158_w320_h240_c1_t0.jpeg" style="padding: 0px; margin: 0px; border: 0px; width: 320px; height: auto; display: inline;"/></a></p><p>室内</p></li></ul><h3 style="white-space: normal; margin: 0px; font-size: 12px; zoom: 1; float: none; width: 778.4000244140625px; padding: 0px !important; background-image: none !important;"><span style="padding: 0px 0px 0px 10px; margin: 0px; font-size: 14px; float: left;">&nbsp;</span><p><strong style="padding: 0px; margin: 0px;">娱乐设施</strong></p></h3><p style="white-space: normal;"><strong style="padding: 0px; margin: 0px;">水上活动</strong></p><p style="white-space: normal;">潜水：我们提供了多个海洋探险机会。新手和潜水老手可在PADI认证潜水教练的指导下，参加浮潜、进行主要路线潜水、私人潜水和巡潜，以及潜入水中观看鲸鲨。<br style="padding: 0px; margin: 0px;"/></p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a class="niuren_light" href="http://m.tuniucdn.com/filebroker/cdn/prd/00/12/0012b5cb29b31e253d1f0aec8ebf0648_w0_h600_c0_t0.jpeg" style="padding: 0px; margin: 0px; color: rgb(64, 64, 64); display: block; width: 320px; height: 240px; overflow: hidden;"><img class="" src="http://127.0.0.1/tripbox/Uploads/upload_image/20140326/92921395826880.jpeg" data-src="http://m.tuniucdn.com/filebroker/cdn/prd/00/12/0012b5cb29b31e253d1f0aec8ebf0648_w320_h240_c1_t0.jpeg" style="padding: 0px; margin: 0px; border: 0px; width: 320px; height: auto; display: inline;"/></a></p><p>出海</p></li><li><p><a class="niuren_light" href="http://m.tuniucdn.com/filebroker/cdn/prd/60/a2/60a24065bdb2b0f729f2e20d33eb4b58_w0_h600_c0_t0.jpeg" style="padding: 0px; margin: 0px; color: rgb(64, 64, 64); display: block; width: 320px; height: 240px; overflow: hidden;"><img class="" src="http://127.0.0.1/tripbox/Uploads/upload_image/20140326/5441395826881.jpeg" data-src="http://m.tuniucdn.com/filebroker/cdn/prd/60/a2/60a24065bdb2b0f729f2e20d33eb4b58_w320_h240_c1_t0.jpeg" style="padding: 0px; margin: 0px; border: 0px; width: 320px; height: auto; display: inline;"/></a></p><p>婚礼</p></li><li><p><br/></p></li></ul><p><br/></p>', '<h3 style="padding: 0px; margin: 18px 0px 8px; font-size: 12px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">费用包含</h3><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><br/></p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">1.交通：往返团队经济舱机票含税费（团队机票将统一出票，如遇政府或航空公司政策性调整燃油税费，在未出票的情况下将进行多退少补，敬请谅解。团队机票一经开出，不得更改、不得签转、不得退票），当地旅游巴士。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">2.小交通：景区内用车（亚龙湾热带天堂森林公园电瓶车）。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">3.小交通：三亚凤凰机场往返接送服务。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">4.住宿：行程所列酒店。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">5.用餐：行程中团队标准用餐，全程安排5正4早（酒店含早，正餐餐标25元/人/餐；十人一桌、十菜一汤），如每桌人数不是为10人，则餐费不变的情况下，酌情增减菜品数量，参考菜单中个别菜品可能随时令有所调整。（中式餐或自助餐或特色餐，自由活动期间用餐请自理；如因自身原因放弃用餐，则餐费不退）。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">6.门票：行程中所含的景点首道大门票。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">7.导服：当地中文导游。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">8.儿童价标准：年龄2~12周岁（不含），不占床，含往返机票（含税）、半价正餐、导服、旅游车车位，其他当地自理。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">9.赠送：三亚政府调节基金。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><br/></p><h3 style="padding: 0px; margin: 18px 0px 8px; font-size: 12px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">费用不包含</h3><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><br/></p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">1.小交通：景区内用车。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">2.小交通：出发地机场往返接送服务。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">3.单房差。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">4.因交通延阻、罢工、天气、飞机机器故障、航班取消或更改时间等不可抗力原因所引致的额外费用。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">5.酒店内洗衣、理发、电话、传真、收费电视、饮品、烟酒等个人消费。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">6.当地参加的自费以及以上“费用包含”中不包含的其它项目。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">7.旅游人身意外保险</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><span style="padding: 0px; margin: 0px; color: rgb(241, 102, 26);">* 建议购买旅游人身意外保险&nbsp;</span><a href="http://www.tuniu.com/help/ejchina.shtml" rel="nofollow" target="_blank" class="f_4e9700 fb" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0); font-weight: 700;">国内旅游意外伤害保障计划</a></p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><br/></p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><span style="padding: 0px; margin: 0px; color: rgb(241, 102, 26);">* 由于航空公司机票价格时时变动，您提交信息后可能会出现没有位置或者价格波动，请以占位及出票后的价格为准。</span></p>', '<p class="ht_sub1" style="padding: 0px 0px 0px 15px; line-height: 24px; color: rgb(102, 102, 102); font-size: 12px; font-family: 微软雅黑; text-indent: -15px; white-space: normal; background-color: rgb(252, 248, 242); margin-top: 0px !important; margin-bottom: 0px !important;">1. 网上预订与电话预订同等有效，下单更自主、便捷；</p><p class="ht_sub1" style="padding: 0px 0px 0px 15px; line-height: 24px; color: rgb(102, 102, 102); font-size: 12px; font-family: 微软雅黑; text-indent: -15px; white-space: normal; background-color: rgb(252, 248, 242); margin-top: 0px !important; margin-bottom: 0px !important;">2. 享受更多专属优惠：</p><p class="ht_sub2" style="padding: 0px 0px 0px 20px; line-height: 24px; color: rgb(102, 102, 102); font-size: 12px; font-family: 微软雅黑; text-indent: -15px; white-space: normal; background-color: rgb(252, 248, 242); margin-top: 0px !important; margin-bottom: 0px !important;">1）立减优惠最高可省150元/每成人；</p><p class="ht_sub2" style="padding: 0px 0px 0px 20px; line-height: 24px; color: rgb(102, 102, 102); font-size: 12px; font-family: 微软雅黑; text-indent: -15px; white-space: normal; background-color: rgb(252, 248, 242); margin-top: 0px !important; margin-bottom: 0px !important;">（2）旅游归来发表点评，每成人获赠200元抵用券+5元现金；</p><p class="ht_sub2" style="padding: 0px 0px 0px 20px; line-height: 24px; color: rgb(102, 102, 102); font-size: 12px; font-family: 微软雅黑; text-indent: -15px; white-space: normal; background-color: rgb(252, 248, 242); margin-top: 0px !important; margin-bottom: 0px !important;">（3）旅游归来写游记，最高可获1000元</p><p><br/></p>', 0);
INSERT INTO `pa_product` (`id`, `cid`, `title`, `photo`, `from_city`, `to_city`, `supplier`, `keywords`, `description`, `status`, `summary`, `publish_time`, `update_time`, `content`, `aid`, `visa_desc`, `travel_desc`, `fee_desc`, `booking_notes`, `is_deleted`) VALUES
(48, 51, '上海到马尔代夫10日', '', '1', '3', 0, '', '', 0, '上海到马尔代夫', 0, 1395827201, '上海到马尔代夫', 1, '<p>暂无</p>', '<p id="tour_abstruct" style="padding: 0px;margin-top: 0px;margin-bottom: 0px;color: rgb(241, 102, 26)">本产品为目的地成团（您可在网上预订过程中查看和选择合适的航班出行）。</p><p style="padding: 0px;margin-top: 0px;margin-bottom: 0px;color: rgb(241, 102, 26)">本产品与其他旅行社联合发团。</p><p style="padding: 0px;margin-top: 0px;margin-bottom: 0px;color: rgb(241, 102, 26)"><br/></p><p><a href="http://sh.tuniu.com/tours/523167/print" rel="nofollow" target="_blank" style="padding: 0px 0px 0px 20px;color: rgb(64, 64, 64);background-position: 0px -1515px">打印行程</a></p><h3 style="margin: 0px 0px 10px;font-size: 12px;zoom: 1;float: none;width: 778.4000244140625px;padding: 0px !important;background-image: none !important"><span style="padding: 0px 0px 0px 10px;margin: 0px 10px 0px 0px;font-size: 14px;float: left;background-position: 0px 4px;background-repeat: no-repeat no-repeat">第1天</span>出发地<img src="http://img.tuniucdn.com/icons/route/plain.gif" data-src="http://img.tuniucdn.com/icons/route/plain.gif" style="padding: 0px;margin: 0px 5px;border: 0px;display: inline"/>三亚</h3><p>飞往美丽的鹿城——三亚，专人举“客人名字”接机，入住酒店。酒店：三亚海湾维景国际大酒店<br style="padding: 0px"/>地址：河西区南边海路113号（近鹿回头公园）2013年开业，拥有497间房，三亚海湾维景国际大酒店由港中旅酒店有限公司管理，是一座东南亚建筑风格与中国传统文化精髓相融合的热带风情商务休闲会聚之所。酒店座落于风景旖旎的天然国际海港——三亚南边海港，毗邻有&quot;东方迪拜&quot;美誉的凤凰岛，背倚风景秀丽的旅游风景区&quot;南海情山&quot;鹿回头，依山傍海，尽享自然美景。<br style="padding: 0px"/>酒店设施：中餐厅、西餐厅、酒吧、免费停车场、可无线上网的公共区域、免费旅游交通图(可赠送)、大堂吧、电梯<br style="padding: 0px"/>房间设施：国内国际长途电话、拖鞋、书桌、24小时热水、电热水壶、咖啡壶/茶壶、免费洗漱用品(6样以上)、免费瓶装水、迷你吧、熨衣设备、小冰箱、浴衣、多种规格、电源插座、独立淋浴间、吹风机、房内保险箱、中央空调<br style="padding: 0px"/></p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a href="http://images.tuniu.com/images/2013-06-28/4/4ZOqUIO7UJx98rg.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://images.tuniu.com/images/2013-06-28/4/4ZOqUIO7UJx98rgn.jpg" data-src="http://images.tuniu.com/images/2013-06-28/4/4ZOqUIO7UJx98rgn.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>酒店外观——依山傍海</p></li><li><p><a href="http://images.tuniu.com/images/2013-06-28/4/4P8gKynb9tXznc0i.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://images.tuniu.com/images/2013-06-28/4/4P8gKynb9tXznc0in.jpg" data-src="http://images.tuniu.com/images/2013-06-28/4/4P8gKynb9tXznc0in.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>大堂</p></li><li><p><a href="http://images.tuniu.com/images/2013-06-28/2/2F88ii664433SSGG.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://images.tuniu.com/images/2013-06-28/2/2F88ii664433SSGGn.jpg" data-src="http://images.tuniu.com/images/2013-06-28/2/2F88ii664433SSGGn.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>餐厅</p></li><li><p><a href="http://images.tuniu.com/images/2013-06-28/y/ybEthLL4oobFtti6.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://images.tuniu.com/images/2013-06-28/y/ybEthLL4oobFtti6n.jpg" data-src="http://images.tuniu.com/images/2013-06-28/y/ybEthLL4oobFtti6n.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>海景双标房</p></li><li><p><a href="http://images.tuniu.com/images/2013-06-28/5/5ffJx0998rgg42m.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://images.tuniu.com/images/2013-06-28/5/5ffJx0998rgg42mn.jpg" data-src="http://images.tuniu.com/images/2013-06-28/5/5ffJx0998rgg42mn.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>高级山景套房</p></li><li><p><a href="http://images.tuniu.com/images/2013-06-28/Q/QW4R0tM430uMMApS.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://images.tuniu.com/images/2013-06-28/Q/QW4R0tM430uMMApSn.jpg" data-src="http://images.tuniu.com/images/2013-06-28/Q/QW4R0tM430uMMApSn.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>豪华海景套房阳台</p></li></ul><p><span style="padding: 0px;margin: 0px 15px 0px 0px;font-weight: 700;float: left">用餐</span>早餐:<span style="padding: 0px">敬请自理</span>&nbsp;&nbsp;&nbsp;&nbsp; 午餐:<span style="padding: 0px">敬请自理</span>&nbsp;&nbsp;&nbsp;&nbsp; 晚餐:敬请自理<span style="padding: 0px;margin: 0px 15px 0px 0px;font-weight: 700;float: left">住宿</span>三亚海湾维景国际大酒店海湾房2人间（独卫、热水、空调、彩电）</p><h3 style="margin: 0px 0px 10px;font-size: 12px;zoom: 1;float: none;width: 778.4000244140625px;padding: 0px !important;background-image: none !important"><span style="padding: 0px 0px 0px 10px;margin: 0px 10px 0px 0px;font-size: 14px;float: left;background-position: 0px 4px;background-repeat: no-repeat no-repeat">第2天</span>三亚</h3><p>酒店西餐厅早餐，80多个品种纯西式自助早餐任您搭配，异国风情的热带水果满足您的味蕾。<br style="padding: 0px"/>上午早餐后前往<span style="padding: 0px;color: rgb(0, 0, 255) !important">【亚龙湾国家旅游度假区】</span>（游约60分钟）。您可以在沙滩休闲自在地漫步，吹吹海风，享受阳光。景点介绍：亚龙湾国家旅游度假区亚龙湾国家旅游度假区位于三亚市东南28公里处,是海南最南端的一个半月形海湾，全长约7.5公里，是海南名景之一。沙滩绵延7000米且平缓宽阔，浅海区宽达50～60米。沙粒洁白细软,海水清澈洁莹,能见度7～9米。年平均气温25.5℃,海水温度22℃-25.1℃，终年可游泳。海水浴场绝佳,被誉为“天下第一湾”。</p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a href="http://images.tuniu.com/images/2013-06-28/v/v6e20ZOC55e331kP.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://images.tuniu.com/images/2013-06-28/v/v6e20ZOC55e331kPn.jpg" data-src="http://images.tuniu.com/images/2013-06-28/v/v6e20ZOC55e331kPn.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>亚龙湾国家旅游度假区</p></li><li><p><a href="http://images.tuniu.com/images/2013-06-28/7/7d1v75pHH0ZO7U2w.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://images.tuniu.com/images/2013-06-28/7/7d1v75pHH0ZO7U2wn.jpg" data-src="http://images.tuniu.com/images/2013-06-28/7/7d1v75pHH0ZO7U2wn.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>亚龙湾沙滩</p></li></ul><p>下午游览素有“中国的马尔代夫”之称的—<span style="padding: 0px;color: rgb(0, 0, 255) !important">【蜈支洲岛】</span>(游览200分钟)它是世界上为数不多的唯一没有礁石或者鹅卵石混杂的情人岛。景点介绍：蜈支洲岛落在三亚市北部的海棠湾内，北面与南湾猴岛遥遥相对，南邻是号称天下第一湾的亚龙湾。蜈支洲岛距海岸线2.7公里，方圆1.48平方公里，呈不规则的蝴蝶状，东西长1400米，南北宽1100米。距三亚市30公里，凤凰机场38公里，紧靠海口至三亚的高速公路，位置优越，交通便利。该岛是海南岛周围为数不多的有淡水资源和丰富植被的小岛，有二千多种植物，种类繁多。并生长着许多珍贵树种，如有被称为植物界中大熊猫的龙血树，并有许多难得一见的植物现象，如“共生”、“寄生”、“绞杀”等。该岛东、南两峰相连，最高峰79.9米，悬崖壁立。<br style="padding: 0px"/></p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/olb/5d/5e/5d5ea683bccf1719c74ef4c76e840c85_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/olb/5d/5e/5d5ea683bccf1719c74ef4c76e840c85_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/olb/5d/5e/5d5ea683bccf1719c74ef4c76e840c85_w320_h240_c1_t0.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>蜈支洲岛</p></li><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/online/ca/04/ca04af59_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/online/ca/04/ca04af59_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/online/ca/04/ca04af59_w320_h240_c1_t0.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>蜈支洲岛度假中心</p></li><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/online/15/17/15177ef8_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/online/15/17/15177ef8_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/online/15/17/15177ef8_w320_h240_c1_t0.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>蜈支洲岛度假中心</p></li><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/online/9b/de/9bde55fd_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/online/9b/de/9bde55fd_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/online/9b/de/9bde55fd_w320_h240_c1_t0.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>蜈支洲岛度假中心</p></li></ul><p>晚餐后自由活动。<span style="padding: 0px;margin: 0px 15px 0px 0px;font-weight: 700;float: left">用餐</span>早餐:<span style="padding: 0px">含</span>&nbsp;&nbsp;&nbsp; 午餐:<span style="padding: 0px">含</span>&nbsp;&nbsp;&nbsp;&nbsp; 晚餐:含<span style="padding: 0px;margin: 0px 15px 0px 0px;font-weight: 700;float: left">住宿</span>三亚海湾维景国际大酒店海湾房2人间（独卫、热水、空调、彩电）</p><h3 style="margin: 0px 0px 10px;font-size: 12px;zoom: 1;float: none;width: 778.4000244140625px;padding: 0px !important;background-image: none !important"><span style="padding: 0px 0px 0px 10px;margin: 0px 10px 0px 0px;font-size: 14px;float: left;background-position: 0px 4px;background-repeat: no-repeat no-repeat">第3天</span>三亚</h3><p>酒店西餐厅用早餐，为今天的<span style="padding: 0px;font-weight: bold">探密之旅</span>充电，整装待发。上午早餐后前往陵水乘亚洲最长的跨海索道参观<span style="padding: 0px;color: rgb(0, 0, 255) !important">【南湾猴岛】</span>（特别赠送跨海索道，游览150分钟),（三面环海，是我国也是世界上唯一的岛屿型猕猴自然保护区。乘坐跨海索道，穿越疍家渔排风情，桐楼渔火；岛上体验生灵给我们带来的乐趣之余，让我们共庆人与自然的和谐成果）。之后前往海南旅游新地标海南首家10万平米集旅游、购物、休闲、娱乐为一体----<span style="padding: 0px;color: rgb(0, 0, 255) !important">【首创奥特莱斯】</span>！参观有“海南的花果山”之称的岛屿型猕猴自然保护区。之后前往海南原始生态部落<span style="padding: 0px;color: rgb(0, 0, 255) !important">【崖州古越】</span>了解海南奇特的民俗文化。景点介绍：南湾猴岛南湾猴岛为半岛，三面环海，位于海南省陵水县南湾半岛上。岛上现居住着千余只总计21群猕猴，近2000只活泼可爱的猕猴（属国家二类保护动物），因此人们称之为“猴岛”南湾猴岛形状狭长，气候温和，是猕猴生息繁衍的理想乐园。猴岛共有大小12座山头，总面积 为10.2平方公里，山上草木繁茂，岩洞怪石无数，花果四季飘香。岛上的植物种类繁多。</p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/online/d0/5a/d05a56ae_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/online/d0/5a/d05a56ae_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/online/d0/5a/d05a56ae_w320_h240_c1_t0.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>南湾猴岛</p></li><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/online/f9/51/f9514992_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/online/f9/51/f9514992_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/online/f9/51/f9514992_w320_h240_c1_t0.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>南湾猴岛</p></li><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/online/c6/0e/c60e3e88_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/online/c6/0e/c60e3e88_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/online/c6/0e/c60e3e88_w320_h240_c1_t0.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>南湾猴岛</p></li><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/online/7a/a1/7aa19e2d_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/online/7a/a1/7aa19e2d_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/online/7a/a1/7aa19e2d_w320_h240_c1_t0.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>南湾猴岛</p></li></ul><p>下午探密南国带雨林<span style="padding: 0px;color: rgb(0, 0, 255) !important">【呀诺哒】<span style="padding: 0px;color: rgb(0, 0, 0) !important">（</span></span>游约150分钟），在这里热带雨林六大奇观可以让你身心震憾留连忘返；这里可以欣赏到鹦鹉表演，黎族的打柴舞，云林品茶；这里山奇、林茂、水秀、谷深，可以称得上是海南岛的&quot;香格里拉&quot;，人间仙境的&quot;世外桃源&quot;。景点介绍：呀诺达热带雨林呀诺达热带雨林风景区“呀诺达”，是形声词，在海南本土方言中表示一、二、三。景区赋予它新的内涵，“呀”表示创新，“诺”表示承诺，“达”表示践行，同时“呀诺达”又被意为欢迎、你好，表示友好和祝福。热带雨林谷遮天蔽日，流泉叠瀑倾泻而下，年平均温度24度，踱步雨林中，你能感受阵阵清新凉意。在这里，你能卸下最繁杂的纷扰，穿越雨林栈道，呼吸最清新的空气，畅享休闲的快乐时光。</p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/olb/e1/9e/e19e116b6b0c477365903a7873266fe5_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/olb/e1/9e/e19e116b6b0c477365903a7873266fe5_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/olb/e1/9e/e19e116b6b0c477365903a7873266fe5_w320_h240_c1_t0.jpg" height="240" width="320" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>呀喏哒雨林</p></li><li><p><a href="http://images.tuniu.com/images/2013-06-28/5/5b98X6AA2c00ui77.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://images.tuniu.com/images/2013-06-28/5/5b98X6AA2c00ui77n.jpg" data-src="http://images.tuniu.com/images/2013-06-28/5/5b98X6AA2c00ui77n.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>呀喏哒雨林</p></li></ul><p>晚餐后自由活动。<span style="padding: 0px;margin: 0px 15px 0px 0px;font-weight: 700;float: left">用餐</span>早餐:<span style="padding: 0px">含</span>&nbsp;&nbsp;&nbsp; 午餐:含 &nbsp; &nbsp; 晚餐:含<span style="padding: 0px;margin: 0px 15px 0px 0px;font-weight: 700;float: left">住宿</span>三亚海湾维景国际大酒店海湾房2人间（独卫、热水、空调、彩电）</p><h3 style="margin: 0px 0px 10px;font-size: 12px;zoom: 1;float: none;width: 778.4000244140625px;padding: 0px !important;background-image: none !important"><span style="padding: 0px 0px 0px 10px;margin: 0px 10px 0px 0px;font-size: 14px;float: left;background-position: 0px 4px;background-repeat: no-repeat no-repeat">第4天</span>三亚</h3><p>上午早餐后游览集热带园林、佛教文化为一体的“福泽之地”、&nbsp;国家5A景点——<span style="padding: 0px;color: rgb(0, 0, 255) !important">【南山佛教文化苑】</span>(游约120分钟），观南海奇观--海上108米观音圣像。<br style="padding: 0px"/>景点介绍：南山佛教文化苑南山别名鳌山，是琼南名山。山高500余米，山上终年祥云缭绕，气象万千。南山佛教文化苑是一座展示中国佛教传统文化，富有深刻哲理寓意，能够启迪心智、教化人生的园区。而南山寺最著名的是创造世界之最，高达108米,造价6亿人民币的海上观音，南山寺无处不在显示着博大精深的佛教文化和中国传统文化,是世所罕见，震惊世界的佛教名山胜地。</p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/olb/ba/7f/ba7f4ff4edbc3c82e511b98aba9b5b42_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/olb/ba/7f/ba7f4ff4edbc3c82e511b98aba9b5b42_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/olb/ba/7f/ba7f4ff4edbc3c82e511b98aba9b5b42_w320_h240_c1_t0.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>南山</p></li><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/online/08/76/876bd2c_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/online/08/76/876bd2c_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/online/08/76/876bd2c_w320_h240_c1_t0.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>南山</p></li></ul><p>下午午餐后前往游览久负盛名的<span style="padding: 0px;color: rgb(0, 0, 255) !important">【天涯海角】</span>(游约120分钟），海天一色，烟波浩翰，帆影点点，椰林婆娑，奇石林立，让爱与您同在。亲！您是不是有点留恋忘返呢？<br style="padding: 0px"/>景点介绍：天涯海角天涯海角游览区，位于三亚市区西南23公里处，陆地面积10.4平方公里，海域面积6平方公里，背负马岭山，面向茫茫大海，是海南建省20年第一旅游名胜。这里海水澄碧， 烟波浩瀚，帆影点点，椰林婆娑，奇石林立水天一色。海湾沙滩上大小百块石耸立，“天涯”、“海角”和“南天一柱”巨石突兀其间，昂首天外，峥嵘壮观。</p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/vnd/50/65/506539331e7b9dff39765eb6dca92c12_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/vnd/50/65/506539331e7b9dff39765eb6dca92c12_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/vnd/50/65/506539331e7b9dff39765eb6dca92c12_w320_h240_c1_t0.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>天涯海角</p></li><li><p><a href="http://m.tuniucdn.com/filebroker/cdn/vnd/60/6e/606ea91cade99bca340df4be9fd464f0_w320_h240_c1_t0.jpg" style="padding: 0px;color: rgb(64, 64, 64)"><img src="http://m.tuniucdn.com/filebroker/cdn/vnd/60/6e/606ea91cade99bca340df4be9fd464f0_w320_h240_c1_t0.jpg" data-src="http://m.tuniucdn.com/filebroker/cdn/vnd/60/6e/606ea91cade99bca340df4be9fd464f0_w320_h240_c1_t0.jpg" style="padding: 0px;border: 0px;width: 320px;height: 240px;display: inline"/></a>爱情石</p></li></ul><p>18:30晚餐后自由活动。<span style="padding: 0px;margin: 0px 15px 0px 0px;font-weight: 700;float: left">用餐</span>早餐:含 &nbsp; &nbsp; 午餐:含 &nbsp; &nbsp;&nbsp; 晚餐:含<span style="padding: 0px;margin: 0px 15px 0px 0px;font-weight: 700;float: left">住宿</span>三亚海湾维景国际大酒店海湾房2人间（独卫、热水、空调、彩电）</p><h3 style="margin: 0px 0px 10px;font-size: 12px;zoom: 1;float: none;width: 778.4000244140625px;padding: 0px !important;background-image: none !important"><span style="padding: 0px 0px 0px 10px;margin: 0px 10px 0px 0px;font-size: 14px;float: left;background-position: 0px 4px;background-repeat: no-repeat no-repeat">第5天</span>三亚<img src="http://img.tuniucdn.com/icons/route/plain.gif" data-src="http://img.tuniucdn.com/icons/route/plain.gif" style="padding: 0px;margin: 0px 5px;border: 0px;display: inline"/>出发地</h3><p>早餐后自由活动，您可体验酒店内各项免费设施，亦可步行至免税店逛逛，或到三亚解放路体验三亚滨海风情的市井生活，结束愉快旅程，根据航班时间安排送机。<br style="padding: 0px"/><span style="padding: 0px;margin: 0px 15px 0px 0px;font-weight: 700;float: left">用餐</span>早餐:含&nbsp;&nbsp;&nbsp; 午餐:<span style="padding: 0px">敬请自理</span>&nbsp;&nbsp;&nbsp;&nbsp; 晚餐:<span style="padding: 0px">敬请自理</span><span style="padding: 0px;margin: 0px 15px 0px 0px;font-weight: 700;float: left">住宿</span>温暖的家* 以上行程仅供参考，最终行程可能会根据实际情况进行微调，敬请以出团通知为准。</p><p style="padding: 0px;margin-top: 0px;margin-bottom: 0px"><span style="padding: 0px"><span style="padding: 0px;color: red">春秋航班乘坐注意事项：</span></span></p><p style="padding: 0px;margin-top: 0px;margin-bottom: 0px">1、春秋航空的票价不含餐、饮费用。客舱内设有售品部，您可以根据需要选购饮料、餐食及其他商品<br style="padding: 0px"/>2、70周岁以上老人乘坐春秋航班，需要提供区级以上医院开具的体检证明（心率、心电图，呼吸道，血压），如果春秋航空审核未通过，我们将为您办理全额退款。<br style="padding: 0px"/>3、<span style="padding: 0px;color: rgb(255, 0, 51)">团队票签约之后默认出票，不能退改签，退票只退往返100元/人机场建设费；</span>如由于春秋航空公司原因造成航班<span style="padding: 0px;color: red">延误4小时以上并直至晚上22:00以后，且计划航班班取消的，</span><span style="padding: 0px;color: black">春秋</span><span style="padding: 0px;color: red">为您免费安排带盥洗设施的标准间。</span>由于天气等不可抗力因素导致航班取消或<span style="padding: 0px;color: red">延误超过3个小时，团队票的旅客均有权选择免费变更至春秋航空的下班机</span>。除上述退票权或变更权之外，鉴于春秋整体一贯低价，无论何种原因航班延误或取消，春秋不承诺提供任何其它补偿。</p><p style="padding: 0px;margin-top: 0px;margin-bottom: 0px">4、免费行李额（包括托运行李和非托运行李）为15公斤（婴儿无免费行李额）。超重部分需支付逾重行李费,每公斤按国家公布的经济舱全票价的1.5%计算。</p><p style="padding: 0px;margin-top: 0px;margin-bottom: 0px">5、每位旅客带入客舱的非托运行李（包括自理行李和随身携带物品）仅限一件，重量不应超过5公斤，尺寸不应超过20厘米×30厘米×40厘米。超重或超限的，均应办理托运。</p><p style="padding: 0px;margin-top: 0px;margin-bottom: 0px;text-indent: 28px;line-height: 39px;background-image: none;background-attachment: scroll;background-position: 0% 0%;background-repeat: repeat repeat"><span style="padding: 0px;font-size: 19px;font-family: 宋体;color: rgb(68, 68, 68)">&nbsp;</span></p><p><br/></p>', '<h3 style="padding: 0px; margin: 18px 0px 8px; font-size: 12px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">费用包含</h3><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><br/></p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">1.交通：往返团队经济舱机票含税费（团队机票将统一出票，如遇政府或航空公司政策性调整燃油税费，在未出票的情况下将进行多退少补，敬请谅解。团队机票一经开出，不得更改、不得签转、不得退票），当地旅游巴士。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">2.小交通：三亚凤凰机场往返接送服务。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">3.住宿：行程所列酒店。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">4.用餐：行程中团队标准用餐，全程含4早5正，早餐为自助早，正餐餐标30元/人，标准为8菜1汤，4荤4素，所有餐均不含酒水。用餐10人一桌，不足10人根据标准团餐餐标安排，按每人1菜菜量相应减少。如用餐人数不足6人，我社另按餐标退还。（中式餐或自助餐或特色餐，自由活动期间用餐请自理；如因自身原因放弃用餐，则餐费不退）。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">5.门票：行程中所含的景点首道大门票，行程内景区均含首道门票（必须乘坐观光车/索道/燃气车等才能进入到景区的，行程中已经标注“含景点观光车/索道/燃气车”）。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">6.导服：当地中文导游。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">7.儿童价标准：年龄2~12周岁（不含），不占床，含往返儿童机票，当地车位，半价正餐（不含早）；其余请自理【1.2米以下儿童免早，1.2-1.4米早餐64元/人/餐，1.4米以上全早128元/人/餐】。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">8.赠送：含海南政府调节基金。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><br/></p><h3 style="padding: 0px; margin: 18px 0px 8px; font-size: 12px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">费用不包含</h3><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><br/></p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">1.小交通：景区内用车。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">2.小交通：出发地机场往返接送服务。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">3.单房差。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">4.门票：行程中注明需要另行支付的自费景点。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">5.因交通延阻、罢工、天气、飞机机器故障、航班取消或更改时间等不可抗力原因所引致的额外费用。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">6.酒店内洗衣、理发、电话、传真、收费电视、饮品、烟酒等个人消费。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">7.当地参加的自费以及以上“费用包含”中不包含的其它项目。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">8.旅游人身意外保险</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><span style="padding: 0px; margin: 0px; color: rgb(241, 102, 26);">* 建议购买旅游人身意外保险&nbsp;</span><a href="http://www.tuniu.com/help/ejchina.shtml" rel="nofollow" target="_blank" class="f_4e9700 fb" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0); font-weight: 700;">国内旅游意外伤害保障计划</a></p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><br/></p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><span style="padding: 0px; margin: 0px; color: rgb(241, 102, 26);">* 由于航空公司机票价格时时变动，您提交信息后可能会出现没有位置或者价格波动，请以占位及出票后的价格为准。</span></p><p><br/></p>', '<ul class="how_toorder list-paddingleft-2" style="list-style-type: none;"><li><p class="ht_sub1" style="padding: 0px 0px 0px 15px; line-height: 24px; color: rgb(102, 102, 102); font-family: 微软雅黑; text-indent: -15px; margin-top: 0px !important; margin-bottom: 0px !important;">1. 网上预订与电话预订同等有效，下单更自主、便捷；</p><p class="ht_sub1" style="padding: 0px 0px 0px 15px; line-height: 24px; color: rgb(102, 102, 102); font-family: 微软雅黑; text-indent: -15px; margin-top: 0px !important; margin-bottom: 0px !important;">2. 享受更多专属优惠：</p><p class="ht_sub2" style="padding: 0px 0px 0px 20px; line-height: 24px; color: rgb(102, 102, 102); font-family: 微软雅黑; text-indent: -15px; margin-top: 0px !important; margin-bottom: 0px !important;">（1）早预订多人预订最高可省 26元/每成人；</p><p class="ht_sub2" style="padding: 0px 0px 0px 20px; line-height: 24px; color: rgb(102, 102, 102); font-family: 微软雅黑; text-indent: -15px; margin-top: 0px !important; margin-bottom: 0px !important;">（2）旅游归来发表点评，每成人获赠200元抵用券+5元现金；</p><p class="ht_sub2" style="padding: 0px 0px 0px 20px; line-height: 24px; color: rgb(102, 102, 102); font-family: 微软雅黑; text-indent: -15px; margin-top: 0px !important; margin-bottom: 0px !important;">（3）旅游归来写游记，最高可获1000元；</p></li><li><p class="ht_sub1" style="padding: 0px 0px 0px 15px; line-height: 24px; color: rgb(102, 102, 102); font-family: 微软雅黑; text-indent: -15px; margin-top: 0px !important; margin-bottom: 0px !important;">1.网上提交的订单将由专属客服跟进处理，由该专属客服一对一的为您服务；</p><p class="ht_sub1" style="padding: 0px 0px 0px 15px; line-height: 24px; color: rgb(102, 102, 102); font-family: 微软雅黑; text-indent: -15px; margin-top: 0px !important; margin-bottom: 0px !important;">2.专属客服会根据您提交的订单为您进行核实位置、确认出游价格等工作，一般会致电与您确认您的出游 要求，您的任何疑问，均可由专属客服为您解答；</p><p class="ht_sub1" style="padding: 0px 0px 0px 15px; line-height: 24px; color: rgb(102, 102, 102); font-family: 微软雅黑; text-indent: -15px; margin-top: 0px !important; margin-bottom: 0px !important;"><br/></p><p class="ht_sub1" style="padding: 0px 0px 0px 15px; line-height: 24px; color: rgb(102, 102, 102); font-family: 微软雅黑; text-indent: -15px; margin-top: 0px !important; margin-bottom: 0px !important;">3.订单完成确认后，即可进行签约付款，建议您选择方便快捷的网上签约、网上支付完成签约付款，如您 有其他签约付款的要求，也可告知专属客服；</p></li></ul><p><br/></p>', 0);
INSERT INTO `pa_product` (`id`, `cid`, `title`, `photo`, `from_city`, `to_city`, `supplier`, `keywords`, `description`, `status`, `summary`, `publish_time`, `update_time`, `content`, `aid`, `visa_desc`, `travel_desc`, `fee_desc`, `booking_notes`, `is_deleted`) VALUES
(49, 51, '[五一]<迪拜-阿布扎比-沙迦7日游>2晚国五、2晚希尔顿，每单减300 ', 'upload_photo/20140425/5359d0186b5b8.png', '0', '0', 3, '', '', 0, '全程4晚国际五星酒店住宿，迪拜、阿布扎比各住2晚，其中阿布扎比2晚为希尔顿酒店行程可升级迪拜塔观光门票、沙漠冲沙（含阿拉伯烧烤晚餐）、法拉利主题公园门票、678酒店用餐项目，详情可见升级方案。678星用餐体验：6星亚特兰蒂斯晚餐，7星帆船早餐，8星酋长皇宫晚餐（6人以上成行）；所有用餐一般安排自助餐或阿拉伯式/西式几道式用餐，酒店按照新的规定可能会变更用餐标准。★行程安排：阿提哈德航空，上海直飞阿…', 0, 1395897178, '<p>全程4晚国际五星酒店住宿，迪拜、阿布扎比各住2晚，其中阿布扎比2晚为希尔顿酒店\n行程可升级迪拜塔观光门票、沙漠冲沙（含阿拉伯烧烤晚餐）、法拉利主题公园门票、678酒店用餐项目，详情可见升级方案。\n\n\n678星用餐体验：6星亚特兰蒂斯晚餐，7星帆船早餐，8星酋长皇宫晚餐（6人以上成行）；\n所有用餐一般安排自助餐或阿拉伯式/西式几道式用餐，酒店按照新的规定可能会变更用餐标准。\n\n\n★行程安排：阿提哈德航空，上海直飞阿布扎比。\n★游玩安排：乘坐棕榈岛高架桥上最新的单轨列车外观仿照传说中失落海底古城亚特兰蒂斯而建的亚特兰蒂斯酒店。 \n感受迪拜令人叹为观止的帆船酒店、世界最高建筑迪拜塔、人造景观棕榈岛的神奇杰作。 \n欣赏未来世界之最，夺人眼球的地标式建筑。</p>', 1, '<p style="padding: 0px; margin-top: 0px; line-height: 1.7; word-wrap: break-word; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; white-space: normal; background-color: rgb(255, 255, 255);">阿联酋签证</p><p class="mb" style="padding: 0px; margin-top: 0px; margin-bottom: 15px; line-height: 1.7; word-wrap: break-word; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; white-space: normal; background-color: rgb(255, 255, 255);">请根据自身情况选择签证所需材料：&nbsp;<a href="http://www.tuniu.com/visa/157" title="" target="_blank" class="cgreen" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0);">在职人员、退休、自由职业者</a>&nbsp;<a href="http://www.tuniu.com/visa/2161" title="" target="_blank" class="cgreen" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0);">学生、学龄前儿童</a></p><p class="mb" style="padding: 0px; margin-top: 0px; margin-bottom: 15px; line-height: 1.7; word-wrap: break-word; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; white-space: normal; background-color: rgb(255, 255, 255);">您可以选择快递材料到门市或者亲自去门市递交材料。<a href="http://www.tuniu.com/help/sh_map.shtml" title="" target="_blank" class="cgreen" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0);">查看门市地址</a></p><p class="mb" style="padding: 0px; margin-top: 0px; margin-bottom: 15px; line-height: 1.7; word-wrap: break-word; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; white-space: normal; background-color: rgb(255, 255, 255);">请确保因私护照有效：本次旅游归国后至少还有6个月以上有效期，且尚有签证所需空白签证页。</p><p style="padding: 0px; margin-top: 0px; line-height: 1.7; word-wrap: break-word; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; white-space: normal; background-color: rgb(255, 255, 255);">南京银行 “<a href="http://www.tuniu.com/help/bank_nj.shtml#q1" class="cgreen" target="_blank" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0);">金梅花旅游服务</a>”，旅游保证金交纳不烦恼。（您也可选择渣打银行 “<a href="http://www.tuniu.com/help/bank.shtml#q1" class="cgreen" target="_blank" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0);">创享旅游服务</a>”，光大银行 “<a href="http://www.tuniu.com/help/bank_ceb.shtml#q1" class="cgreen" target="_blank" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0);">旅游保证金服务</a>”，浦东发展银行 “<a href="http://www.tuniu.com/help/bank_sp.shtml#q1" class="cgreen" target="_blank" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0);">出境旅游保证金服务</a>”，北京银行 “<a href="http://www.tuniu.com/help/bank_bj.shtml#q1" class="cgreen" target="_blank" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0);">旅游金融服务</a>”）</p><p class="mb" style="padding: 0px; margin-top: 0px; margin-bottom: 15px; line-height: 1.7; word-wrap: break-word; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; white-space: normal; background-color: rgb(255, 255, 255);">收取材料的截止日期如下：<br style="padding: 0px; margin: 0px;"/>2014-04-30出发团队，2014-04-17下午15 :30截止收取材料;2014-05-08出发团队，2014-04-24下午15 :30截止收取材料;<br style="padding: 0px; margin: 0px;"/>2014-05-14出发团队，2014-04-28下午15 :30截止收取材料;2014-05-22出发团队，2014-05-08下午15 :30截止收取材料;<br style="padding: 0px; margin: 0px;"/>2014-06-04出发团队，2014-05-22下午15 :30截止收取材料;2014-06-11出发团队，2014-05-29下午15 :30截止收取材料;</p><p><br/></p>', '<h3 style="margin: 0px 0px 10px; font-size: 12px; zoom: 1; float: none; width: 778.4000244140625px; padding: 0px !important; background-image: none !important;"><span style="padding: 0px 0px 0px 10px; margin: 0px 10px 0px 0px; font-size: 14px; background-image: url(http://img1.tuniucdn.com/ui/v2/images/diNtian.png); float: left; background-position: 0px 4px; background-repeat: no-repeat no-repeat;">第1天</span><p>上海</p></h3><p>晚上于上海浦东国际机场T2航站楼集合。</p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">用餐</span></p><p>早餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">敬请自理</span>&nbsp;午餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">敬请自理</span>&nbsp;晚餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">敬请自理</span></p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">住宿</span></p><p>飞机上</p><h3 style="margin: 0px 0px 10px; font-size: 12px; zoom: 1; float: none; width: 778.4000244140625px; padding: 0px !important; background-image: none !important;"><span style="padding: 0px 0px 0px 10px; margin: 0px 10px 0px 0px; font-size: 14px; background-image: url(http://img1.tuniucdn.com/ui/v2/images/diNtian.png); float: left; background-position: 0px 4px; background-repeat: no-repeat no-repeat;">第2天</span><p>上海<img src="http://127.0.0.1/tripbox/Uploads/upload_image/20140327/39231395897114.gif" data-src="http://img.tuniucdn.com/icons/route/plain.gif" style="padding: 0px; margin: 0px 5px; border: 0px; display: inline;"/>阿布扎比</p></h3><p><span style="padding: 0px; margin: 0px; font-weight: bold;"><span class="_0000FF" style="padding: 0px; margin: 0px; color: rgb(0, 0, 255) !important;">参考航班： EY867 0100/0625+1 （飞行约10小时）</span></span><br style="padding: 0px; margin: 0px;"/><br style="padding: 0px; margin: 0px;"/>凌晨搭乘国际航班飞往阿联酋首都——阿布扎比。 抵达后机场内柜台照眼睛确认身份。导游关外接机.</p><p>我们跟随车导前去参观中东最大的清真寺★扎耶德清真寺（不少于1小时）。该清真寺是世界第六大清寺，可同时容纳四万名信徒。耗资五十五亿美元，整个建筑群都用来自希腊的汉白玉包裹着，非常的典雅肃穆，是拥有全世界最大的手工地毯及最多吊灯的清真寺。建筑及设计壮观华丽无与伦比，令人惊叹。<br style="padding: 0px; margin: 0px;"/><br style="padding: 0px; margin: 0px;"/><span class="_FF0000" style="padding: 0px; margin: 0px; color: rgb(255, 0, 0) !important; font-weight: bold;">温馨提示：</span><br style="padding: 0px; margin: 0px; font-weight: bold;"/><span class="_FF0000" style="padding: 0px; margin: 0px; color: rgb(255, 0, 0) !important;">如遇朝拜、休息日或超过参观人数上限时扎伊德清真寺只可外观，不可入内参观！！</span><br style="padding: 0px; margin: 0px;"/><span class="_FF0000" style="padding: 0px; margin: 0px; color: rgb(255, 0, 0) !important;">（即日起，扎耶德清真寺不再提供黑袍及头巾，请客人自备！请按照如下要求准备衣着：温和，保守，宽松的衣服，长袖，长裙，长裤。禁止透视装男士禁止穿短裤。女士不可穿短裤，裙子必须长及脚踝。禁止紧身衣，泳装，沙滩装。）</span></p><p>随后前往阿布扎比美丽的被誉为★海湾的曼哈顿（车游）的海滨观赏风景和★Batina老城区（车游），期间有众多设计独特的清真寺、喷泉和街心花园，与海边的自然美景结合，让人留连忘返。 继续随车和导游前往★国会大厦（外观），★酋长皇宫酒店（外观），酋长皇宫酒店是一座古典式的阿拉伯皇宫式建筑，是世上唯一的八星级酒店。远远看去，它有点像清真寺，也有点像传说中的辛巴德或阿里巴巴时代的皇宫。每座宫殿都有一个传说的故事，具有很浓的民族色彩。这座用30亿美金“堆”出来的酒店，其奢侈程度，大概是除了“富得流油”的阿拉伯人外别的人都难以想象的，大家在此尽情拍照留念。</p><p>接着前往★人工岛探访★阿布扎比民俗村（不少于30分钟），里面的建筑和环境，清晰的还原了当年阿布扎比原著居民的生活状态和风貌，从民俗村里遥望对岸的阿不扎比市，可以看到海对岸的阿布扎比城的美丽海景。一边是木船茅棚，一边是高楼大厦，不能不感叹时代日新月异的发展。一些时候，民俗村里还有歌舞表演等娱乐节目。<br style="padding: 0px; margin: 0px;"/><br style="padding: 0px; margin: 0px;"/><span class="_0000FF" style="padding: 0px; margin: 0px; color: rgb(0, 0, 255) !important;">15点司导服务结束。您可安排自由活动。&nbsp;</span><br style="padding: 0px; margin: 0px;"/><span class="_0000FF" style="padding: 0px; margin: 0px; color: rgb(0, 0, 255) !important;">自由活动推荐景点：阿布扎比购物中心Maria Mall；法拉利主题公园；Yas水世界乐园；阿拉伯海鲜自助餐；八星皇宫晚餐。 费用和预定请向各酒店前台咨询。&nbsp;</span></p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">用餐</span></p><p>早餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">&nbsp;机上早餐&nbsp;</span>&nbsp;午餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">中式午餐&nbsp;</span>&nbsp;晚餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">敬请自理</span></p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">住宿</span></p><p>阿布扎比国际五星酒店</p><h3 style="margin: 0px 0px 10px; font-size: 12px; zoom: 1; float: none; width: 778.4000244140625px; padding: 0px !important; background-image: none !important;"><span style="padding: 0px 0px 0px 10px; margin: 0px 10px 0px 0px; font-size: 14px; background-image: url(http://img1.tuniucdn.com/ui/v2/images/diNtian.png); float: left; background-position: 0px 4px; background-repeat: no-repeat no-repeat;">第3天</span><p>阿布扎比全天自由活动</p></h3><p>全天自由活动，不含用车、导游和午晚餐。</p><p>★Marina Mall是位于阿布扎比Marina Village酋长国宫殿酒店附近的Marina Mall，地方偌大，共有4层楼。约300&nbsp;间店铺，且有颇多餐厅及Cafe，感觉极之悠闲，商场中央还有一个喷水池表演，也吸引很多小朋友来观赏。品牌方面，Burberry、Chanel、Gucci、Versace、Louis Vuitton&nbsp;及Yves Saint-Laurent&nbsp;等名牌都齐备，在阿布扎比零销售税的政策下，依然颇有吸引力。此购物中心的消费和设施包括食品店、阿布扎比Marina Sky Tower、电影院、儿童乐园、溜冰场、了望塔。此外，一家大小也可逛逛当地的家乐福、宜家家居，在冷气下度过舒适下午。&nbsp;<br style="padding: 0px; margin: 0px;"/><br style="padding: 0px; margin: 0px;"/><span class="_0000FF" style="padding: 0px; margin: 0px; color: rgb(0, 0, 255) !important;">自由活动推荐景点：阿布扎比购物中心Maria Mall；法拉利主题公园；Yas水世界乐园；阿拉伯海鲜自助餐；八星皇宫晚餐。 费用和预定请向各酒店前台咨询。</span></p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">用餐</span></p><p>早餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">酒店早餐</span>&nbsp;午餐：敬请自理&nbsp;<span class="po_dining_diy" style="padding: 0px; margin: 0px;"></span>&nbsp;晚餐：敬请自理<span class="po_dining_diy" style="padding: 0px; margin: 0px;"></span></p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">住宿</span></p><p>阿布扎比国际五星酒店</p><h3 style="margin: 0px 0px 10px; font-size: 12px; zoom: 1; float: none; width: 778.4000244140625px; padding: 0px !important; background-image: none !important;"><span style="padding: 0px 0px 0px 10px; margin: 0px 10px 0px 0px; font-size: 14px; background-image: url(http://img1.tuniucdn.com/ui/v2/images/diNtian.png); float: left; background-position: 0px 4px; background-repeat: no-repeat no-repeat;">第4天</span><p>阿布扎比<img src="http://127.0.0.1/tripbox/Uploads/upload_image/20140327/85701395897115.gif" data-src="http://img.tuniucdn.com/icons/route/bus.gif" style="padding: 0px; margin: 0px 5px; border: 0px; display: inline;"/>&nbsp;迪拜</p></h3><p>酒店早餐后前往参观★迪拜博物馆（不少于45分钟）。这座前身曾是皇宫,要塞及海防的古堡,是迪拜最古老的建筑物。走进博物馆,就像进入时光隧道一样,从博物馆的露天展览,可看到古阿拉伯人人以前的住屋模式,包含最早期的风塔。室内展区部分位于在古堡底层,是一个仿古市集,展现阿拉伯传统风貌的艺术馆,其中包括已有3-4千年历史的古墓铜器,值得慢慢欣赏。</p><p>随后乘坐传统的★水上的士横渡迪拜河，前往当地著名的★黄金香料市场。您可以坐在木船上静静的欣赏迪拜河两岸的现代建筑物。迪拜河有“海湾威尼斯”之称，一条宽阔的港湾向内地延伸约十公里，像一条水面宽阔的大河把这个城市分为两半。</p><p>然后驱车前往★Jumeirah海滨浴场（外观）、★Jumeirah海滨酒店（外观）、★Jumeirah清真寺(车游)、 ★迪拜酋长皇宫 (车游)，有机会看到孔雀开屏的胜景。★七星帆船酒店（外观），金帆船酒店是阿拉伯人奢侈的象征，亦是迪拜的新标志，豪华的佐证非笔墨可言喻。帆船酒店时刻散发着浓浓的阿拉伯风味。<br style="padding: 0px; margin: 0px;"/>随后前往外观有阿拉伯度假村之称的“朱美拉古城堡”-★运河酒店（不少于30分钟），在有浓郁阿拉伯风情的古堡酒店内古代集市（仿古）自由购物，欣赏小木船悠扬穿梭于古堡饶城河之间的闲情惬意。</p><p>继而乘坐单轨列车，以全新角度饱览全球瞩目的号称是“世界上最大的人工岛”、也有“世界第八大奇景”之称的★The Palm棕榈岛美丽的风光。让您深入了解这项突破人类工程史的伟大计划。整个由1亿平方公尺沙石建成的棕榈岛，外型酷似棕榈树，将来将可容纳50间豪华酒店、2500间沙滩住宅别墅、2400间面海住宅大厦、游艇会、水上乐园、餐馆、大型购物中心、运动设施、水疗设施及戏院等等。最终我们深入89棕榈岛腹地参观全迪拜最宏伟的★亚特兰蒂斯酒店（外观）。</p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">用餐</span></p><p>早餐：酒店早餐&nbsp;<span class="po_dining_diy" style="padding: 0px; margin: 0px;"></span>&nbsp;午餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">&nbsp;敬请自理&nbsp;&nbsp;</span>晚餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">敬请自理</span></p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">住宿</span></p><p>迪拜国际五星酒店</p><h3 style="margin: 0px 0px 10px; font-size: 12px; zoom: 1; float: none; width: 778.4000244140625px; padding: 0px !important; background-image: none !important;"><span style="padding: 0px 0px 0px 10px; margin: 0px 10px 0px 0px; font-size: 14px; background-image: url(http://img1.tuniucdn.com/ui/v2/images/diNtian.png); float: left; background-position: 0px 4px; background-repeat: no-repeat no-repeat;">第5天</span><p>迪拜全天自由活动</p></h3><p>全天自由活动，不含用车、导游和午晚餐。&nbsp;<br style="padding: 0px; margin: 0px;"/><br style="padding: 0px; margin: 0px;"/>自由活动推荐景点：Mall of Emirates；Dubai Mall；亚特兰蒂斯自助午晚餐；迪拜塔登塔观景；七星帆船自助午晚餐； 费用和预定请向各酒店前台咨询。<br style="padding: 0px; margin: 0px;"/><br style="padding: 0px; margin: 0px;"/>★Mall of Emirates是中东地区最前卫最顶级的精品购物城，揽尽各国际名牌精品商店，并拥有戏院、游乐场以及一座世界第三大的室内滑雪场等各项娱乐设施，以及多国口味的餐厅选择。逛累了，建议您找间特色餐厅喝杯咖啡，让悠闲购物的情境更为加分。&nbsp;<br style="padding: 0px; margin: 0px;"/>★Dubai Mall是迪拜最新落成的最大最现代的购物中心，规模惊人，有1200家零售店、150多家餐饮设施和数不胜数的休闲店铺。其中44万平方英尺的时装大道堪称一大亮点，该景点设有一座游弋着鲨鱼的水族馆、一座室内主题公园、一个溜冰场和一座可以同时放映22部电影的影院。此外千万不要错过全球最大的室内的黄金市场。购物完毕后您还可以步行到最新诞生的世界第一高楼，斥资15亿美金打造的哈利法塔Burj Khalifa Tower。傍晚可前往购物城室外的人工湖，一边欣赏世界最高建筑迪拜塔的雄姿（外观），一边欣赏湖中精彩纷珵的世界最大的音乐喷泉表演,充满了视听享受，蔚为壮观。音乐喷泉参考时间19点开始，每半小时表演一次，直至24点。</p><p>当地景点：扎耶德清真寺</p><p>该清真寺是世界第六大清寺，可同时容纳四万名信徒。是拥有世界最大的地毯及最多吊灯的清真寺。建筑及设计壮观华丽无与伦比，令人惊叹。&nbsp;<br style="padding: 0px; margin: 0px;"/><span class="_FF0000" style="padding: 0px; margin: 0px; color: rgb(255, 0, 0) !important;">【具体清真寺注意事项请看附件旅游须<span class="_FF0000" style="padding: 0px; margin: 0px;">知</span></span><span class="_FF0000" style="padding: 0px; margin: 0px; color: rgb(255, 0, 0) !important;">】</span><br style="padding: 0px; margin: 0px;"/>&nbsp;温和，保守，宽松的衣服，长袖，长裙，长裤。禁止透视装男士禁止穿短裤。女士不可穿短褲和裙子必須長及腳踝。禁止紧身衣，泳装，沙滩装。</p><ul class="time_img_photo clearfix list-paddingleft-2" style="list-style-type: none;"><li><p><a class="niuren_light" href="http://images.tuniu.com/images/2013-09-22/N/Np20ZOC4U3xPrf3.jpg" style="padding: 0px; margin: 0px; color: rgb(64, 64, 64);"><img class="base64" src="http://127.0.0.1/tripbox/Uploads/upload_image/20140327/62151395897116.jpg" data-src="http://images.tuniu.com/images/2013-09-22/N/Np20ZOC4U3xPrf3n.jpg" style="padding: 0px; margin: 0px; border: 0px; width: 320px; height: 240px; display: inline;"/></a></p><p>迪拜塔</p></li><li><p><a class="niuren_light" href="http://images.tuniu.com/images/2013-09-22/3/3b0uX664ooccGGuu.jpg" style="padding: 0px; margin: 0px; color: rgb(64, 64, 64);"><img class="base64" src="http://127.0.0.1/tripbox/Uploads/upload_image/20140327/61791395897116.jpg" data-src="http://images.tuniu.com/images/2013-09-22/3/3b0uX664ooccGGuun.jpg" style="padding: 0px; margin: 0px; border: 0px; width: 320px; height: 240px; display: inline;"/></a></p><p>Dubai Mall<br style="padding: 0px; margin: 0px;"/></p></li></ul><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">用餐</span></p><p>早餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">&nbsp;酒店早餐&nbsp;</span>&nbsp;午餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">&nbsp;敬请自理&nbsp;&nbsp;</span>晚餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">敬请自理</span></p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">住宿</span></p><p>迪拜国际五星酒店</p><h3 style="margin: 0px 0px 10px; font-size: 12px; zoom: 1; float: none; width: 778.4000244140625px; padding: 0px !important; background-image: none !important;"><span style="padding: 0px 0px 0px 10px; margin: 0px 10px 0px 0px; font-size: 14px; background-image: url(http://img1.tuniucdn.com/ui/v2/images/diNtian.png); float: left; background-position: 0px 4px; background-repeat: no-repeat no-repeat;">第6天</span><p>迪拜<img src="http://127.0.0.1/tripbox/Uploads/upload_image/20140327/85701395897115.gif" data-src="http://img.tuniucdn.com/icons/route/bus.gif" style="padding: 0px; margin: 0px 5px; border: 0px; display: inline;"/>沙迦 （单程约30分钟）<img src="http://127.0.0.1/tripbox/Uploads/upload_image/20140327/85701395897115.gif" data-src="http://img.tuniucdn.com/icons/route/bus.gif" style="padding: 0px; margin: 0px 5px; border: 0px; display: inline;"/>迪拜<img src="http://127.0.0.1/tripbox/Uploads/upload_image/20140327/85701395897115.gif" data-src="http://img.tuniucdn.com/icons/route/bus.gif" style="padding: 0px; margin: 0px 5px; border: 0px; display: inline;"/>阿布扎比 （约2小时车程）<img src="http://127.0.0.1/tripbox/Uploads/upload_image/20140327/39231395897114.gif" data-src="http://img.tuniucdn.com/icons/route/plain.gif" style="padding: 0px; margin: 0px 5px; border: 0px; display: inline;"/>上海</p></h3><p>参考航班 ： EY862 2240/1110+1 （飞行约9小时）</p><p>早餐后驱车前往阿联酋第三大城市-沙迦，此城市是唯一一座同时坐拥“阿拉伯湾”和“阿曼湾”两条海岸线的酋长国。具有丰富历史传统的沙迦以出色的艺术和建筑而闻名，拥有十五座个以上的博物馆也是其引以为豪之处！</p><p>抵达后车游★文化广场，外观★酋长皇宫、★古兰经纪念碑并参观★考古博物馆(不少于30分钟),该博物馆全面展示了中东地区从公元前5000年直至今日的历史背景和环境的变化。外观★那不达大宅这是一座有150年历史的老宅，<span class="_FF0000" style="padding: 0px; margin: 0px; color: rgb(255, 0, 0) !important;">（古宅维修不对外开放，故为外观）</span>参观★火车头黄金手工艺品市场。</p><p>结束沙迦观光后前往位于阿拉伯海湾岸上的阿治曼，这是阿联酋七个酋长国中最小的一座，但确拥有长达16公里的白色沙滩。我们将您带到★阿治曼海滨，让您停车漫步在美丽的海滨之路上，慢慢欣赏眼前的美景。<br style="padding: 0px; margin: 0px;"/>午餐后前往哈利法塔旁边最新落成的迪拜最大最现代的购物中心Dubai Mall自由活动，晚上于指定时间集合乘车前往机场，搭乘国际航班返回上海。</p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">用餐</span></p><p>早餐：敬请自理&nbsp; 午餐： 中式午餐 &nbsp; 晚餐：敬请自理</p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">住宿</span></p><p>飞机上</p><h3 style="margin: 0px 0px 10px; font-size: 12px; zoom: 1; float: none; width: 778.4000244140625px; padding: 0px !important; background-image: none !important;"><span style="padding: 0px 0px 0px 10px; margin: 0px 10px 0px 0px; font-size: 14px; background-image: url(http://img1.tuniucdn.com/ui/v2/images/diNtian.png); float: left; background-position: 0px 4px; background-repeat: no-repeat no-repeat;">第7天</span><p>上海</p></h3><p>中午时分，抵达上海。结束愉快的阿联酋心动梦想之旅。</p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">用餐</span></p><p>早餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">敬请自理</span>&nbsp;午餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">敬请自理</span>&nbsp;晚餐：<span class="po_dining_diy" style="padding: 0px; margin: 0px;">敬请自理</span></p><p><span style="padding: 0px; margin: 0px 15px 0px 0px; font-weight: 700; float: left;">住宿</span></p><p>温馨的家</p><p><br/></p>', '<h3 style="padding: 0px; margin: 18px 0px 8px; font-size: 12px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">费用包含</h3><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><br/></p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">1.交通：往返团队经济舱机票含税费（团队机票将统一出票，如遇政府或航空公司政策性调整燃油税费，在未出票的情况下将进行多退少补，敬请谅解。团队机票一经开出，不得更改、不得签转、不得退票），当地旅游巴士。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">2.签证：团队旅游签证500元/人。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">3.住宿：行程所列酒店。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">4.用餐：行程中团队标准用餐，（中式餐或自助餐或特色餐，含飞机上用餐，自由活动期间用餐请自理；如因自身原因放弃用餐，则餐费不退）。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">5.门票：行程中所含的景点首道大门票。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">6.导服：专职领队和当地中文导游。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">7.儿童价标准：年龄2~12周岁（不含），不占床，其余标准同成人。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">8.赠送：海外期间每人每天赠送一瓶矿泉水。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">9.其他：司机导游服务费。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><br/></p><h3 style="padding: 0px; margin: 18px 0px 8px; font-size: 12px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">费用不包含</h3><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><br/></p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">1.单房差。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">2.门票：行程中注明需要另行支付的自费景点。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">3.其他：预订城市到出发城市的往返交通费用。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">4.出入境个人物品海关征税，超重行李的托运费、保管费。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">5.因交通延阻、战争、政变、罢工、天气、飞机机器故障、航班取消或更改时间等不可抗力原因所引致的额外费用。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">6.酒店内洗衣、理发、电话、传真、收费电视、饮品、烟酒等个人消费。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">7.当地参加的自费以及以上“费用包含”中不包含的其它项目。</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">8.旅游人身意外保险</p><p style="padding: 0px; margin-top: 8px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><span style="padding: 0px; margin: 0px; color: rgb(241, 102, 26);">* 建议购买旅游人身意外保险&nbsp;</span><a href="http://www.tuniu.com/help/alltrust.shtml" rel="nofollow" target="_blank" class="f_4e9700 fb" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0); font-weight: 700;">永诚保险境外旅游意外保险</a>&nbsp;<a href="http://www.tuniu.com/help/aiush.shtml" rel="nofollow" target="_blank" class="f_4e9700 fb" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0); font-weight: 700;">美亚保险境外旅游意外保险</a>&nbsp;<a href="http://www.tuniu.com/help/dubang.shtml" rel="nofollow" target="_blank" class="f_4e9700 fb" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0); font-weight: 700;">都邦境外旅游意外保险</a>&nbsp;<a class="f_4e9700 fb" href="http://www.tuniu.com/help/pingan.shtml" rel="nofollow" target="_blank" style="padding: 0px; margin: 0px; color: rgb(78, 151, 0); font-weight: 700;">平安境外旅游意外保险</a></p><p><br/></p>', '<h3 style="padding: 0px; margin: 18px 0px 8px; font-size: 12px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);">出行须知</h3><ul style="list-style-type: none;" class=" list-paddingleft-2"><li><p>为了确保您能够按时出行，产品确认后请在48小时内付款，同时请按要求尽快提供出游所需的材料并签订出境旅游合同。</p></li><li><p>团队报价按2人入住1间房核算，如出现单男单女，则尽量安排与其他同性别团友拼房或加床；若客人无需安排或旅行社无法安排，请补齐单房差以享用单人房间。</p></li><li><p>团队机票一经开出，不得更改，不得签转，不得退票；另飞行时间、车程时间、船程时间以当日实际所用时间为准。</p></li><li><p>如遇国家或航空公司政策性调整机票、燃油税价格，按调整后的实际价格结算。</p></li><li><p>当地购物时请慎重考虑，把握好质量与价格，请务必保存好所有的购物票据，若购物点提供发票，请索要。</p></li><li><p>是否给予签证或签注、是否准予出入境，是使领馆及有关部门的权力，如因游客自身原因或因提供材料存在问题不能及时办理签证或签注，以及被有关部门拒发签证或签注，不准出入境而影响行程的，签证费及其他费用损失由游客自行承担。</p></li><li><p>为了不耽误您的行程，请您在国际航班起飞前180分钟到达机场办理登机以及出入境相关手续；如涉及海外国内段行程，请您在航班起飞前60分钟到达机场办理登机手续。</p></li><li><p>出团通知最晚于出团前1个工作日发送，若能提前确定，我们将会第一时间通知您。</p></li><li><p>此团收客人数不足10人时，本公司会于出发前10个工作日通知取消该行程，您可以选择延期出发、更改线路出行，或退回团款。</p></li><li><p>团队游览中不允许擅自离团（自由活动除外），如有不便敬请谅解。</p></li><li><p>（1）因阿联酋当地各类特色星级酒店、特色餐厅较多，前往游览客人对于当地酒店的入住需求及特色餐用餐要求不同，我们会根据客人要求预订，所以团队中的客人住宿用餐会存在差别，但导游、领队服务、讲解及常规签约行程内的景点、用餐等服务均无差别。境外服务人员经验丰富，会合理安排各位的行程，请您提前知晓，以免产生惊讶。</p></li></ul><p style="padding: 0px; margin-top: 10px; margin-bottom: 8px; color: rgb(64, 64, 64); font-family: Tahoma, arial, 宋体, sans-serif; font-size: 12px; line-height: 14.399999618530273px; white-space: normal; background-color: rgb(255, 255, 255);"><span style="padding: 0px; margin: 0px; color: rgb(241, 102, 26);">* 此行程非本社独立成团，与其他旅行社联合发团</span></p><p><br/></p>', 0),
(50, 54, '线路名称', '', '0', '2', 0, '', '', 0, '', 0, 1397008393, '', 1, '', '', '', '<p>啊水电费</p>', 1),
(51, 54, '上海到马尔代夫10日', '', '0', '0', 0, '', '', 0, '', 0, 1397008470, '', 1, '', '', '', '', 1),
(52, 54, '一日游', 'upload_photo/20140425/5359f32426c9d.png', '1', '5', 0, '', '', 0, '啊啊啊    ', 0, 1398403897, '<p>aaaaa</p>', 0, '<p>ddd</p>', '<p>bbb</p>', '<p>ccc</p>', '', 1),
(53, 54, 'fsdf', 'upload_photo/20141112/54633c90111e4.jpg', '1', '1', 3, 'fsdf', NULL, 0, '', 1415789808, 1415789808, NULL, NULL, '<p>sdf<br/></p>', '<p>sdf<br/></p>', '<p>sdf</p>', '<p>sdf</p>', 0);

-- --------------------------------------------------------

--
-- 表的结构 `pa_product_part`
--

CREATE TABLE IF NOT EXISTS `pa_product_part` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `pid` int(10) NOT NULL,
  `date` date NOT NULL,
  `desc` text NOT NULL,
  `prime_price` float(8,2) NOT NULL COMMENT '原价',
  `retail_price` float(8,2) NOT NULL COMMENT '零售价',
  `cost_price` float(8,2) NOT NULL COMMENT '成本价',
  `children_price` float(8,2) NOT NULL,
  `baby_price` float(8,2) NOT NULL,
  `stock` int(8) NOT NULL COMMENT '库存',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

--
-- 转存表中的数据 `pa_product_part`
--

INSERT INTO `pa_product_part` (`id`, `pid`, `date`, `desc`, `prime_price`, `retail_price`, `cost_price`, `children_price`, `baby_price`, `stock`) VALUES
(27, 49, '2014-03-23', '批量03-19到03-23', 7000.00, 5000.00, 3000.00, 0.00, 0.00, 101),
(28, 49, '2014-04-02', '', 10000.00, 9000.00, 8000.00, 0.00, 0.00, 10),
(29, 49, '2014-04-03', '', 10000.00, 9000.00, 8000.00, 0.00, 0.00, 10),
(30, 49, '2014-04-04', '藏藏藏', 10000.00, 9000.00, 8000.00, 0.00, 0.00, 10),
(31, 49, '2014-04-05', '', 10000.00, 9000.00, 8000.00, 0.00, 0.00, 10),
(32, 49, '2014-04-06', '', 10000.00, 9000.00, 8000.00, 0.00, 0.00, 10),
(33, 49, '2014-04-07', '', 10000.00, 9000.00, 8000.00, 0.00, 0.00, 10),
(34, 49, '2014-04-08', '', 10000.00, 9000.00, 8000.00, 0.00, 0.00, 10),
(35, 49, '2014-03-15', '6', 3.00, 2.00, 1.00, 0.00, 0.00, 5),
(36, 47, '2014-05-01', '', 0.00, 0.00, 0.00, 0.00, 0.00, 0),
(37, 49, '2014-04-15', '', 333.00, 0.00, 0.00, 0.00, 0.00, 0),
(38, 49, '2014-04-16', '', 333.00, 0.00, 0.00, 0.00, 0.00, 0),
(39, 49, '2014-04-17', '', 333.00, 0.00, 0.00, 0.00, 0.00, 0),
(40, 49, '2014-04-18', '', 333.00, 0.00, 0.00, 0.00, 0.00, 0);

-- --------------------------------------------------------

--
-- 表的结构 `pa_reseller`
--

CREATE TABLE IF NOT EXISTS `pa_reseller` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `weibo_uid` varchar(15) DEFAULT NULL COMMENT '对应的新浪微博uid',
  `tencent_uid` varchar(20) DEFAULT NULL COMMENT '腾讯微博UID',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱地址',
  `username` varchar(20) NOT NULL COMMENT '用户昵称',
  `password` char(32) DEFAULT NULL COMMENT '密码',
  `reg_date` int(10) DEFAULT NULL,
  `reg_ip` char(15) DEFAULT NULL COMMENT '注册IP地址',
  `verify_status` int(1) DEFAULT '0' COMMENT '电子邮件验证标示 0未验证，1已验证',
  `verify_code` varchar(32) DEFAULT NULL COMMENT '电子邮件验证随机码',
  `verify_time` int(10) DEFAULT NULL COMMENT '邮箱验证时间',
  `verify_exp_time` int(10) DEFAULT NULL COMMENT '验证邮件过期时间',
  `find_fwd_code` varchar(32) DEFAULT NULL COMMENT '找回密码验证随机码',
  `find_pwd_time` int(10) DEFAULT NULL COMMENT '找回密码申请提交时间',
  `find_pwd_exp_time` int(10) DEFAULT NULL COMMENT '找回密码验证随机码过期时间',
  `avatar` varchar(100) DEFAULT NULL COMMENT '用户头像',
  `birthday` int(10) DEFAULT NULL COMMENT '用户生日',
  `sex` int(1) DEFAULT NULL COMMENT '0女1男',
  `address` varchar(50) DEFAULT NULL COMMENT '地址',
  `province` varchar(100) DEFAULT NULL COMMENT '省份',
  `city` varchar(100) DEFAULT NULL COMMENT '城市',
  `intr` varchar(500) DEFAULT NULL COMMENT '个人介绍',
  `mobile` varchar(11) DEFAULT NULL COMMENT '手机号码',
  `phone` varchar(30) DEFAULT NULL COMMENT '电话',
  `fax` varchar(30) DEFAULT NULL,
  `qq` int(15) DEFAULT NULL,
  `msn` varchar(100) DEFAULT NULL,
  `login_ip` varchar(15) DEFAULT NULL COMMENT '登录ip',
  `login_time` int(10) DEFAULT NULL COMMENT '登录时间',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='分销商会员表' AUTO_INCREMENT=354 ;

--
-- 转存表中的数据 `pa_reseller`
--

INSERT INTO `pa_reseller` (`id`, `weibo_uid`, `tencent_uid`, `email`, `username`, `password`, `reg_date`, `reg_ip`, `verify_status`, `verify_code`, `verify_time`, `verify_exp_time`, `find_fwd_code`, `find_pwd_time`, `find_pwd_exp_time`, `avatar`, `birthday`, `sex`, `address`, `province`, `city`, `intr`, `mobile`, `phone`, `fax`, `qq`, `msn`, `login_ip`, `login_time`, `is_locked`) VALUES
(351, '', '', 'asdfasdf@asdf.com', 'lingchen', 'e10adc3949ba59abbe56e057f20f883e', 1396420475, '127.0.0.1', 0, '', 0, 0, '', 0, 0, '', 0, 0, '', '', '', '', '', '', '', 0, '', '127.0.0.1', 1397717551, 0),
(352, '', '', 'chvch@163.com', 'xyz', 'e10adc3949ba59abbe56e057f20f883e', 0, '', 0, '', 0, 0, '', 0, 0, '', 0, 0, '', '', '', '', '', '', '', 0, '', '', 0, 0),
(353, '', '', 'cadsf@asdf.com', 'xyzz', 'e10adc3949ba59abbe56e057f20f883e', 1396420475, '127.0.0.1', 0, '', 0, 0, '', 0, 0, '', 0, 0, '', '', '', '', '', '', '', 0, '', '', 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `pa_role`
--

CREATE TABLE IF NOT EXISTS `pa_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `pid` smallint(6) DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='权限角色表' AUTO_INCREMENT=6 ;

--
-- 转存表中的数据 `pa_role`
--

INSERT INTO `pa_role` (`id`, `name`, `pid`, `status`, `remark`) VALUES
(1, '超级管理员', 0, 1, '系统内置超级管理员组，不受权限分配账号限制'),
(2, '管理员', 1, 1, '拥有系统仅此于超级管理员的权限'),
(3, '领导', 1, 1, '拥有所有操作的读权限，无增加、删除、修改的权限'),
(4, '测试组', 1, 1, '测试'),
(5, 'abc', 1, 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `pa_role_user`
--

CREATE TABLE IF NOT EXISTS `pa_role_user` (
  `role_id` mediumint(9) unsigned DEFAULT NULL,
  `user_id` char(32) DEFAULT NULL,
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户角色表';

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
