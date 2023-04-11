/*
Navicat MySQL Data Transfer

Source Server         : 直播-独立生产
Source Server Version : 50735
Source Host           : 18.162.97.180:3306
Source Database       : liveprod

Target Server Type    : MYSQL
Target Server Version : 50735
File Encoding         : 65001

Date: 2022-08-27 20:10:25
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `cmf_role`
-- ----------------------------
DROP TABLE IF EXISTS `cmf_role`;
CREATE TABLE `cmf_role` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '角色名称',
  `pid` smallint(6) DEFAULT NULL COMMENT '父角色ID',
  `status` tinyint(1) unsigned DEFAULT NULL COMMENT '状态',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `listorder` int(3) NOT NULL DEFAULT '0' COMMENT '排序字段',
  `tenant_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `parentId` (`pid`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of cmf_role
-- ----------------------------
INSERT INTO `cmf_role` VALUES ('1', '超级管理员', '0', '1', '拥有网站最高管理员权限！', '1329633709', '1329633709', '0', null);
INSERT INTO `cmf_role` VALUES ('2', '租户管理员', '0', '1', '非公共配置的增删改查权限', '1459210018', '1649482653', '0', null);
INSERT INTO `cmf_role` VALUES ('3', '查看权限', '0', '1', '仅查看权限和手动充值权限', '1461210950', '1508124227', '0', null);
INSERT INTO `cmf_role` VALUES ('4', '集成客服', null, '1', '租户客服所使用的角色', '1597045806', '1644313413', '0', null);
INSERT INTO `cmf_role` VALUES ('5', '独立财务', null, '1', '独立租户需要用到改角色', '1644313214', '1644313425', '0', null);
INSERT INTO `cmf_role` VALUES ('6', '独立客服', null, '1', '独立直播使用的客服', '1644313443', '0', '0', null);
INSERT INTO `cmf_role` VALUES ('8', '出入款专员', null, '1', '出入款专员', '1658823698', '0', '0', null);
