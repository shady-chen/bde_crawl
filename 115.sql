/*
SQLyog Ultimate v10.00 Beta1
MySQL - 5.5.53 : Database - zz
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `app_banks` */

DROP TABLE IF EXISTS `app_banks`;

CREATE TABLE `app_banks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `bank_num` varchar(255) NOT NULL COMMENT '用户的账号',
  `real_name` varchar(255) NOT NULL COMMENT '用户姓名',
  `bank_which` varchar(255) NOT NULL COMMENT '账号类型 支付宝 或哪银行卡',
  `bank_where` varchar(255) DEFAULT NULL COMMENT '开户行',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  `status` int(2) DEFAULT NULL COMMENT '银行卡状态 0是删除 1是可用',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `app_banks` */

insert  into `app_banks`(`id`,`uid`,`bank_num`,`real_name`,`bank_which`,`bank_where`,`create_time`,`status`) values (5,10,'71025123@1qq.com','张三','支付宝','',1547196284,1);

/*Table structure for table `app_moneysteam` */

DROP TABLE IF EXISTS `app_moneysteam`;

CREATE TABLE `app_moneysteam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `money` decimal(11,3) NOT NULL COMMENT '流动金额',
  `user_money_now` decimal(11,3) NOT NULL COMMENT '用户没改变之前的金额',
  `user_money_later` decimal(11,3) NOT NULL COMMENT '用户改变后的金额',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  `uid` int(11) NOT NULL COMMENT 'uid',
  `create_time` int(11) NOT NULL COMMENT 'craete_time',
  `type` varchar(255) DEFAULT NULL COMMENT '资金的流向',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `app_moneysteam` */

insert  into `app_moneysteam`(`id`,`money`,`user_money_now`,`user_money_later`,`remark`,`uid`,`create_time`,`type`) values (1,'135.000','0.000','135.675','金额增加135.675',10,1547195433,'抢红包'),(2,'123.000','135.675','12.675','金额减少123,未结算金额减少0.000',10,1547196325,'提现');

/*Table structure for table `app_notice` */

DROP TABLE IF EXISTS `app_notice`;

CREATE TABLE `app_notice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '0是发给所有人的 发个人的话就是uid',
  `title` varchar(255) DEFAULT NULL COMMENT '通知标题',
  `content` text,
  `states` int(1) DEFAULT NULL COMMENT '0是未发布 1是已发布',
  `create_time` int(11) DEFAULT NULL,
  `read_states` int(1) DEFAULT NULL COMMENT '是否已读，0是未读，1是已读',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `app_notice` */

/*Table structure for table `app_order` */

DROP TABLE IF EXISTS `app_order`;

CREATE TABLE `app_order` (
  `id` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `packet_id` int(11) NOT NULL COMMENT '红包id',
  `user_phone` varchar(255) NOT NULL COMMENT '用户手机号',
  `packet_expect` varchar(255) NOT NULL COMMENT '红包期数',
  `money` decimal(11,3) NOT NULL COMMENT '订单金额',
  `status` int(1) NOT NULL COMMENT '0是过期，1待付款 2审核中 3审核通过 4 审核不通过',
  `img_url` varchar(255) DEFAULT NULL COMMENT '提交订单时的转账图片',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` int(11) NOT NULL COMMENT '订单生成时间',
  `sys_bank_num` varchar(255) DEFAULT NULL COMMENT '系统银行卡号',
  `sys_bank_which` varchar(255) DEFAULT NULL COMMENT '系统银行或支付宝',
  `sys_bank_where` varchar(255) DEFAULT NULL COMMENT '系统开户行',
  `sys_name` varchar(255) DEFAULT NULL COMMENT '银行卡所属人姓名',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

/*Data for the table `app_order` */

insert  into `app_order`(`id`,`uid`,`packet_id`,`user_phone`,`packet_expect`,`money`,`status`,`img_url`,`remarks`,`create_time`,`sys_bank_num`,`sys_bank_which`,`sys_bank_where`,`sys_name`) values (00000000001,10,3,'15880630261','201901110065','135.000',3,'\\upload\\20190111\\b079981ce2ce7d3320c91e03997a5097.jpg','',1547194858,'63384819950826262','建设银行','上海第三分行','大我人'),(00000000002,10,6,'15880630261','201901110068','45.000',1,'','',1547195817,'63384819950826262','建设银行','上海第三分行','大我人'),(00000000003,10,16,'15880630261','201901110087','198.000',1,'','',1547201560,'63384819950826262','建设银行','上海第三分行','大我人'),(00000000004,10,23,'15880630261','201901110094','33.000',1,'','',1547203623,'63384819950826262','建设银行','上海第三分行','大我人'),(00000000005,10,34,'15880630261','201901150079','133.000',1,'','',1547544696,'63384819950826262','建设银行','上海第三分行','大我人');

