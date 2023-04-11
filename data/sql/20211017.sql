ALTER TABLE `cmf_user_task` ADD `commission_rate` DECIMAL(4,3) NOT NULL DEFAULT '0.000' COMMENT '佣金比例' AFTER `experience_shop`;
CREATE TABLE `cmf_activity` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `fro_min` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '首充奖励1（最小值）',
                                `fro_max` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '首充奖励1（最大值）',
                                `fro_reward` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '奖励金额（首充奖励1）',
                                `fro_watchtimesnum` int(11) NOT NULL DEFAULT '0' COMMENT '观影次数（首充奖励1）',
                                `fro_watchtime` int(11) NOT NULL DEFAULT '0' COMMENT '观影时长（首充奖励1）',
                                `frt_min` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '首充奖励2（最小金额）',
                                `frt_max` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '首充奖励2（最大金额）',
                                `frt_reward` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '奖励金额（首充奖励2）',
                                `frt_watchtimesnum` decimal(11,0) NOT NULL DEFAULT '0' COMMENT '观影次数（首充奖励2）',
                                `frt_watchtime` decimal(11,0) NOT NULL DEFAULT '0' COMMENT '观影时长（首充奖励2）',
                                `recommend_frmr` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励首充最低充值金额',
                                `recommend_rr_1` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充1人]-奖励金额',
                                `recommend_rr_2` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充2人]-奖励金额',
                                `recommend_rr_3` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充3人]-奖励金额',
                                `recommend_rr_4` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充4人]-奖励金额',
                                `recommend_rr_5` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充5人]-奖励金额',
                                `recommend_rr_6` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '推荐奖励[推荐首充6人]-奖励金额',
                                `act_uid` int(11) NOT NULL DEFAULT '0' COMMENT '操作人',
                                `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
                                PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `cmf_user_task` ADD `task_name` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '任务名称' AFTER `task_id`;
ALTER TABLE `cmf_task_rewardlog` ADD `reward1` TINYINT(2) NOT NULL COMMENT '完成奖励1' AFTER `reward_type`, ADD `reward2_upgrade_vip` TINYINT(2) NOT NULL COMMENT '完成奖励2：是否升级VIP等级: 0否，1是' AFTER `reward1`;
ALTER TABLE `cmf_task_rewardlog` CHANGE `reward1` `reward1` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '完成奖励1';
ALTER TABLE `cmf_user_task` CHANGE `reward1` `reward1` DECIMAL(20,2) NOT NULL DEFAULT '0' COMMENT '完成奖励1';
ALTER TABLE `cmf_task` CHANGE `reward1` `reward1` DECIMAL(20,2) NOT NULL DEFAULT '0' COMMENT '完成奖励1';
ALTER TABLE `cmf_task_loginreward` CHANGE `reg_withdrawable_coin` `reg_withdrawable_coin` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '注册奖励-可提现金币';
ALTER TABLE `cmf_task_loginreward`
    MODIFY COLUMN `reg_withdrawable_coding`  int(11) NOT NULL DEFAULT 0 COMMENT '注册奖励-可提现金币打码量' AFTER `reg_withdrawable_coin`,
    MODIFY COLUMN `reg_nowithdrawable_coin`  decimal(20,2) NOT NULL DEFAULT 0.00 COMMENT '注册奖励-不可提现金币' AFTER `reg_withdrawable_coding`,
    MODIFY COLUMN `firstlog_withdrawable_coin`  decimal(20,2) NOT NULL DEFAULT 0.00 COMMENT '首次登录-可提现金币' AFTER `reg_nowithdrawable_coin`,
    MODIFY COLUMN `firstlog_withdrawable_coding`  int(11) NOT NULL DEFAULT 0 COMMENT '首次登录-可提现金币打码量' AFTER `firstlog_withdrawable_coin`,
    MODIFY COLUMN `firstlog_nowithdrawable_coin`  decimal(20,2) NOT NULL DEFAULT 0.00 COMMENT '首次登录-不可提现金币' AFTER `firstlog_withdrawable_coding`,
    MODIFY COLUMN `otherlog_withdrawable_coin`  decimal(20,2) NOT NULL DEFAULT 0.00 COMMENT '非首次登录-可提现金币' AFTER `firstlog_nowithdrawable_coin`,
    MODIFY COLUMN `otherlog_withdrawable_coding`  int(11) NOT NULL DEFAULT 0 COMMENT '非首次登录-可提现金币打码量' AFTER `otherlog_withdrawable_coin`,
    MODIFY COLUMN `otherlog_nowithdrawable_coin`  decimal(20,2) NOT NULL DEFAULT 0.00 COMMENT '非首次登录-不可提现金币' AFTER `otherlog_withdrawable_coding`;
ALTER TABLE `cmf_task_rewardlog` ADD `task_name` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '任务名称' AFTER `task_id`;
ALTER TABLE `cmf_user_task` ADD `start_time` INT(11) NOT NULL DEFAULT '0' COMMENT '生效时间' AFTER `reward2_upgrade_vip`, ADD `end_time` INT(11) NOT NULL DEFAULT '0' COMMENT '失效时间' AFTER `start_time`;


