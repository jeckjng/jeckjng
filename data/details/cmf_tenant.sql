/*
Navicat MySQL Data Transfer

Source Server         : 直播-独立生产-中东-1035
Source Server Version : 50739
Source Host           : 157.175.4.223:3306
Source Database       : liveprod

Target Server Type    : MYSQL
Target Server Version : 50739
File Encoding         : 65001

Date: 2022-08-27 14:51:54
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `cmf_tenant`
-- ----------------------------
DROP TABLE IF EXISTS `cmf_tenant`;
CREATE TABLE `cmf_tenant` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT '' COMMENT '租户名称',
  `site` varchar(255) DEFAULT '' COMMENT '域名',
  `status` tinyint(1) NOT NULL COMMENT '状态 0-禁用 1-启用',
  `type` tinyint(1) NOT NULL COMMENT '租户类型 0-平台 1-租户',
  `appid` varchar(255) NOT NULL DEFAULT '' COMMENT 'appid',
  `appkey` varchar(255) NOT NULL DEFAULT '' COMMENT 'appkey',
  `game_tenant_id` bigint(20) NOT NULL COMMENT '游戏系统租户id',
  `initial_admin` varchar(255) DEFAULT '' COMMENT '初始管理员帐号',
  `initial_admin_email` varchar(255) DEFAULT '' COMMENT '初始管理员邮箱',
  `bank_card` varchar(64) DEFAULT '' COMMENT '收款银行卡',
  `account_name` varchar(64) DEFAULT '' COMMENT '户名',
  `account_bank` varchar(64) DEFAULT '' COMMENT '开户行',
  `balance_query_url` varchar(255) NOT NULL DEFAULT '' COMMENT '余额查询接口地址',
  `balance_update_url` varchar(255) NOT NULL DEFAULT '' COMMENT '余额更新接口地址',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `site_id` tinyint(1) NOT NULL COMMENT '租户类型：1 彩票租户 2 独立租户',
  `live_jurisdiction` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1开启，0关闭',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='租户表';

-- ----------------------------
-- Records of cmf_tenant
-- ----------------------------
INSERT INTO `cmf_tenant` VALUES ('26', '一号租户', 'https://livebackprd.meibocms.com', '1', '1', '', '157cbe5d64b5638d3ffd2be2fa5c22a6', '1', 'test01', 'test01@qq.com', '拉文', '广州银行', '冬粉银行', 'http://zbm.weicaitest.com/liveUser/getBalance', 'http://zbm.weicaitest.com/liveUser/updateBalance', '2021-05-04 08:04:49', '2022-07-23 15:27:50', '1', '1');
INSERT INTO `cmf_tenant` VALUES ('27', '第二租户', 'https://liveprod-new.jxmm168.com', '1', '1', '', '157cbe5d64b5638d3ffd2be2fa5c22a6', '2', 'test02', 'test02@qq.com', '广发银行', '哈哈哈', '北京', 'http://zbm.weicaitest.com/liveUser/getBalance', 'http://zbm.weicaitest.com/liveUser/updateBalance', '2021-05-04 08:56:21', '2022-07-23 15:27:50', '1', '1');
INSERT INTO `cmf_tenant` VALUES ('29', '我是独立租户', 'https://liveprod-new.jxmm168.com/	', '0', '1', '', '24242424', '100', 'linch0501', '123456@qq.com', '哈哈哈', '哈哈哈哈', '哈哈哈', '', '', '2021-05-05 17:19:08', '2022-08-10 06:48:09', '2', '1');
INSERT INTO `cmf_tenant` VALUES ('30', '第三租户', 'https://livebackprd.meibocms.com', '1', '1', '', '157cbe5d64b5638d3ffd2be2fa5c22a6', '3', 'admin103', '123456@qq.com', '三号银行', '三号银行', '三号银行', 'http://zbm.lg0808.com/liveUser/getBalance', 'http://zbm.lg0808.com/liveUser/updateBalance', '2021-05-24 15:03:18', '2022-07-23 15:27:50', '1', '1');
INSERT INTO `cmf_tenant` VALUES ('32', '独立租户1', 'https://liveprod-new.jxmm168.com/', '0', '1', '', '', '101', 'admin101', 'admin101@qq.com', '测试', '测试', '测试', '', '', '2021-09-24 14:27:56', '2022-08-10 10:13:31', '2', '1');
INSERT INTO `cmf_tenant` VALUES ('33', '独立租户2', 'https://68b1hegd4mvm.suipai989.com/', '0', '1', '', '', '102', 'duli2', '', '', '', '', '', '', '2022-08-10 06:34:28', '2022-08-12 07:23:53', '2', '1');
INSERT INTO `cmf_tenant` VALUES ('34', '独立租户3-mitok', 'https://tobcdmjd3zsn.mitok889.com/', '1', '1', '', '', '103', 'zh3', '', '', 'admin', '', '', '', '2022-08-12 07:23:29', '2022-08-12 07:24:15', '2', '1');