/*Table structure for table `app_packet` */

DROP TABLE IF EXISTS `app_packet`;

CREATE TABLE `app_packet` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `expect` varchar(255) NOT NULL COMMENT '包的期数 20181211001',
  `money` decimal(11,3) NOT NULL COMMENT '包的总金额',
  `amount` varchar(255) NOT NULL COMMENT '包的个数',
  `create_time` int(11) NOT NULL COMMENT '发包时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

/*Data for the table `app_packet` */

insert  into `app_packet`(`id`,`expect`,`money`,`amount`,`create_time`) values (1,'201901110063','1000.000','5',1547194200),(2,'201901110064','1000.000','4',1547194500),(3,'201901110065','1000.000','4',1547194800),(4,'201901110066','1000.000','5',1547195100),(5,'201901110067','1000.000','5',1547195400),(6,'201901110068','1000.000','4',1547195700),(7,'201901110078','1000.000','5',1547198700),(8,'201901110079','1000.000','5',1547199000),(9,'201901110080','1000.000','5',1547199300),(10,'201901110081','1000.000','5',1547199600),(11,'201901110082','1000.000','5',1547199900),(12,'201901110083','1000.000','5',1547200200),(13,'201901110084','1000.000','5',1547200500),(14,'201901110085','1000.000','5',1547200800),(15,'201901110086','1000.000','5',1547201100),(16,'201901110087','1000.000','4',1547201400),(17,'201901110088','1000.000','5',1547201700),(18,'201901110089','1000.000','5',1547202000),(19,'201901110090','1000.000','5',1547202300),(20,'201901110091','1000.000','5',1547202600),(21,'201901110092','1000.000','5',1547202900),(22,'201901110093','1000.000','5',1547203200),(23,'201901110094','1000.000','4',1547203500),(24,'201901110095','1000.000','5',1547203800),(25,'201901110096','1000.000','5',1547204100),(26,'201901110097','1000.000','5',1547204400),(27,'201901110098','1000.000','5',1547204700),(28,'201901110099','1000.000','5',1547205000),(29,'201901150074','1000.000','5',1547543100),(30,'201901150075','1000.000','5',1547543400),(31,'201901150076','1000.000','5',1547543700),(32,'201901150077','1000.000','5',1547544000),(33,'201901150078','1000.000','5',1547544300),(34,'201901150079','1000.000','4',1547544600),(35,'201901150080','1000.000','5',1547544900),(36,'201901150081','1000.000','5',1547545200);

/*Table structure for table `app_user` */

DROP TABLE IF EXISTS `app_user`;

