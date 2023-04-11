ALTER TABLE `cmf_ads`
ADD COLUMN  `grade` varchar(12) DEFAULT '0' COMMENT '剪辑工具(软件难度)' AFTER `is_rotation`,
ADD COLUMN `type_name`  varchar(255) NULL DEFAULT '' COMMENT '剪辑工具(软件类型)' AFTER `grade`;

/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50726
Source Host           : 127.0.0.1:3306
Source Database       : cs88_dev

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2022-04-16 20:49:32
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `cmf_ads_sort`
-- ----------------------------
DROP TABLE IF EXISTS `cmf_ads_sort`;
CREATE TABLE `cmf_ads_sort` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `sortname` varchar(20) DEFAULT '' COMMENT '分类名',
  `orderno` int(3) DEFAULT '0' COMMENT '序号',
  `addtime` int(11) DEFAULT '0' COMMENT '时间',
  `tenant_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of cmf_ads_sort
-- ----------------------------
INSERT INTO `cmf_ads_sort` VALUES ('1', '启动页', '1', '1624615048', null);
INSERT INTO `cmf_ads_sort` VALUES ('2', '首页', '1', '1624615081', null);
INSERT INTO `cmf_ads_sort` VALUES ('3', '我的', '1', '1624615089', null);
INSERT INTO `cmf_ads_sort` VALUES ('4', '短视频', '1', '1624615095', null);
INSERT INTO `cmf_ads_sort` VALUES ('5', '直播', '1', '1624615104', null);
INSERT INTO `cmf_ads_sort` VALUES ('6', '短视频推荐', '0', '1625050338', null);
INSERT INTO `cmf_ads_sort` VALUES ('7', '首页弹窗', '0', '1627648958', null);
INSERT INTO `cmf_ads_sort` VALUES ('8', '游戏', '0', '1627648975', null);
INSERT INTO `cmf_ads_sort` VALUES ('9', 'pc', '0', '1631092237', null);
INSERT INTO `cmf_ads_sort` VALUES ('10', 'pc 底部', '0', '1631529618', null);
INSERT INTO `cmf_ads_sort` VALUES ('11', 'pc左右广告', '0', '1633337114', null);
INSERT INTO `cmf_ads_sort` VALUES ('12', '长视频播放页广告', '0', '1634632692', null);
INSERT INTO `cmf_ads_sort` VALUES ('13', '创作说明', '0', '1650106364', null);
INSERT INTO `cmf_ads_sort` VALUES ('14', '剪辑创作技巧', '0', '1650106373', null);
INSERT INTO `cmf_ads_sort` VALUES ('15', '剪辑工具推荐', '0', '1650106424', null);
