CREATE TABLE `cmf_uplode_video_rules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `min_time` int(11) NOT NULL DEFAULT '0' COMMENT '最短时间',
  `max_time` int(11) NOT NULL DEFAULT '0' COMMENT '最长时间',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `add_time` int(11) NOT NULL DEFAULT '0',
  `tenant_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `cmf_tenant_config`
ADD COLUMN `upload_video_reward_model`  tinyint(11) NULL DEFAULT 1 COMMENT '1 vip模式 2 视频长短' AFTER `video_stop_ads`;

