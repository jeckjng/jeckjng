
// 弃用
CREATE TABLE `cmf_line_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `play_line` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '播放线路',
  `download_line` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tenant_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;