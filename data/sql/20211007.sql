ALTER TABLE `cmf_video` ADD `href_1` varchar (100)  DEFAULT NULL COMMENT '视频切片地址' AFTER `href`;
ALTER TABLE `cmf_video` ADD `href_2` varchar (100)  DEFAULT NULL COMMENT '视频切片地址' AFTER `href_1`;
ALTER TABLE `cmf_video` ADD `href_3` varchar (100)  DEFAULT NULL COMMENT '视频切片地址' AFTER `href_2`;
ALTER TABLE `cmf_video` ADD `href_4` varchar (100)  DEFAULT NULL COMMENT '视频切片地址' AFTER `href_3`;
ALTER TABLE `cmf_video` ADD `href_5` varchar (100)  DEFAULT NULL COMMENT '视频切片地址' AFTER `href_4`;
ALTER TABLE `cmf_video` ADD `href_6` varchar (100)  DEFAULT NULL COMMENT '视频切片地址' AFTER `href_5`;
ALTER TABLE `cmf_video` ADD `href_7` varchar (100)  DEFAULT NULL COMMENT '视频切片地址' AFTER `href_6`;
ALTER TABLE `cmf_video` ADD `href_8` varchar (100)  DEFAULT NULL COMMENT '视频切片地址' AFTER `href_7`;
ALTER TABLE `cmf_video` ADD `href_9` varchar (100) DEFAULT NULL COMMENT '视频切片地址' AFTER `href_8`;


ALTER TABLE `cmf_video_long` ADD `href_1` varchar (100) DEFAULT NULL COMMENT '视频切片地址' AFTER `href`;
ALTER TABLE `cmf_video_long` ADD `href_2` varchar (100) DEFAULT NULL COMMENT '视频切片地址' AFTER `href_1`;
ALTER TABLE `cmf_video_long` ADD `href_3` varchar (100) DEFAULT NULL COMMENT '视频切片地址' AFTER `href_2`;
ALTER TABLE `cmf_video_long` ADD `href_4` varchar (100) DEFAULT NULL COMMENT '视频切片地址' AFTER `href_3`;
ALTER TABLE `cmf_video_long` ADD `href_5` varchar (100) DEFAULT NULL COMMENT '视频切片地址' AFTER `href_4`;
ALTER TABLE `cmf_video_long` ADD `href_6` varchar (100) DEFAULT NULL COMMENT '视频切片地址' AFTER `href_5`;
ALTER TABLE `cmf_video_long` ADD `href_7` varchar (100) DEFAULT NULL COMMENT '视频切片地址' AFTER `href_6`;
ALTER TABLE `cmf_video_long` ADD `href_8` varchar (100) DEFAULT NULL COMMENT '视频切片地址' AFTER `href_7`;
ALTER TABLE `cmf_video_long` ADD `href_9` varchar (100) DEFAULT NULL COMMENT '视频切片地址' AFTER `href_8`;




ALTER TABLE `cmf_video` ADD `download_address_1` varchar(255) DEFAULT NULL COMMENT '视频下载地址' AFTER `download_address`;
ALTER TABLE `cmf_video` ADD `download_address_2` varchar(255) DEFAULT NULL COMMENT '视频下载地址' AFTER `download_address_1`;


ALTER TABLE `cmf_video_long` ADD `download_address_1` varchar(255) DEFAULT NULL COMMENT '视频下载地址' AFTER `download_address`;
ALTER TABLE `cmf_video_long` ADD `download_address_2` varchar(255) DEFAULT NULL COMMENT '视频下载地址' AFTER `download_address_1`;

DROP TABLE IF EXISTS `cmf_playback_address`;
CREATE TABLE `cmf_playback_address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '名称',
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '播放地址',
  `is_enable` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `viode_table_field` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '对应视频表的播放字段',
  `type` tinyint(2) DEFAULT '1' COMMENT '1播放线路2 下载线路',
  `java_field` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'java返回对应字段',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of cmf_playback_address
-- ----------------------------
INSERT INTO `cmf_playback_address` VALUES ('1', '阿里', 'https://jiami-video.oss-cn-beijing.aliyuncs.com', '0', 'href', '1', 'slave-1');
INSERT INTO `cmf_playback_address` VALUES ('2', '七牛', 'https://qiniu.sxtymedia.com', '1', 'href_1', '1', 'master');
INSERT INTO `cmf_playback_address` VALUES ('3', '3', '3', '0', 'href_2', '1', '');
INSERT INTO `cmf_playback_address` VALUES ('4', '4', '4', '0', 'href_3', '1', '');
INSERT INTO `cmf_playback_address` VALUES ('5', '5', '5', '0', 'href_4', '1', '');
INSERT INTO `cmf_playback_address` VALUES ('6', '5', '6', '0', 'href_5', '1', '');
INSERT INTO `cmf_playback_address` VALUES ('7', '7', '7', '0', 'href_6', '1', '');
INSERT INTO `cmf_playback_address` VALUES ('8', '8', '8', '0', 'href_7', '1', '');
INSERT INTO `cmf_playback_address` VALUES ('9', '9', '9', '0', 'href_8', '1', '');
INSERT INTO `cmf_playback_address` VALUES ('10', '10', '10', '0', 'href_9', '1', '');
INSERT INTO `cmf_playback_address` VALUES ('11', '下载', 'https://s3.ap-southeast-1.amazonaws.com', '1', 'download_address', '2', '');
INSERT INTO `cmf_playback_address` VALUES ('12', '下载2', '2', '0', 'download_address_1', '2', '');
INSERT INTO `cmf_playback_address` VALUES ('13', '下载3', '13', '0', 'download_address_2', '2', '');


ALTER TABLE `cmf_video` ADD  `desc` text COMMENT '剧情简介';
ALTER TABLE `cmf_video` ADD  `years` varchar(255) NOT NULL DEFAULT '' COMMENT '年代';
ALTER TABLE `cmf_video` ADD   `region` varchar(255) NOT NULL DEFAULT '' COMMENT '地区';
ALTER TABLE `cmf_video_long` ADD  `desc` text COMMENT '剧情简介';
ALTER TABLE `cmf_video_long` ADD  `years` varchar(255) NOT NULL DEFAULT '' COMMENT '年代';
ALTER TABLE `cmf_video_long` ADD   `region` varchar(255) NOT NULL DEFAULT '' COMMENT '地区';



