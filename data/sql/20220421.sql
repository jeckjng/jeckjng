/* 贴吧、*/
ALTER TABLE `cmf_users` ADD     `is_allow_post` tinyint(4) DEFAULT '1' COMMENT '是否允许发贴，1 允许，0 不允许';
ALTER TABLE `cmf_users` ADD   `is_allow_comment` tinyint(4) DEFAULT '1' COMMENT '是否允许评论 1 允许 ，0不允许';
ALTER TABLE `cmf_users` ADD   `is_allow_seeking_slice` tinyint(4) DEFAULT '1' COMMENT '是否允许求片 1  允许 ，0 不允许';
ALTER TABLE `cmf_users` ADD   `is_allow_push_slice` tinyint(4) DEFAULT '1' COMMENT '是否允许推片 1  允许， 0 不允许';
ALTER TABLE `cmf_users` ADD   `withdrawable_push_slice` decimal(12,2) DEFAULT '0.00' COMMENT '可提现推片赏金（已加到余额）';
ALTER TABLE `cmf_users` ADD    `no_withdrawable_push_slice` decimal(12,2) DEFAULT '0.00' COMMENT '不可提现推片赏金（已加到不可提现余额）';

/**
 配置
 */
ALTER TABLE `cmf_tenant_config` ADD     `is_open_seeking_slice` tinyint(4) DEFAULT '1' COMMENT '是否开启求片(贴吧) 1开启 0 关闭';
ALTER TABLE `cmf_tenant_config` ADD     `posting_strategy` tinyint(4) DEFAULT '1' COMMENT '1全开启 发帖策略  0 全关闭，3按vip';
ALTER TABLE `cmf_tenant_config` ADD    `comment_strategy` tinyint(4) DEFAULT '1' COMMENT '评论策略(1 全开启 0 全关闭 ，2按vip)';
ALTER TABLE `cmf_tenant_config` ADD   `seeking_slice_strategy` tinyint(4) DEFAULT '1' COMMENT '发求片策略 (1 全开启 0 全关闭 ，2按vip)';
ALTER TABLE `cmf_tenant_config` ADD   `push_strategy` tinyint(4) DEFAULT '1' COMMENT '推片策略 (1 全开启 0 全关闭 ，2按vip)';
ALTER TABLE `cmf_tenant_config` ADD   `seeking_slice_effective_time` int(11) DEFAULT '0' COMMENT '求片有效时间（单位： 秒）' ;
ALTER TABLE `cmf_tenant_config` ADD   `seeking_slice_bonus_min` decimal(12,2) DEFAULT '0.00' COMMENT '推片悬赏金最少值';
ALTER TABLE `cmf_tenant_config` ADD  `seeking_slice_bonus_max` decimal(12,2) DEFAULT '0.00' COMMENT '推片悬赏金最大值';
ALTER TABLE `cmf_tenant_config` ADD `reward_is_withdrawal` tinyint(4) DEFAULT '1' COMMENT '是否可提现（推片悬赏金）1 可提现 0 不可提现';

ALTER TABLE `cmf_users_jurisdiction` ADD `bar_number` int(11) NOT NULL DEFAULT '0' COMMENT '发帖数量';
ALTER TABLE `cmf_users_jurisdiction` ADD  `bar_slice_number` int(11) NOT NULL COMMENT '发帖评论数量';


