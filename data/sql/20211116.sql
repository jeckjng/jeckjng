CREATE TABLE `cmf_lottery_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `play_id` int(10) NOT NULL DEFAULT '0',
  `upper_id` int(10) NOT NULL DEFAULT '0',
  `play_cname` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '彩种名称',
  `play_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '币种名称',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT ' 状态， 1 启用： 2禁用',
  `tenant_id` tinyint(4) NOT NULL DEFAULT '0' COMMENT '租户id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `cmf_users_live` ADD   `lottery_id` varchar(100) NOT NULL DEFAULT '' COMMENT '彩种id';
ALTER TABLE `cmf_users_live` ADD   `recommend_lottery_id` varchar(100) NOT NULL DEFAULT '' COMMENT '推荐彩种id';
ALTER TABLE `cmf_users_live` ADD  `show_game_entry` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否展示游戏入口 1 展示 0 不展示';
ALTER TABLE `cmf_users_live` ADD  `show_offers` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否显示优惠活动 1 展示 0 不展示';
ALTER TABLE `cmf_users_live` ADD  `show_dragon_assistant` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否显示长龙助手 1 是 0 否';
ALTER TABLE `cmf_users_live` ADD   `show_reward_reporting` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否显示举报有奖  1 显示 0 不显示';
ALTER TABLE `cmf_users_live` ADD   `enable_follow` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否启用跟投 1 启用 0 不启用';