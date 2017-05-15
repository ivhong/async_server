-- --------------------------------------------------------
-- 主机:                           211.151.70.9
-- 服务器版本:                        5.6.26-log - Source distribution
-- 服务器操作系统:                      Linux
-- HeidiSQL 版本:                  9.2.0.4947
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出  表 sms_server.curl 结构
CREATE TABLE IF NOT EXISTS `curl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(500) NOT NULL DEFAULT '',
  `data` varchar(1000) NOT NULL DEFAULT '',
  `header` varchar(1000) NOT NULL DEFAULT '',
  `method` char(50) NOT NULL DEFAULT '',
  `result` text,
  `result_header` text,
  `result_error` text,
  `addtime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `params` text NOT NULL,
  `donetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 数据导出被取消选择。


-- 导出  表 sms_server.mass_sms 结构
CREATE TABLE IF NOT EXISTS `mass_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `suid` int(11) NOT NULL DEFAULT '0' COMMENT '主账号',
  `channel` varchar(50) NOT NULL DEFAULT '0' COMMENT '短信渠道',
  `recordname` varchar(50) NOT NULL DEFAULT '' COMMENT '备案名',
  `content` text COMMENT '短信内容',
  `receivers` longtext COMMENT '发送的目标',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '发送数量',
  `successed` int(11) NOT NULL DEFAULT '0' COMMENT '成功数量',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `donetime` int(11) NOT NULL DEFAULT '0' COMMENT '完成时间',
  `refunded` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT '是否已经退款',
  PRIMARY KEY (`id`),
  KEY `suid` (`suid`),
  KEY `addtime` (`addtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信表';

-- 数据导出被取消选择。


-- 导出  表 sms_server.notify 结构
CREATE TABLE IF NOT EXISTS `notify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `suid` int(11) NOT NULL DEFAULT '0' COMMENT '主账号id',
  `kid` text NOT NULL COMMENT 'sms id',
  `type` char(10) NOT NULL DEFAULT '0' COMMENT '任务类型',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `donetime` int(11) NOT NULL DEFAULT '0' COMMENT '完成时间',
  `keyname` varchar(150) NOT NULL DEFAULT '' COMMENT '队列名',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：正常状态；2：异常状态',
  PRIMARY KEY (`id`),
  KEY `suid` (`suid`),
  KEY `channel` (`type`),
  KEY `donetime` (`donetime`,`addtime`,`keyname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='通知表，用于存放 sms 的任务列表';

-- 数据导出被取消选择。


-- 导出  表 sms_server.queue_status 结构
CREATE TABLE IF NOT EXISTS `queue_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(150) NOT NULL DEFAULT '',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '队列内数据发送错误数',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `uptime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='队列状态';

-- 数据导出被取消选择。


-- 导出  表 sms_server.receive_chuanglan 结构
CREATE TABLE IF NOT EXISTS `receive_chuanglan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `content` varchar(500) NOT NULL DEFAULT '' COMMENT '内容',
  `totime` int(11) NOT NULL DEFAULT '0' COMMENT '到达时间',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '本条记录添加时间',
  `destcode` char(25) NOT NULL DEFAULT '0' COMMENT '接收详细号段',
  `spcode` char(25) NOT NULL DEFAULT '0' COMMENT '接入号',
  `request` text COMMENT '请求参数',
  PRIMARY KEY (`id`),
  KEY `spcode` (`spcode`,`totime`),
  KEY `content` (`content`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='创蓝短信通道接收到的短信';

-- 数据导出被取消选择。


-- 导出  表 sms_server.sendrecord_chuanglan 结构
CREATE TABLE IF NOT EXISTS `sendrecord_chuanglan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `m_id` int(11) NOT NULL DEFAULT '0' COMMENT 'mass_sms id',
  `msgid` varchar(128) NOT NULL DEFAULT '0' COMMENT '渠道方消息id',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '需要发短信的总数',
  `successed` int(11) NOT NULL DEFAULT '0' COMMENT '成功了多少',
  `failure` int(11) NOT NULL DEFAULT '0' COMMENT '失败了多少',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `lasttime` int(11) NOT NULL DEFAULT '0' COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `msgid` (`msgid`),
  KEY `t_id` (`m_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='创蓝短信通道短信包发送状态表';

-- 数据导出被取消选择。


-- 导出  表 sms_server.users 结构
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pass` varchar(50) NOT NULL DEFAULT '0',
  `channel` varchar(50) NOT NULL DEFAULT '0',
  `total` int(11) NOT NULL DEFAULT '0',
  `used` int(11) NOT NULL DEFAULT '0',
  `params` text,
  `desc` text,
  `addtime` int(11) NOT NULL DEFAULT '0',
  `uptime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 数据导出被取消选择。
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