CREATE TABLE `cmf_bar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `desc` text COLLATE utf8_unicode_ci COMMENT '标题',
  `img` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '图片(多图片，逗号切割)',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1普通发帖,求片',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1审核中 2 通过 3 驳回 4 删除',
  `video` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '视频地址，多个用 ，切割',
  `video_img` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '视频封面图',
  `reward_money` decimal(12,2) DEFAULT '0.00' COMMENT '赏金',
  `tenant_id` int(11) DEFAULT NULL COMMENT '租户id',
  `comments_number` int(11) DEFAULT '0' COMMENT '评论数（求片时为推片数）',
  `like_number` int(11) DEFAULT '0' COMMENT '点赞数',
  `operator_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作人id',
  `validtime` int(11) NOT NULL DEFAULT '0' COMMENT '有效时间（秒）',
  `optimum_uid` int(11) DEFAULT '0' COMMENT '最佳答案用户（求片才有）',
  `optimum_comment_id` int(11) NOT NULL DEFAULT '0' COMMENT '最佳评论（求片才有）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `cmf_bar_comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `desc` text COLLATE utf8_unicode_ci NOT NULL,
  `parent_comment_id` int(11) NOT NULL COMMENT '被回复评论id（回复上级id）',
  `comment_id` int(11) NOT NULL COMMENT '被回复评论id（一级评论的）',
  `bar_id` int(11) NOT NULL DEFAULT '0' COMMENT '发帖id',
  `publish_uid` int(11) NOT NULL DEFAULT '0' COMMENT '评论者uid（发评论的用户id）',
  `bar_uid` int(11) NOT NULL DEFAULT '0' COMMENT '发贴者id',
  `parent_reply_uid` int(11) NOT NULL COMMENT '被回复用户id（回复上级id）',
  `reply_uid` int(11) NOT NULL COMMENT '被回复uid(一级评论用户id)',
  `addtime` int(11) DEFAULT NULL,
  `endtime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1 普通帖回复 ，2 求片贴回复',
  `video_id` int(11) DEFAULT '0' COMMENT '视频id（求片回复才有）',
  `video_type` tinyint(4) DEFAULT '1' COMMENT '视频类型 1短视频，2长视频（求片回复才有） ',
  `status` tinyint(11) DEFAULT '1' COMMENT '1评论成功 2 设为悬赏评论  （求片回复才有）',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `cmf_bar_likes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bar_id` int(11) DEFAULT '0' COMMENT '贴子id',
  `uid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50726
Source Host           : 127.0.0.1:3306
Source Database       : cs88_dev

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2022-04-21 21:08:44
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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of cmf_reception_meun
-- ----------------------------
INSERT INTO `cmf_reception_meun` VALUES ('1', '拍拍', '0', '1', '1628851562');
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
INSERT INTO `cmf_reception_meun` VALUES ('52', '聊天室', '17', '1', '1635333097');
INSERT INTO `cmf_reception_meun` VALUES ('53', '贴吧', '2', '1', '1650543597');
INSERT INTO `cmf_reception_meun` VALUES ('54', '发帖数量', '53', '1', '1650543634');
INSERT INTO `cmf_reception_meun` VALUES ('55', '求片数量', '53', '1', '1650543653');
INSERT INTO `cmf_reception_meun` VALUES ('56', '评论帖子', '53', '1', '1650543668');
INSERT INTO `cmf_reception_meun` VALUES ('57', '推片', '53', '1', '1650543679');



ALTER TABLE `cmf_bar_comment`
ADD COLUMN `is_delete`  tinyint(4) NULL DEFAULT 0 COMMENT '是否删除 1 是 0 否' AFTER `status`;

ALTER TABLE `cmf_bar`
CHANGE COLUMN `video` `href`  varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '视频地址，多个用 ，切割' AFTER `status`,
ADD COLUMN `href_1`  varchar(255) NULL DEFAULT '' AFTER `href`,
ADD COLUMN `href_2`  varchar(255) NULL DEFAULT '' AFTER `href_1`,
ADD COLUMN `href_3`  varchar(255) NULL DEFAULT '' AFTER `href_2`,
ADD COLUMN `href_4`  varchar(255) NULL DEFAULT '' AFTER `href_3`,
ADD COLUMN `href_5`  varchar(255) NULL DEFAULT '' AFTER `href_4`,
ADD COLUMN `href_6`  varchar(255) NULL DEFAULT '' AFTER `href_5`,
ADD COLUMN `href_7`  varchar(255) NULL DEFAULT '' AFTER `href_6`,
ADD COLUMN `href_8`  varchar(255) NULL DEFAULT '' AFTER `href_7`,
ADD COLUMN `href_9`  varchar(255) NULL DEFAULT '' AFTER `href_8`;

ALTER TABLE `cmf_bar`
ADD COLUMN `fileStoreKey`  varchar(255) NULL DEFAULT '' COMMENT '视频上传key' AFTER `optimum_comment_id`,
ADD COLUMN `video_status`  int(4) NULL DEFAULT 0 COMMENT '0等待上传成功，1上传成功，2上传失败,3文件不存在' AFTER `fileStoreKey`;


ALTER TABLE `cmf_bar`
MODIFY COLUMN `img`  text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL COMMENT '图片(多图片，逗号切割)' AFTER `desc`;

