ALTER TABLE cmf_video  ADD `download_times` int (11) DEFAULT '0' COMMENT '1: 下载次数';
ALTER TABLE cmf_video_long  ADD `download_times` int (11) DEFAULT '0' COMMENT '1: 下载次数';
CREATE TABLE `cmf_video_download` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `videoid` int(11) NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL DEFAULT '0',
  `video_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1短视频 2长视频',
  `uid` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='视频下载';