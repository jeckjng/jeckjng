ALTER TABLE `cmf_users`
ADD COLUMN `pids`  varchar(255) NOT NULL DEFAULT '0' COMMENT '上级用户id' AFTER `client`;

ALTER TABLE `cmf_users`
ADD COLUMN `agent_total_income`  decimal(12,2) NOT NULL DEFAULT 0 COMMENT '代理总收入' AFTER `pids`;


CREATE TABLE `cmf_agent_proportion` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `agent_level` int(11) NOT NULL DEFAULT '1' COMMENT '代理等级',
  `rate` decimal(20,2) NOT NULL DEFAULT '0.00',
  `tenant_id` int(10) NOT NULL DEFAULT '0' COMMENT '租户id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `cmf_agent_reward` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '操作用户id',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '收入id',
  `addtime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `endtime` int(11) NOT NULL DEFAULT '0' COMMENT '转换时间',
  `level` tinyint(2) NOT NULL DEFAULT '1' COMMENT '代理等级',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT ' 1  任务  ，2 购买视频  ， 3 点赞视频',
  `operation_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作的数据id（如任务，就是该任务 id，视频就是 该视频的id） ',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1 为转换为 可提现余额 ，2 已转换为可提现 余额',
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '总额',
  `rate` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '比例',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '到账金额',
  `tenant_id` int(10) NOT NULL DEFAULT '0' COMMENT '租户id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `cmf_tenant_config` ADD COLUMN  `agent_sum` tinyint(2) DEFAULT '20' COMMENT '代理层数（目前）';
ALTER TABLE `cmf_tenant_config` ADD COLUMN `commission_type` tinyint(2) DEFAULT '1' COMMENT '1 7 日可转提现 2  永不可提现';
ALTER TABLE `cmf_tenant_config` ADD COLUMN`video_likes_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '视频点赞佣金';
ALTER TABLE `cmf_tenant_config`  ADD COLUMN  `video_likes_amount_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '视频点赞佣金类型  1 可提现 2 不可提现 （代理佣金 一致）';
ALTER TABLE `cmf_tenant_config` ADD COLUMN `video_buy_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '购买视频佣金';
ALTER TABLE `cmf_tenant_config` ADD COLUMN `video_buy_amount_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT ' 购买视频佣金类型 1 可提现  2 不可提现';
ALTER TABLE `cmf_tenant_config` ADD COLUMN `video_uplode_amount` decimal(12,0) NOT NULL DEFAULT '0' COMMENT '上传视频佣金';
ALTER TABLE `cmf_tenant_config` ADD COLUMN `video_uplode_amount_type` tinyint(2) NOT NULL DEFAULT '1' COMMENT ' 上传视频佣金类型 1可提现  2， 不可提现';
ALTER TABLE `cmf_tenant_config` ADD COLUMN `withdrawal_time` tinyint(4) NOT NULL DEFAULT '7' COMMENT '不可提现转可提现日期设置';
ALTER TABLE `cmf_tenant_config` ADD COLUMN `share_award_amount_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '分享奖励类型 1 可提现 2 ，不可提现';
ALTER TABLE `cmf_tenant_config`
ADD COLUMN `first_charge_award_amount_type`  tinyint(4) NOT NULL DEFAULT 1 COMMENT ' 首充奖励金额类型 1 可提现 2 不可提现' AFTER `share_award_amount_type`;



ALTER TABLE `cmf_tenant_config`
DROP COLUMN `like_rate_zero`,
DROP COLUMN `like_rate_one`,
DROP COLUMN `like_rate_two`,
DROP COLUMN `like_rate_three`;

ALTER TABLE `cmf_users_video_buy`
DROP COLUMN `one_price`,
DROP COLUMN `one_user`,
DROP COLUMN `two_price`,
DROP COLUMN `two_user`,
DROP COLUMN `three_price`,
DROP COLUMN `tree_user`;

ALTER TABLE `cmf_users_coinrecord`
ADD COLUMN `is_withdrawable`  tinyint(2) NOT NULL DEFAULT 1 COMMENT '1  可提现金额  2 不可提现(为转为可提现) 3（不可提现已转为可提现）' AFTER `familyhead_total`;

ALTER TABLE `cmf_video_profit`
DROP COLUMN `video_uid_one`,
DROP COLUMN `video_profit_one`,
DROP COLUMN `video_uid_two`,
DROP COLUMN `video_profit_two`,
DROP COLUMN `video_uid_three`,
DROP COLUMN `video_profit_three`,
CHANGE COLUMN `video_uid_zero` `video_uid`  int(11) NOT NULL DEFAULT 0 COMMENT '视频作者UID' AFTER `video_like_uid`,
CHANGE COLUMN `video_profit_zero` `video_profit`  decimal(6,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '视频作者收益' AFTER `video_uid`;
CREATE TABLE `cmf_video_uplode_reward` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `video_id` int(11) NOT NULL DEFAULT '0',
  `video_type` tinyint(3) NOT NULL DEFAULT '1' COMMENT ' 1  短视频 2 长视频',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `add_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `cmf_users_video_buy`
ADD COLUMN `ex_user_id`  int(11) NULL DEFAULT 0 COMMENT '作者id' AFTER `video_type`;





