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
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `bank_num` varchar(255) NOT NULL COMMENT '用户的账号',
  `real_name` varchar(255) NOT NULL COMMENT '用户姓名',
  `bank_which` varchar(255) NOT NULL COMMENT '账号类型 支付宝 或哪银行卡',
  `bank_where` varchar(255) DEFAULT NULL COMMENT '开户行',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `app_banks` */

/*Table structure for table `app_notice` */

DROP TABLE IF EXISTS `app_notice`;

CREATE TABLE `app_notice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '0是发给所有人的 发个人的话就是uid',
  `content` text,
  `states` int(1) DEFAULT NULL COMMENT '0是未读 1是已读',
  `create_time` int(11) DEFAULT NULL,
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
  `money` decimal(11,3) NOT NULL COMMENT '订单金额',
  `status` int(1) NOT NULL COMMENT '0是过期，1待付款 2审核中 3审核通过 4 审核不通过',
  `img_url` varchar(255) DEFAULT NULL COMMENT '提交订单时的转账图片',
  `remarks` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` int(11) NOT NULL COMMENT '订单生成时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

/*Data for the table `app_order` */

insert  into `app_order`(`id`,`uid`,`packet_id`,`money`,`status`,`img_url`,`remarks`,`create_time`) values (00000000001,7,105,'200.000',1,'','',1544610997),(00000000002,7,106,'200.000',1,'','',1544611305),(00000000003,7,107,'200.000',1,'','',1544611523),(00000000004,7,108,'200.000',1,'','',1544611803),(00000000005,7,116,'200.000',1,'','',1544614388),(00000000006,7,117,'200.000',1,'','',1544614519),(00000000007,7,118,'200.000',1,'','',1544614804),(00000000008,7,119,'200.000',1,'','',1544615123),(00000000009,7,120,'200.000',1,'','',1544615409),(00000000010,7,121,'200.000',1,'','',1544615873);

/*Table structure for table `app_packet` */

DROP TABLE IF EXISTS `app_packet`;

CREATE TABLE `app_packet` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `expect` varchar(255) NOT NULL COMMENT '包的期数 20181211001',
  `money` decimal(11,3) NOT NULL COMMENT '包的总金额',
  `amount` varchar(255) NOT NULL COMMENT '包的个数',
  `create_time` int(11) NOT NULL COMMENT '发包时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=126 DEFAULT CHARSET=utf8;

/*Data for the table `app_packet` */

insert  into `app_packet`(`id`,`expect`,`money`,`amount`,`create_time`) values (107,'2018121258','1000.000','106',1544611500),(106,'2018121257','1000.000','105',1544611200),(105,'2018121256','1000.000','104',1544610900),(104,'2018121255','1000.000','5',1544610600),(103,'2018121254','1000.000','5',1544610300),(102,'2018121253','1000.000','5',1544610000),(101,'2018121252','1000.000','5',1544609700),(100,'2018121251','1000.000','99',1544609400),(99,'2018121250','1000.000','5',1544609100),(98,'2018121249','1000.000','97',1544608800),(97,'2018121248','1000.000','5',1544608500),(96,'2018121247','1000.000','5',1544608200),(95,'2018121246','1000.000','94',1544607900),(94,'2018121245','1000.000','93',1544607600),(93,'2018121244','1000.000','5',1544607300),(92,'2018121243','1000.000','5',1544607000),(91,'20181212411','1000.000','5',1544606700),(108,'2018121259','1000.000','107',1544611800),(109,'2018121260','1000.000','5',1544612100),(110,'2018121261','1000.000','5',1544612400),(111,'2018121262','1000.000','5',1544612700),(112,'2018121263','1000.000','5',1544613000),(113,'2018121264','1000.000','5',1544613300),(114,'2018121265','1000.000','5',1544613600),(115,'2018121266','1000.000','5',1544613900),(116,'2018121267','1000.000','115',1544614200),(117,'2018121268','1000.000','116',1544614500),(118,'2018121269','1000.000','117',1544614800),(119,'2018121270','1000.000','4',1544615100),(120,'2018121271','1000.000','4',1544615400),(121,'2018121272','1000.000','4',1544615700),(122,'2018121273','1000.000','5',1544616000),(123,'2018121274','1000.000','5',1544616300),(124,'2018121275','1000.000','5',1544616600),(125,'2018121276','1000.000','5',1544616900);

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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

/*Data for the table `app_user` */

insert  into `app_user`(`id`,`phone`,`password`,`money`,`unclear_money`,`type`,`state`,`invitation_code`,`today_total`,`sons`,`last_login_time`,`last_login_ip`,`bonus`,`token`,`create_time`,`update_time`,`update_what`) values (4,'15880630261','202cb962ac59075b964b07152d234b70','0.000','0.000',1,1,'2147483647','0.000',0,1544504384,'127.0.0.1','0.000',NULL,1544504384,1544504384,'用户自己注册'),(5,'12312345123','c20ad4d76fe97759aa27a0c99bff6710','0.000','0.000',1,1,'123456','0.000',0,1544540494,'192.168.1.2','0.000',NULL,1544540494,1544540494,'用户自己注册'),(6,'12345678912','202cb962ac59075b964b07152d234b70','0.000','0.000',1,1,'123456','0.000',0,1544540576,'192.168.1.2','0.000',NULL,1544540576,1544540576,'用户自己注册'),(7,'12345678911','4297f44b13955235245b2497399d7a93','0.000','0.000',1,1,'12','0.000',0,1544610958,'192.168.1.2','0.000',NULL,1544591755,1544591755,'用户自己注册');

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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `app_withdraw` */

/*Table structure for table `system_banks` */

DROP TABLE IF EXISTS `system_banks`;

CREATE TABLE `system_banks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_num` varchar(255) NOT NULL COMMENT '系统账号',
  `bank_which` varchar(255) DEFAULT NULL COMMENT '哪个银行或支付宝',
  `bank_where` varchar(255) DEFAULT NULL COMMENT '开户行',
  `is_use` int(1) NOT NULL COMMENT '是否使用',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `system_banks` */

/*Table structure for table `system_setting` */

DROP TABLE IF EXISTS `system_setting`;

CREATE TABLE `system_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bonus_rule` decimal(11,3) DEFAULT NULL COMMENT '奖金的阶级',
  `per_money` decimal(11,3) DEFAULT NULL COMMENT '一次奖励多少',
  `star_time` int(2) DEFAULT NULL COMMENT '开始发单时间',
  `per_total` decimal(11,3) DEFAULT NULL COMMENT '每次发的总金额',
  `how_many` int(5) DEFAULT NULL COMMENT '每次发多少个',
  `end_time` int(2) DEFAULT NULL COMMENT '停止发单时间',
  `how_long` int(11) DEFAULT NULL COMMENT '每隔多久发一次',
  `bunus_money` decimal(11,3) DEFAULT NULL COMMENT '单次佣金',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

/*Data for the table `system_setting` */

insert  into `system_setting`(`id`,`bonus_rule`,`per_money`,`star_time`,`per_total`,`how_many`,`end_time`,`how_long`,`bunus_money`) values (1,'500000.000','500.000',14,'1000.000',5,23,300,'0.006');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
