ALTER TABLE `cmf_users_charge`
ADD COLUMN `is_buy_vip`  tinyint(4) NOT NULL DEFAULT 0 COMMENT '  0 正常充值 1 购买vip 充值' AFTER `img`,
ADD COLUMN `vip_id`  int(10) NOT NULL DEFAULT 0 COMMENT '如果是购买vip 次 为 vip 的id  （保证金模式 关联 cmf_vip_grade   购买模式 关联 cmf_vip）' AFTER `is_buy_vip`;

ALTER TABLE `cmf_users_charge`
CHANGE COLUMN `vip_id` `buy_log_id`  int(10) NOT NULL DEFAULT 0 COMMENT '购买记录id' AFTER `is_buy_vip`;

ALTER TABLE `cmf_users`
ADD COLUMN `reg_url`  varchar(255) NOT NULL DEFAULT '' AFTER `certification_name`,
ADD COLUMN `reg_key`  varchar(255) NOT NULL DEFAULT '' COMMENT '域名对应的key' AFTER `reg_url`;

CREATE TABLE `cmf_activity_reward_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) NOT NULL COMMENT '类型：1 首充活动，2: 分享活动',
  `watnum` int(11) NOT NULL DEFAULT '0' COMMENT '观影次数',
  `wattime` int(11) NOT NULL DEFAULT '0' COMMENT '观影时长',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `reward` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '奖励金额',
  `tenant_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户所在租户ID',
  `ctime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=215 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=FIXED;