CREATE TABLE `app_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(11) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'md5加密的用户登录密码',
  `money` decimal(11,3) DEFAULT NULL COMMENT '用户的余额',
  `unclear_money` decimal(11,3) DEFAULT NULL COMMENT '用户未加到余额的金额',
  `type` int(2) NOT NULL COMMENT '用户类型 0：管理员  1：普通用户',
  `state` int(2) DEFAULT NULL COMMENT '用户状态，0是冻结 1是正常，2可以登录，不能抢包',
  `invitation_code` varchar(255) DEFAULT NULL COMMENT '用户邀请码',
  `today_total` decimal(11,3) DEFAULT NULL COMMENT '今天总的打码数量',
  `sons` int(11) DEFAULT NULL COMMENT '下线数量',
  `last_login_time` int(11) DEFAULT NULL COMMENT '最后一次登录时间',
  `last_login_ip` varchar(255) DEFAULT NULL COMMENT '最后一次登录ip',
  `bonus` decimal(11,3) DEFAULT NULL COMMENT '总奖金金额',
  `token` varchar(255) DEFAULT NULL COMMENT '请求的token',
  `create_time` int(11) DEFAULT NULL COMMENT '注册时间',
  `update_time` int(11) DEFAULT NULL COMMENT '用户更新时间',
  `update_what` varchar(255) DEFAULT NULL COMMENT '最后一次更新的原因',
  `head_img_url` varchar(255) DEFAULT NULL COMMENT '头像',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Data for the table `app_user` */

insert  into `app_user`(`id`,`phone`,`password`,`money`,`unclear_money`,`type`,`state`,`invitation_code`,`today_total`,`sons`,`last_login_time`,`last_login_ip`,`bonus`,`token`,`create_time`,`update_time`,`update_what`,`head_img_url`) values (9,'15880630262','4297f44b13955235245b2497399d7a93','0.000','0.000',1,1,'123','0.000',2,NULL,NULL,'0.000',NULL,1544591755,1547195501,'修改用户金额.  ',NULL),(10,'15880630261','4297f44b13955235245b2497399d7a93','12.675','0.000',1,1,'15880630262','0.000',0,1547544687,'127.0.0.1','0.000',NULL,1547194781,1547194781,'用户自己注册','\\head.png'),(11,'15880630263','0f8c5d9249b3e177559855dc3a8ae9dc','0.000','0.000',1,1,'15880630262','0.000',0,1547544377,'127.0.0.1','0.000',NULL,1547544377,1547544377,'用户自己注册','\\head.png'),(8,'admin','4297f44b13955235245b2497399d7a93','0.000','0.000',0,1,'123','0.000',0,1547179639,'127.0.0.1','0.000',NULL,1544591755,1544591755,'开发者注册',NULL);

/*Table structure for table `app_withdraw` */

DROP TABLE IF EXISTS `app_withdraw`;

CREATE TABLE `app_withdraw` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL COMMENT '用户绑定银行卡的id',
  `money` decimal(11,3) NOT NULL COMMENT '提现金额',
  `states` int(1) NOT NULL COMMENT '提现状态 1是审核中 2是审核通过 3是审核不通过',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` int(11) NOT NULL,
  `bank_num` varchar(255) DEFAULT NULL COMMENT '用户账号',
  `real_name` varchar(255) DEFAULT NULL COMMENT '用户姓名',
  `bank_which` varchar(255) DEFAULT NULL COMMENT '账号类型 支付宝 或哪银行卡',
  `bank_where` varchar(255) DEFAULT NULL COMMENT '开户行',
  `user_phone` varchar(255) DEFAULT NULL COMMENT '用户手机号',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `app_withdraw` */

insert  into `app_withdraw`(`id`,`uid`,`bank_id`,`money`,`states`,`remarks`,`create_time`,`bank_num`,`real_name`,`bank_which`,`bank_where`,`user_phone`) values (1,10,5,'123.000',2,'',1547196325,'71025123@1qq.com','张三','支付宝','','15880630261');

/*Table structure for table `system_banks` */

DROP TABLE IF EXISTS `system_banks`;

CREATE TABLE `system_banks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_num` varchar(255) NOT NULL COMMENT '系统账号',
  `bank_which` varchar(255) DEFAULT NULL COMMENT '哪个银行或支付宝',
  `bank_where` varchar(255) DEFAULT NULL COMMENT '开户行',
  `name` varbinary(255) DEFAULT NULL COMMENT '真实姓名',
  `total_money` decimal(11,3) DEFAULT NULL COMMENT '这卡已收到总金额',
  `is_use` int(1) NOT NULL COMMENT '是否使用',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `system_banks` */

insert  into `system_banks`(`id`,`bank_num`,`bank_which`,`bank_where`,`name`,`total_money`,`is_use`,`create_time`) values (1,'63384819950826262','建设银行','上海第三分行','大我人',NULL,1,1547194852);

/*Table structure for table `system_setting` */

DROP TABLE IF EXISTS `system_setting`;

CREATE TABLE `system_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bonus_rule` decimal(11,3) DEFAULT NULL COMMENT '奖金的阶级',
  `per_money` decimal(11,3) DEFAULT NULL COMMENT '一次奖励多少',
  `star_time` int(2) DEFAULT NULL COMMENT '开始发单时间',
  `per_total` decimal(11,3) DEFAULT NULL COMMENT '每次发的总金额',
  `minManey` decimal(11,3) DEFAULT NULL COMMENT '每个红包的最小金额',
  `how_many` int(5) DEFAULT NULL COMMENT '每次发多少个',
  `end_time` int(2) DEFAULT NULL COMMENT '停止发单时间',
  `how_long` int(11) DEFAULT NULL COMMENT '每隔多久发一次',
  `bunus_money` decimal(11,3) DEFAULT NULL COMMENT '单次佣金',
  `full_money` decimal(11,3) DEFAULT NULL COMMENT '满多少金额以后,金额增加到冻结金额',
  `sons` int(11) DEFAULT NULL COMMENT '下线数量',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `system_setting` */

insert  into `system_setting`(`id`,`bonus_rule`,`per_money`,`star_time`,`per_total`,`minManey`,`how_many`,`end_time`,`how_long`,`bunus_money`,`full_money`,`sons`) values (1,'100000.000','115.000',11,'1000.000','20.000',5,8,300,'0.005','25000.000',10);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
