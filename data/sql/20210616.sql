CREATE TABLE `cmf_long_video_banner` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `video_id` int(11) NOT NULL DEFAULT '0' COMMENT '长视频id',
  `img` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '图片地址',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1启用，2删除',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '标签',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;