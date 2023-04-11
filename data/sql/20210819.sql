/*
Navicat MySQL Data Transfer

Source Server         : 18.142.148.178
Source Server Version : 50712
Source Host           : 18.142.148.178:3306
Source Database       : live_dev

Target Server Type    : MYSQL
Target Server Version : 50712
File Encoding         : 65001

Date: 2021-10-22 23:21:10
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `cmf_reception_meun`
-- ----------------------------
DROP TABLE IF EXISTS `cmf_reception_meun`;
CREATE TABLE `cmf_reception_meun` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '菜单名称',
  `parentid` tinyint(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1启用，2禁用',
  `addtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of cmf_reception_meun
-- ----------------------------
INSERT INTO `cmf_reception_meun` VALUES ('1', '啪啪', '0', '1', '1628851562');
INSERT INTO `cmf_reception_meun` VALUES ('2', '游戏', '0', '1', '1628851581');
INSERT INTO `cmf_reception_meun` VALUES ('3', '直播', '0', '1', '1628851594');
INSERT INTO `cmf_reception_meun` VALUES ('4', '首页', '0', '1', '1628851601');
INSERT INTO `cmf_reception_meun` VALUES ('5', '我的', '0', '1', '1628851607');
INSERT INTO `cmf_reception_meun` VALUES ('6', '精选', '1', '1', '1629359171');
INSERT INTO `cmf_reception_meun` VALUES ('7', '标签', '1', '1', '1629359206');
INSERT INTO `cmf_reception_meun` VALUES ('11', '游戏', '2', '1', '1629359229');
INSERT INTO `cmf_reception_meun` VALUES ('12', '全部', '3', '1', '1629359236');
INSERT INTO `cmf_reception_meun` VALUES ('13', '直播分类', '3', '1', '1629359250');
INSERT INTO `cmf_reception_meun` VALUES ('14', '精选', '4', '1', '1629359256');
INSERT INTO `cmf_reception_meun` VALUES ('15', '推荐', '4', '1', '1629359268');
INSERT INTO `cmf_reception_meun` VALUES ('16', '广告', '5', '1', '1629359281');
INSERT INTO `cmf_reception_meun` VALUES ('17', '功能区', '5', '1', '1629359308');
INSERT INTO `cmf_reception_meun` VALUES ('18', '全局菜单', '0', '1', '1629359576');
INSERT INTO `cmf_reception_meun` VALUES ('19', '点赞', '6', '1', '1629363962');
INSERT INTO `cmf_reception_meun` VALUES ('20', '评论', '6', '1', '1629363967');
INSERT INTO `cmf_reception_meun` VALUES ('21', '下载', '6', '1', '1629363974');
INSERT INTO `cmf_reception_meun` VALUES ('22', '关注', '6', '1', '1629363980');
INSERT INTO `cmf_reception_meun` VALUES ('23', '收 藏', '6', '1', '1629364007');
INSERT INTO `cmf_reception_meun` VALUES ('24', '弹窗广告', '6', '1', '1629364034');
INSERT INTO `cmf_reception_meun` VALUES ('25', '广告', '11', '1', '1629364042');
INSERT INTO `cmf_reception_meun` VALUES ('26', '开直播间', '12', '1', '1629364105');
INSERT INTO `cmf_reception_meun` VALUES ('27', '交友聊天室', '12', '1', '1629364114');
INSERT INTO `cmf_reception_meun` VALUES ('28', '广告', '12', '1', '1629364118');
INSERT INTO `cmf_reception_meun` VALUES ('29', '点赞', '15', '1', '1629364157');
INSERT INTO `cmf_reception_meun` VALUES ('30', '评论', '15', '1', '1629364161');
INSERT INTO `cmf_reception_meun` VALUES ('31', '下载', '15', '1', '1629364167');
INSERT INTO `cmf_reception_meun` VALUES ('32', '收藏', '15', '1', '1629364172');
INSERT INTO `cmf_reception_meun` VALUES ('33', '关注', '15', '1', '1629364177');
INSERT INTO `cmf_reception_meun` VALUES ('34', '播放页广告', '15', '1', '1629364182');
INSERT INTO `cmf_reception_meun` VALUES ('35', '我的视频', '17', '1', '1629364222');
INSERT INTO `cmf_reception_meun` VALUES ('36', '我要开播', '17', '1', '1629364228');
INSERT INTO `cmf_reception_meun` VALUES ('37', '我的收藏', '17', '1', '1629364233');
INSERT INTO `cmf_reception_meun` VALUES ('38', '我的下载', '17', '1', '1629364238');
INSERT INTO `cmf_reception_meun` VALUES ('39', '福利兑换', '17', '1', '1629364242');
INSERT INTO `cmf_reception_meun` VALUES ('40', '收支明细', '17', '1', '1629364247');
INSERT INTO `cmf_reception_meun` VALUES ('41', '推广详情', '17', '1', '1629364251');
INSERT INTO `cmf_reception_meun` VALUES ('42', '关注', '17', '1', '1629364256');
INSERT INTO `cmf_reception_meun` VALUES ('43', '我的反馈', '17', '1', '1629364262');
INSERT INTO `cmf_reception_meun` VALUES ('44', '常见问题', '17', '1', '1629364281');
INSERT INTO `cmf_reception_meun` VALUES ('45', '全局菜单', '18', '1', '1629364314');
INSERT INTO `cmf_reception_meun` VALUES ('46', '限制数量', '45', '1', '1629364325');
INSERT INTO `cmf_reception_meun` VALUES ('47', '观影时长', '45', '1', '1629364335');
INSERT INTO `cmf_reception_meun` VALUES ('48', '任务', '2', '1', '1634129690');
INSERT INTO `cmf_reception_meun` VALUES ('49', '我的任务', '17', '1', '1634129701');
INSERT INTO `cmf_reception_meun` VALUES ('50', '语言切换', '17', '1', '1634712707');
INSERT INTO `cmf_reception_meun` VALUES ('51', '首页弹窗', '4', '1', '1634730202');
