ALTER TABLE `cmf_user_task` ADD `reward2_upgrade_vip` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '完成奖励2：是否升级VIP等级: 0否，1是 ' AFTER `experience_shop`;
ALTER TABLE `cmf_user_task` ADD `reward1` TINYINT(2) NOT NULL DEFAULT '0' COMMENT '完成奖励1' AFTER `experience_shop`;
ALTER TABLE `cmf_user_task` ADD `price` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '任务价格' AFTER `experience_shop`;
