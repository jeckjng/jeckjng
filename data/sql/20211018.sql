ALTER TABLE `cmf_task_loginreward`
    MODIFY COLUMN `reg_withdrawable_coin`  int(11) NOT NULL DEFAULT 0 COMMENT '注册奖励-可提现金币' AFTER `id`,
    MODIFY COLUMN `reg_nowithdrawable_coin`  int(11) NOT NULL DEFAULT 0 COMMENT '注册奖励-不可提现金币' AFTER `reg_withdrawable_coding`,
    MODIFY COLUMN `firstlog_withdrawable_coin`  int(11) NOT NULL DEFAULT 0 COMMENT '首次登录-可提现金币' AFTER `reg_nowithdrawable_coin`,
    MODIFY COLUMN `firstlog_nowithdrawable_coin`  int(11) NOT NULL DEFAULT 0 COMMENT '首次登录-不可提现金币' AFTER `firstlog_withdrawable_coding`,
    MODIFY COLUMN `otherlog_withdrawable_coin`  int(11) NOT NULL DEFAULT 0 COMMENT '非首次登录-可提现金币' AFTER `firstlog_nowithdrawable_coin`,
    MODIFY COLUMN `otherlog_nowithdrawable_coin`  int(11) NOT NULL DEFAULT 0 COMMENT '非首次登录-不可提现金币' AFTER `otherlog_withdrawable_coding`;

CREATE TABLE `cmf_task_config` (
                                   `id` int(11) NOT NULL AUTO_INCREMENT,
                                   `value` varchar(160) CHARACTER SET utf8 NOT NULL COMMENT '值',
                                   `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '类型：1 日志记录',
                                   `mtime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
                                   PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=59 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;